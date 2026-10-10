<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('seguridad_auth_proveedor')) {
            Schema::create('seguridad_auth_proveedor', function (Blueprint $table) {
                $table->id('id_seguridad_auth_proveedor');
                $table->string('codigo', 32)->unique();
                $table->string('nombre', 120);
                $table->string('descripcion', 500)->nullable();
                $table->string('logo_url', 255)->nullable();
                $table->boolean('is_habilitado')->default(false);
                $table->boolean('is_predeterminado')->default(false);
                $table->unsignedSmallInteger('orden')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seguridad_auth_proveedor_config')) {
            Schema::create('seguridad_auth_proveedor_config', function (Blueprint $table) {
                $table->id('id_seguridad_auth_proveedor_config');
                $table->unsignedBigInteger('id_seguridad_auth_proveedor');
                $table->string('clave', 100);
                $table->text('valor')->nullable();
                $table->string('etiqueta', 150)->nullable();
                $table->boolean('es_secreto')->default(false);
                $table->timestamps();
                $table->foreign('id_seguridad_auth_proveedor', 'fk_auth_prov_config_prov')
                    ->references('id_seguridad_auth_proveedor')->on('seguridad_auth_proveedor')
                    ->onDelete('cascade');
                $table->unique(['id_seguridad_auth_proveedor', 'clave'], 'uq_auth_prov_config_clave');
            });
        }

        if (!Schema::hasTable('seguridad_usuario_oauth')) {
            Schema::create('seguridad_usuario_oauth', function (Blueprint $table) {
                $table->id('id_seguridad_usuario_oauth');
                $table->unsignedBigInteger('id_usuario');
                $table->string('proveedor', 32);
                $table->string('provider_user_id', 191);
                $table->string('email', 191)->nullable();
                $table->text('avatar')->nullable();
                $table->timestamps();
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['proveedor', 'provider_user_id'], 'uq_usuario_oauth_prov');
            });
        }

        if (!Schema::hasTable('seguridad_auth_oauth_sesion')) {
            Schema::create('seguridad_auth_oauth_sesion', function (Blueprint $table) {
                $table->id('id_seguridad_auth_oauth_sesion');
                $table->string('state', 64)->unique();
                $table->string('code_verifier', 128)->nullable();
                $table->string('proveedor', 32);
                $table->string('exchange_code', 64)->nullable()->unique();
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seguridad_auth_login_log')) {
            Schema::create('seguridad_auth_login_log', function (Blueprint $table) {
                $table->id('id_seguridad_auth_login_log');
                $table->string('proveedor', 32);
                $table->unsignedBigInteger('id_usuario')->nullable();
                $table->string('email', 191)->nullable();
                $table->string('ip', 45)->nullable();
                $table->char('exito', 1)->default('N');
                $table->string('mensaje', 500)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('seguridad_usuario_2fa')) {
            Schema::create('seguridad_usuario_2fa', function (Blueprint $table) {
                $table->id('id_seguridad_usuario_2fa');
                $table->unsignedBigInteger('id_usuario')->unique();
                $table->text('secret_cifrado')->nullable();
                $table->boolean('is_habilitado')->default(false);
                $table->timestamp('habilitado_at')->nullable();
                $table->text('recovery_codes_hash')->nullable();
                $table->timestamps();
                $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'microsoft_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('microsoft_id', 191)->nullable()->after('google_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'microsoft_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('microsoft_id');
            });
        }
        Schema::dropIfExists('seguridad_usuario_2fa');
        Schema::dropIfExists('seguridad_auth_login_log');
        Schema::dropIfExists('seguridad_auth_oauth_sesion');
        Schema::dropIfExists('seguridad_usuario_oauth');
        Schema::dropIfExists('seguridad_auth_proveedor_config');
        Schema::dropIfExists('seguridad_auth_proveedor');
    }
};
