<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_tutor');
            $table->unsignedBigInteger('id_usuario')->unique('uk_tutores_usuario');
            $table->string('nombre', 60);
            $table->string('apellido_paterno', 60);
            $table->string('apellido_materno', 60)->nullable();
            $table->string('telefono', 15);
            $table->string('correo', 120)->unique('uk_tutores_correo');
            $table->string('ine_ruta', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_usuario', 'fk_tutores_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutores');
    }
};
