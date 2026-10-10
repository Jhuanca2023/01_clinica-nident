<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     title="Valento API",
 *     version="1.0.0",
 *     description="API REST del sistema ecommerce Valento. Todos los endpoints protegidos requieren Bearer Token JWT.",
 *     @OA\Contact(email="ventas@rossety.pe"),
 *     @OA\License(name="MIT")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor de Producción"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor Local (desarrollo)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth", description="Autenticación y sesión")
 * @OA\Tag(name="Backup", description="Respaldos de base de datos")
 * @OA\Tag(name="Balance", description="Balance de ventas y reportes")
 * @OA\Tag(name="Clientes", description="Gestión de clientes")
 * @OA\Tag(name="Configuración Envíos", description="Costos y tarifas de envío")
 * @OA\Tag(name="Empleados", description="Gestión de empleados")
 * @OA\Tag(name="File Management", description="Gestión de archivos del servidor")
 * @OA\Tag(name="Menú", description="Gestión del menú del sistema")
 * @OA\Tag(name="Módulos", description="Gestión de módulos del sistema")
 * @OA\Tag(name="Objetos", description="Objetos del sistema (permisos granulares)")
 * @OA\Tag(name="Pasarelas de Pago", description="Configuración de Culqi, Yape, PayPal, etc.")
 * @OA\Tag(name="Pedidos", description="Gestión de pedidos")
 * @OA\Tag(name="Perfiles", description="Perfiles de seguridad")
 * @OA\Tag(name="Productos", description="Gestión de productos y categorías")
 * @OA\Tag(name="Programación", description="Programación de tareas o eventos")
 * @OA\Tag(name="Roles", description="Roles y permisos")
 * @OA\Tag(name="Sitio Web", description="Gestión de contenido del sitio web (Banners, Sliders, Footer, Ofertas, etc.)")
 * @OA\Tag(name="Ubigeo", description="Ubicaciones geográficas (Perú)")
 * @OA\Tag(name="Usuarios", description="Gestión de usuarios del sistema")
 * @OA\Tag(name="Vendedor", description="Gestión de vendedores")
 *
 * ===================== SCHEMAS =====================
 *
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operación exitosa"),
 *     @OA\Property(property="result", type="object")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error en la operación")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="juan@valento.com"),
 *     @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *     @OA\Property(property="avatar", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Producto",
 *     @OA\Property(property="id_producto", type="integer", example=1),
 *     @OA\Property(property="codigo_producto", type="string", example="P-ABC123"),
 *     @OA\Property(property="nombre", type="string", example="Maleta de Viaje"),
 *     @OA\Property(property="descripcion", type="string", nullable=true),
 *     @OA\Property(property="precio", type="number", format="float", example=99.90),
 *     @OA\Property(property="precio_old", type="number", format="float", nullable=true, example=129.90),
 *     @OA\Property(property="stock", type="integer", example=50),
 *     @OA\Property(property="url_imagen", type="string", nullable=true),
 *     @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S"),
 *     @OA\Property(property="id_producto_categoria", type="integer", example=1),
 *     @OA\Property(property="id_producto_categoria_sub", type="integer", nullable=true),
 *     @OA\Property(property="peso_kilogramo", type="number", nullable=true),
 *     @OA\Property(property="precio_mayorista", type="number", nullable=true),
 *     @OA\Property(property="precio_yape", type="number", nullable=true),
 *     @OA\Property(property="numero_estrellas", type="integer", nullable=true),
 *     @OA\Property(property="ratings_enabled", type="integer", enum={0,1}, example=0)
 * )
 *
 * @OA\Schema(
 *     schema="Pedido",
 *     @OA\Property(property="id_pedido", type="integer", example=100),
 *     @OA\Property(property="codigo_pedido", type="string", example="PED-2024-001"),
 *     @OA\Property(property="estado", type="integer", enum={1,2,3,4,5,6,7}, example=1,
 *         description="1=Recibido, 2=Confirmado, 3=Preparando, 4=Listo, 5=En camino, 6=Entregado, 7=Cancelado"),
 *     @OA\Property(property="total", type="number", example=199.90),
 *     @OA\Property(property="devolucion", type="string", nullable=true),
 *     @OA\Property(property="id_usuario", type="integer", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="OfertaDelDia",
 *     @OA\Property(property="id_oferta_dia", type="integer", example=1),
 *     @OA\Property(property="id_producto", type="integer", example=5),
 *     @OA\Property(property="nombre_oferta", type="string", example="Oferta de la semana"),
 *     @OA\Property(property="precio_oferta", type="number", example=79.90),
 *     @OA\Property(property="precio_original", type="number", example=99.90),
 *     @OA\Property(property="start_time", type="string", format="date-time"),
 *     @OA\Property(property="end_time", type="string", format="date-time"),
 *     @OA\Property(property="cantidad_disponible", type="integer", example=100),
 *     @OA\Property(property="cantidad_vendida", type="integer", example=0),
 *     @OA\Property(property="Activo", type="string", enum={"S","N"}, example="S")
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     required={"email","password"},
 *     @OA\Property(property="email", type="string", format="email", example="ventas@rossety.pe"),
 *     @OA\Property(property="password", type="string", format="password", example="secret123"),
 *     @OA\Property(property="remember", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="LoginResponse",
 *     @OA\Property(property="status", type="integer", example=200),
 *     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *     @OA\Property(property="refreshToken", type="string", example="abc123refreshtoken"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="perfil", type="array", @OA\Items(type="object"))
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedListResponse",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Listar registros"),
 *     @OA\Property(property="result", type="array", @OA\Items(type="object"))
 * )
 */
class SwaggerController extends Controller
{
    // Este controlador solo contiene las anotaciones OpenAPI base.
    // No expone rutas propias.
}
