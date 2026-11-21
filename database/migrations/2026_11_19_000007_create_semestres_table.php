<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semestres', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_semestre');
            $table->unsignedTinyInteger('numero_semestre')->unique('uk_semestres_numero');
            $table->string('nombre', 30)->unique('uk_semestres_nombre');
        });
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `semestres` ADD CONSTRAINT `chk_semestres_rango` CHECK (`numero_semestre` BETWEEN 1 AND 6)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('semestres');
    }
};
