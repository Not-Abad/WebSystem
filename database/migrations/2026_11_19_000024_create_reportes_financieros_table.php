<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_financieros', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_reporte');
            $table->unsignedBigInteger('id_administrador');
            $table->string('titulo', 150);
            $table->unsignedTinyInteger('mes');
            $table->year('anio');
            $table->string('archivo_pdf_ruta', 255);
            $table->dateTime('fecha_publicacion')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mes', 'anio'], 'uk_reportes_mes_anio');
            $table->index('id_administrador', 'idx_reportes_admin');
            $table->foreign('id_administrador', 'fk_reportes_administrador')
                ->references('id_administrador')->on('administradores')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        DB::statement('ALTER TABLE `reportes_financieros` ADD CONSTRAINT `chk_reportes_mes` CHECK (`mes` BETWEEN 1 AND 12)');
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_financieros');
    }
};
