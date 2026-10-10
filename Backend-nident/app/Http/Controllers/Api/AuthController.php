<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\RefreshToken;
use App\Models\SeguridadAuthLoginLog;
use App\Models\SeguridadAuthProveedor;
use App\Services\Seguridad\AdminPrincipalService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private int $tokenExpiration;
    public function __construct()
    {
        $this->tokenExpiration = (int) config('jwt.ttl', 60);
    }

    /** Días de validez del refresh token (sesión persistente en el navegador). */
    private function refreshTokenDays(bool $remember): int
    {
        $long = max(1, min(730, (int) env('AUTH_REFRESH_DAYS', 365)));
        $short = max(1, min(90, (int) env('AUTH_REFRESH_DAYS_SHORT', 30)));

        return $remember ? $long : $short;
    }

    /**
     * Obtiene perfiles para login sin depender de datatreeview.
     * Mismo formato que USP_ADMINISTRACION_PERFIL_LISTAR_LOGIN: menu_objetos como |id_menu_objeto1,objeto2|-
     */
    /**
     * Si el usuario es administrador principal (o id=1) y no tiene perfil en BD, lo vincula automáticamente.
     */
    private function ensureUsuarioTienePerfilAsignado(int $userId): void
    {
        if (DB::table('seguridad_perfil_users')->where('id_usuario', $userId)->exists()) {
            return;
        }

        $user = User::find($userId);
        if (!$user || $user->Activo !== 'S') {
            return;
        }

        $svc = app(AdminPrincipalService::class);
        if ($user->es_administrador_principal || (int) $user->id === 1) {
            $svc->asignarPerfilAdministrador($userId);
            return;
        }

        $principal = $svc->obtenerPrincipal();
        if ($principal && (int) $principal->id === $userId) {
            $svc->asignarPerfilAdministrador($userId);
        }
    }

    private function getPerfilData($userId, $idPerfil = null, $idRoles = null)
    {
        if ($idPerfil === null && $idRoles === null) {
            $this->ensureUsuarioTienePerfilAsignado((int) $userId);
        }

        $bindings = [$userId];
        $wherePerfil = '';
        $whereRol = '';
        if ($idPerfil !== null) {
            $wherePerfil = ' AND SP.id_perfil = ?';
            $bindings[] = $idPerfil;
        }
        if ($idRoles !== null) {
            $whereRol = ' AND SRP.id_roles = ?';
            $bindings[] = $idRoles;
        }

        $rows = DB::select("
            SELECT SPU.id_usuario, U.name AS nombre_usuario, U.email,
                   SP.nombre AS nombre_perfil, SP.id_perfil, SRP.id_roles,
                   SRM.id_menu, SMO.id_objetos AS id_objetos, SMOR.id_menu_objetos AS id_menu_objetos_permiso
            FROM users U
            INNER JOIN seguridad_perfil_users SPU ON U.id = SPU.id_usuario
            INNER JOIN seguridad_perfil SP ON SPU.id_perfil = SP.id_perfil
            LEFT JOIN seguridad_roles_perfil SRP ON SP.id_perfil = SRP.id_perfil
            LEFT JOIN seguridad_roles_menu SRM ON SRP.id_roles = SRM.id_roles
            LEFT JOIN sistema_menu_objetos SMO ON SMO.id_menu = SRM.id_menu AND (SMO.Activo = 'S' OR SMO.Activo IS NULL)
            LEFT JOIN seguridad_menu_objetos_roles SMOR ON SMOR.id_roles = SRP.id_roles AND SMOR.id_menu_objetos = SMO.id_menu_objetos
            WHERE SPU.id_usuario = ?
            $wherePerfil $whereRol
            ORDER BY SP.id_perfil, SRP.id_roles, SRM.id_menu
        ", $bindings);

        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $r) {
            $key = $r->id_usuario . '|' . $r->id_perfil . '|' . $r->id_roles;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'id_usuario' => $r->id_usuario,
                    'nombre_usuario' => $r->nombre_usuario,
                    'email' => $r->email,
                    'nombre_perfil' => $r->nombre_perfil,
                    'id_perfil' => $r->id_perfil,
                    'id_roles' => $r->id_roles,
                    'menus' => [],
                ];
            }
            $mid = $r->id_menu;
            if ($mid !== null) {
                if (!isset($grouped[$key]['menus'][$mid])) {
                    $grouped[$key]['menus'][$mid] = [];
                }
                if ($r->id_objetos !== null && $r->id_menu_objetos_permiso !== null) {
                    $grouped[$key]['menus'][$mid][] = $r->id_objetos;
                }
            }
        }

        $result = [];
        foreach ($grouped as $g) {
            // Menús asignados al rol sin filas en sistema_menu_objetos (ej. menú nuevo "prueba")
            if ($g['id_roles'] !== null) {
                $roleMenuIds = DB::table('seguridad_roles_menu')
                    ->where('id_roles', $g['id_roles'])
                    ->pluck('id_menu');
                foreach ($roleMenuIds as $idMenu) {
                    $idMenu = (int) $idMenu;
                    if (!isset($g['menus'][$idMenu])) {
                        $g['menus'][$idMenu] = [];
                    }
                }
            }

            $parts = [];
            foreach ($g['menus'] as $id_menu => $objetos) {
                $objetos = array_unique(array_filter($objetos));
                // Incluir menú aunque no tenga objetos (acceso a la ruta / sidebar multinivel)
                $parts[] = '|' . $id_menu . '_' . implode(',', $objetos) . '|';
            }

            $modulosPermitidos = [];
            if ($g['id_roles'] !== null && Schema::hasTable('seguridad_roles_modulo')) {
                $modulosPermitidos = DB::table('seguridad_roles_modulo')
                    ->where('id_roles', $g['id_roles'])
                    ->pluck('id_modulo')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }

            // Módulos con objetos directos (sistema_modulo_objetos): agregar a menu_objetos y url_menu_map
            // Convención: id_modulo + 100000 para no colisionar con id_menu (useAccesosObjetos lo usa igual)
            if ($g['id_roles'] !== null && Schema::hasTable('seguridad_modulo_objetos_roles') && Schema::hasTable('sistema_modulo_objetos')) {
                $modulosConObjetos = DB::table('seguridad_modulo_objetos_roles as SMOR')
                    ->join('sistema_modulo_objetos as SMO', 'SMO.id_modulo_objetos', '=', 'SMOR.id_modulo_objetos')
                    ->where('SMOR.id_roles', $g['id_roles'])
                    ->where(function ($q) {
                        $q->where('SMO.Activo', 'S')->orWhereNull('SMO.Activo');
                    })
                    ->select('SMO.id_modulo', 'SMO.id_objetos')
                    ->get();

                $byModulo = [];
                foreach ($modulosConObjetos as $row) {
                    $virtId = 100000 + (int) $row->id_modulo;
                    if (!isset($byModulo[$virtId])) {
                        $byModulo[$virtId] = [];
                    }
                    if ($row->id_objetos !== null) {
                        $byModulo[$virtId][] = $row->id_objetos;
                    }
                }
                foreach ($byModulo as $virtId => $objetos) {
                    $objetos = array_unique(array_filter($objetos));
                    $parts[] = '|' . $virtId . '_' . implode(',', $objetos) . '|';
                }
            }

            // Mapa URL → id (menús o módulos) para que el frontend detecte permisos
            $menuIds = array_keys($g['menus']);
            $urlMenuMap = [];
            if (!empty($menuIds)) {
                $urlMenuMap = [];
                $urlRows = DB::table('sistema_menu')
                    ->whereIn('id_menu', $menuIds)
                    ->whereNotNull('url')
                    ->where('url', '!=', '')
                    ->orderBy('id_menu')
                    ->get(['id_menu', 'url']);
                foreach ($urlRows as $row) {
                    $url = $row->url;
                    $url = $url[0] === '/' ? $url : '/' . $url;
                    // Primera ocurrencia gana (evita que un menú nuevo con URL duplicada pise al original)
                    if (!isset($urlMenuMap[$url])) {
                        $urlMenuMap[$url] = (int) $row->id_menu;
                    }
                }
            }
            // URLs de módulos (Productos, Categoria con link directo)
            if (!empty($modulosPermitidos) && Schema::hasColumn('sistema_modulo', 'url')) {
                $modulosConUrl = DB::table('sistema_modulo')
                    ->whereIn('id_modulo', $modulosPermitidos)
                    ->whereNotNull('url')
                    ->where('url', '!=', '')
                    ->get();
                foreach ($modulosConUrl as $mod) {
                    $url = $mod->url;
                    if ($url !== null && $url !== '') {
                        $url = $url[0] === '/' ? $url : '/' . $url;
                        $urlMenuMap[$url] = 100000 + (int) $mod->id_modulo;
                    }
                }
            }

            $menusPermitidos = array_map('intval', array_keys($g['menus']));

            $result[] = (object) [
                'id_usuario' => $g['id_usuario'],
                'nombre_usuario' => $g['nombre_usuario'],
                'email' => $g['email'],
                'nombre_perfil' => $g['nombre_perfil'],
                'id_perfil' => $g['id_perfil'],
                'id_roles' => $g['id_roles'],
                'menu_objetos' => implode('-', $parts),
                'menus_permitidos' => $menusPermitidos,
                'modulos_permitidos' => $modulosPermitidos,
                'url_menu_map' => $urlMenuMap,
            ];
        }
        return $result;
    }

    /**
     * Combina los datos del usuario con el conteo de perfiles
     */
    private function mergeUserWithProfileCount($user, $profileData)
    {
        return array_merge(
            $user->toArray(),
            ['count' => count($profileData)]
        );
    }

    /**
     * Añade avatar en base64 al array del usuario si tiene avatar en storage
     */
    private function addAvatarBase64($userData)
    {
        $avatarPath = is_array($userData) ? ($userData['avatar'] ?? null) : $userData->avatar;
        if (empty($avatarPath)) {
            return $userData;
        }
        try {
            $content = Storage::get($avatarPath);
            $base64 = $content ? base64_encode($content) : null;
        } catch (\Exception $e) {
            $base64 = null;
        }
        if ($base64 === null) {
            return $userData;
        }
        if (is_array($userData)) {
            $userData['avatar_base64'] = $base64;
            return $userData;
        }
        $arr = $userData->toArray();
        $arr['avatar_base64'] = $base64;
        return $arr;
    }

    /**
     * Genera tokens para el usuario
     */
    /**
     * @param  int  $refreshExpiresDays  Días de validez del refresh (menor si "Recordarme" está desactivado).
     */
    private function generateTokens($user, ?int $refreshExpiresDays = null)
    {
        $days = $refreshExpiresDays ?? $this->refreshTokenDays(true);

        // Generar JWT token
        $token = JWTAuth::claims([
            'id' => $user->id,
            'exp' => Carbon::now()->addMinutes($this->tokenExpiration)->timestamp
        ])->fromUser($user);

        // Generar refresh token
        $refreshToken = RefreshToken::generateToken($user->id, $days);

        return [
            'token' => $token,
            'refreshToken' => $refreshToken->token,
            'tokenExpires' => Carbon::now()->addMinutes($this->tokenExpiration)->timestamp,
            'refreshTokenExpires' => $refreshToken->expires_at->timestamp
        ];
    }

    /**
     * Maneja el login del usuario
     */
    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Auth"},
     *     summary="Iniciar sesión",
     *     description="Autentica al usuario y retorna JWT + refresh token",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(response=200, description="Login exitoso",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(response=422, description="Credenciales incorrectas",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $local = SeguridadAuthProveedor::where('codigo', 'local')->first();
        if (!$local || !$local->is_habilitado) {
            return response([
                'message' => 'El inicio de sesión con correo y contraseña no está habilitado.',
                'status' => 403,
            ], 403);
        }

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            SeguridadAuthLoginLog::registrar('local', false, null, $request->input('email'), $request->ip(), 'Credenciales incorrectas');
            return response([
                'message' => 'El correo electrónico o la contraseña no son correctos. Verifique sus credenciales e intente de nuevo.',
                'status' => 422,
            ], 422);
        }

        $user = auth()->user();
        try {
            app(\App\Services\Seguridad\UsuarioAuthConfigService::class)->assertMetodoPermitido($user, 'local');
        } catch (\Throwable $e) {
            auth()->logout();
            return response([
                'message' => $e->getMessage(),
                'status' => 403,
            ], 403);
        }

        $remember = $request->boolean('remember', true);
        $twoFactor = app(\App\Services\Auth\AuthTwoFactorService::class);
        $payload = $twoFactor->respuestaLoginOChallenge(
            $user,
            $remember,
            $request->ip(),
            $request->input('trusted_device_token')
        );

        if (empty($payload['requires_2fa'])) {
            SeguridadAuthLoginLog::registrar('local', true, $user->id, $user->email, $request->ip());
        } else {
            SeguridadAuthLoginLog::registrar('local', true, $user->id, $user->email, $request->ip(), 'Pendiente verificación 2FA');
        }

        return response($payload);
    }

    /**
     * Respuesta estándar de login (JWT + perfil). Usado por OAuth y login local.
     */
    public function buildLoginResponseForUser(User $user, bool $remember = true): array
    {
        $tokens = $this->generateTokens($user, $this->refreshTokenDays($remember));
        $perfilData = $this->getPerfilData($user->id);

        return [
            'status' => 200,
            'token' => $tokens['token'],
            'user' => $this->addAvatarBase64($this->mergeUserWithProfileCount($user, $perfilData)),
            'perfil' => $perfilData,
            'refreshToken' => $tokens['refreshToken'],
        ];
    }

    /**
     * Refresca el token usando el refresh token
     */
    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Auth"},
     *     summary="Refrescar token JWT",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"refreshToken"},
     *             @OA\Property(property="refreshToken", type="string", example="abc123refreshtoken")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token renovado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(response=401, description="Refresh token inválido o expirado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refreshToken' => 'required|string'
        ]);

        // Buscar el refresh token
        $refreshToken = RefreshToken::where('token', $request->refreshToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$refreshToken) {
            return response([
                'code' => 401,
                'message' => 'Refresh token inválido o expirado',
                'success' => false
            ], 401);
        }

        // Obtener el usuario
        $user = $refreshToken->user;

        // Eliminar el refresh token usado
        $refreshToken->delete();

        // Generar nuevos tokens (mantener sesión larga al renovar)
        $tokens = $this->generateTokens($user, $this->refreshTokenDays(true));
        $perfilData = $this->getPerfilData($user->id);

        return response([
            'status' => 200,
            'code' => 200,
            'message' => 'Token refrescado exitosamente',
            'token' => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
            'user' => $this->addAvatarBase64($this->mergeUserWithProfileCount($user, $perfilData)),
            'perfil' => $perfilData,
            'result' => [
                'refreshToken' => $tokens['refreshToken'],
                'token' => $tokens['token'],
                'tokenExpires' => $tokens['tokenExpires'],
                'refreshTokenExpires' => $tokens['refreshTokenExpires'],
                'success' => true,
                'user' => $this->mergeUserWithProfileCount($user, $perfilData),
                'perfil' => $perfilData,
            ]
        ]);
    }

    /**
     * Cierra la sesión y elimina los tokens
     */
    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Auth"},
     *     summary="Cerrar sesión",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"refreshToken"},
     *             @OA\Property(property="refreshToken", type="string", example="abc123refreshtoken")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Sesión cerrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente"),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->validate([
            'refreshToken' => 'required|string'
        ]);

        RefreshToken::where('token', $request->refreshToken)->delete();

        try {
            if (JWTAuth::getToken()) {
                JWTAuth::invalidate(JWTAuth::getToken());
            }
        } catch (\Exception $e) {
            // Sin Bearer token (p. ej. sesión ya expirada)
        }

        return response([
            'code' => 200,
            'message' => 'Sesión cerrada exitosamente',
            'success' => true
        ]);
    }

    /**
     * Valida la conexión del usuario
     */
    /**
     * @OA\Post(
     *     path="/perfil/validar_conexion",
     *     tags={"Auth"},
     *     summary="Validar conexión activa del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Sesión activa",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function validar_conexion(Request $request)
    {
        $user = auth()->user();
        $perfilData = $this->getPerfilData($user->id);

        return response([
            'status' => 200,
            'token' => null,
            'user' => $this->addAvatarBase64($user->toArray()),
            'perfil' => $perfilData,
        ]);
    }

    /**
     * Valida el perfil del usuario. Recibe id_perfil e id_rol (id_roles) del formulario de selección.
     * Devuelve un único perfil con menu_objetos y modulos_permitidos de ese rol para que el sidebar muestre solo lo permitido.
     */
    /**
     * @OA\Post(
     *     path="/perfil/validar_perfil",
     *     tags={"Auth"},
     *     summary="Seleccionar perfil/rol activo del usuario",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id_perfil", type="integer", example=1),
     *             @OA\Property(property="id_rol", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Perfil cargado",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     )
     * )
     */
    public function validar_perfil(Request $request)
    {
        $user = auth()->user();
        $idPerfil = $request->input('id_perfil');
        $idRol = $request->input('id_rol');
        $perfilData = $this->getPerfilData($user->id, $idPerfil, $idRol);

        return response([
            'status' => 200,
            'user' => $this->addAvatarBase64($this->mergeUserWithProfileCount($user, $perfilData)),
            'perfil' => $perfilData,
        ]);
    }

    /**
     * Envía enlace de recuperación por correo
     */
    /**
     * @OA\Post(
     *     path="/auth/olvido-contrasena",
     *     tags={"Auth"},
     *     summary="Solicitar recuperación de contraseña",
     *     description="Envía un correo con el token de recuperación al email especificado",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"email"},
     *         @OA\Property(property="email", type="string", format="email", example="usuario@ejemplo.com")
     *     )),
     *     @OA\Response(response=200, description="Correo enviado"),
     *     @OA\Response(response=422, description="Email no encontrado"),
     *     @OA\Response(response=500, description="Error al enviar correo")
     * )
     */
    public function olvido_contrasena(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(64);

        try {
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'created_at' => Carbon::now()]
            );

            Mail::send('email.forgetPassword', ['token' => $token, 'email' => $request->email], function($message) use($request){
                $message->to($request->email);
                $message->subject('Recuperación de contraseña - Valento');
            });

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => '¡Le hemos enviado por correo electrónico su enlace para restablecer su contraseña!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa el cambio de contraseña con el token
     */
    /**
     * @OA\Post(
     *     path="/auth/restablecer-contrasena",
     *     tags={"Auth"},
     *     summary="Restablecer contraseña con token",
     *     description="Actualiza la contraseña del usuario usando el token recibido por correo",
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"email","token","password","password_confirmation"},
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="token", type="string"),
     *         @OA\Property(property="password", type="string", minLength=6),
     *         @OA\Property(property="password_confirmation", type="string")
     *     )),
     *     @OA\Response(response=200, description="Contraseña cambiada exitosamente"),
     *     @OA\Response(response=422, description="Token inválido o error de validación")
     * )
     */
    public function restablecer_contrasena(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:6|confirmed',
            'token' => 'required|string'
        ]);

        $resetData = DB::table('password_resets')
            ->where(['email' => $request->email, 'token' => $request->token])
            ->first();

        if (!$resetData) {
            return response()->json([
                'status' => 422,
                'success' => false,
                'message' => 'El token de recuperación es inválido o ha expirado.'
            ], 422);
        }

        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where(['email' => $request->email])->delete();

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Su contraseña ha sido cambiada exitosamente.'
        ], 200);
    }
}