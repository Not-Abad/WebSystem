<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_alumno', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_estado_alumno');
            $table->string('nombre', 40)->unique('uk_estados_alumno_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_alumno');
    }
};
