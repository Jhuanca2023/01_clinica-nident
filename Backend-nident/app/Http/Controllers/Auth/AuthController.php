<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use App\Models\UserVerify;
use Mail;
use Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function index(): View
    {
        return view('auth.login');
    }

    public function registration()
    {
        return view('auth.register');
    }

    public function postLogin(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Si piden JSON (ej. Postman), indicar que usen la API
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Use POST /api/login for API authentication.',
                'endpoint' => url('/api/login'),
            ], 400);
        }

        $request->validate([
            'email' => 'required',
            'password' => 'required',

        ]); 

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            Session::put('email', $request->email);
            Session::put('password', $request->password);
            Session::put('login_provider', 'password');
            return redirect()->intended('/ope_pedidos/listar_pedidos')->withSuccess('Has iniciado sesión correctamente');
        }
        return redirect("login")->withErrors('Oppes!. Has introducido credenciales no válidas.');
    }

    public function postRegistration(Request $request): RedirectResponse
    {  
        // dd($request->all());
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'apellido'        => 'nullable|string|max:100',
            'telefono'        => 'nullable|string|max:30',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|min:8|confirmed',
            'acepto_terminos' => 'accepted',
        ], [
            'password.min'             => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'       => 'Las contraseñas no coinciden.',
            'email.unique'             => 'Ya existe una cuenta con ese correo.',
            'acepto_terminos.accepted' => 'Debes aceptar los Términos y Condiciones y la Política de Privacidad.',
        ]);

        try {

            DB::beginTransaction();

            $data = $request->all();
            $data['name'] = trim($request->nombre . ' ' . ($request->apellido ?? ''));
            $createUser = $this->create($data);
            //========== CREA UN CLIENTE
            DB::table('administracion_cliente')->insert([
                'nombre'        => $request->nombre,
                'apellido'      => $request->apellido,
                'telefono'      => $request->telefono,
                'email'         => $request->email,
                'id_usuario'    => $createUser->id,
                'Activo'        => 'S'
            ]);
            //======================
            $token = Str::random(64);
    
              UserVerify::create([
                  'user_id' => $createUser->id, 
                  'token' => $token
                ]);
      
            Mail::send('email.emailVerificationEmail', ['token' => $token, 'nombre' => $request->nombre], function($message) use($request){
                  $message->to($request->email);
                  $message->subject('Confirma tu correo — Valento');
              });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
     
        //======================
        // dd();
        // exit;
        // return redirect("login")->with('success', 'Registro exitoso. Por favor verifica tu correo electrónico.');

        return redirect("login")->withSuccess('Registro exitoso. Por favor verifica tu correo electrónico.');
    }

    public function dashboard(): RedirectResponse
    {
        if(Auth::check()){
            return redirect("home");
        }
        return redirect("login")->withErrors('Opps! No tienes acceso.');
    }

    public function create(array $data)
    {
      return User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password'])
      ]);
    }

    public function logout(): RedirectResponse
    {
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }

    public function verifyAccount($token): RedirectResponse

    {
        $verifyUser = UserVerify::where('token', $token)->first();
        $message = 'Sorry your email cannot be identified.';

          if(!is_null($verifyUser) ){

            $user = $verifyUser->user;            

            if(!$user->is_email_verified) {
                $verifyUser->user->is_email_verified = 1;
                $verifyUser->user->save();
                $message = "Tu correo electrónico ha sido verificado. Ya puedes iniciar sesión.";
            } else {
                $message = "Your e-mail is already verified. You can now login.";
            }
        }

        return redirect()->route('login')->withSuccess($message);

    }

}