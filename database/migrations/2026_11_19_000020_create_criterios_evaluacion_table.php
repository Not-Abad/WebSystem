<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterios_evaluacion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_criterio');
            $table->unsignedBigInteger('id_clase');
            $table->unsignedTinyInteger('parcial');
            $table->string('nombre_rubro', 60);
            $table->decimal('porcentaje', 5, 2);

            $table->index(['id_clase', 'parcial'], 'idx_criterios_clase_parcial');
            $table->foreign('id_clase', 'fk_criterios_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        DB::statement('ALTER TABLE `criterios_evaluacion` ADD CONSTRAINT `chk_criterios_parcial` CHECK (`parcial` IN (1, 2, 3))');
        DB::statement('ALTER TABLE `criterios_evaluacion` ADD CONSTRAINT `chk_criterios_porcentaje` CHECK (`porcentaje` > 0.00 AND `porcentaje` <= 100.00)');
    }

    public function down(): void
    {
        Schema::dropIfExists('criterios_evaluacion');
    }
};
