<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('username', 60)->unique('uk_usuarios_username');
            $table->string('password_hash', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->dateTime('bloqueado_hasta')->nullable();
            $table->dateTime('ultimo_acceso')->nullable();
            $table->string('two_factor_hash', 255)->nullable();
            $table->timestamps();

            $table->index('id_rol', 'idx_usuarios_rol');
            $table->foreign('id_rol', 'fk_usuarios_rol')
                ->references('id_rol')->on('roles')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
