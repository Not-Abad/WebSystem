<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_periodo');
            $table->string('clave', 20)->unique('uk_periodos_clave');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('is_active')->default(true);
        });
        DB::statement('ALTER TABLE `periodos_academicos` ADD CONSTRAINT `chk_periodos_fechas` CHECK (`fecha_fin` > `fecha_inicio`)');
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_academicos');
    }
};
