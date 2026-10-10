<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('campus_onboarding_links')) return;

        Schema::create('campus_onboarding_links', function (Blueprint $table) {
            $table->id('id_onboarding');
            $table->unsignedBigInteger('id_cliente');
            $table->string('token', 80)->unique();

            // Vigencia
            $table->unsignedSmallInteger('dias_vigencia')->default(5);
            $table->dateTime('fecha_expiracion');
            $table->enum('estado', ['activo', 'inactivo', 'expirado'])->default('activo');

            // Datos empresa (configurados por el admin)
            $table->string('empresa_nombre', 200)->nullable();
            $table->string('empresa_banco', 120)->nullable();
            $table->string('empresa_cuenta', 80)->nullable();
            $table->string('empresa_cci', 80)->nullable();
            $table->string('empresa_titular', 200)->nullable();
            $table->string('empresa_ruc', 20)->nullable();
            $table->decimal('empresa_monto', 12, 2)->nullable();
            $table->string('empresa_qr_path', 500)->nullable();   // Imagen QR subida por admin
            $table->text('empresa_descripcion')->nullable();

            // Datos del formulario (llenados por el cliente)
            $table->string('form_nombres', 150)->nullable();
            $table->string('form_apellidos', 150)->nullable();
            $table->string('form_empresa', 200)->nullable();
            $table->string('form_ruc', 20)->nullable();
            $table->string('form_dni', 20)->nullable();
            $table->string('form_email', 160)->nullable();
            $table->string('form_telefono', 40)->nullable();
            $table->string('form_whatsapp', 40)->nullable();
            $table->string('form_direccion', 300)->nullable();
            $table->string('form_imagen_path', 500)->nullable();  // Imagen subida por cliente

            // Control de progreso
            $table->boolean('form_completado')->default(false);
            $table->boolean('pago_confirmado')->default(false);
            $table->unsignedTinyInteger('progreso')->default(0); // 0-100

            // Estadísticas de acceso
            $table->unsignedInteger('vistas')->default(0);
            $table->dateTime('primer_acceso')->nullable();
            $table->dateTime('ultimo_acceso')->nullable();

            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index(['id_cliente', 'estado']);
            $table->foreign('id_cliente')->references('id_cliente')->on('campus_clientes')->onDelete('cascade');
            $table->foreign('creado_por')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_onboarding_links');
    }
};
