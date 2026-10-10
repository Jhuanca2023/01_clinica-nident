<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificacion_push_subscription')) {
            return;
        }

        Schema::create('notificacion_push_subscription', function (Blueprint $table) {
            $table->id('id_subscription');
            $table->unsignedBigInteger('id_user');
            $table->string('endpoint', 500);
            $table->string('p256dh', 255);
            $table->string('auth', 255);
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->unique(['id_user', 'endpoint'], 'notif_push_user_endpoint');
            $table->index('id_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_push_subscription');
    }
};
