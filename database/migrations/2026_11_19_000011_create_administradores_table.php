<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administradores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_administrador');
            $table->unsignedBigInteger('id_usuario')->unique('uk_administradores_usuario');
            $table->unsignedBigInteger('id_puesto');
            $table->string('nombre', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60)->nullable();
            $table->string('matricula_rfc', 20)->unique('uk_administradores_matricula');
            $table->string('correo_institucional', 120)->unique('uk_administradores_correo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('id_puesto', 'idx_administradores_puesto');
            $table->foreign('id_usuario', 'fk_administradores_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_puesto', 'fk_administradores_puesto')
                ->references('id_puesto')->on('puestos_administrativos')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
