<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_asistencia');
            $table->unsignedBigInteger('id_clase');
            $table->unsignedBigInteger('id_alumno');
            $table->date('fecha');
            $table->string('estatus', 20)->default('presente');
            $table->string('observaciones', 200)->nullable();
            $table->timestamps();

            $table->unique(['id_clase', 'id_alumno', 'fecha'], 'uk_asistencias_clase_alumno_fecha');
            $table->index('id_alumno', 'idx_asistencias_alumno');

            $table->foreign('id_clase', 'fk_asistencias_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_alumno', 'fk_asistencias_alumno')
                ->references('id_alumno')->on('alumnos')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_evaluacion');
            $table->unsignedBigInteger('id_clase');
            $table->unsignedTinyInteger('parcial');
            $table->string('tipo', 20)->default('examen');
            $table->date('fecha_examen')->nullable();
            $table->date('fecha_entrega_proyecto')->nullable();
            $table->timestamps();

            $table->unique(['id_clase', 'parcial'], 'uk_evaluaciones_clase_parcial');

            $table->foreign('id_clase', 'fk_evaluaciones_clase')
                ->references('id_clase')->on('clases')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('criterios_evaluacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_evaluacion')->nullable()->after('parcial');
            $table->string('tipo_evidencia', 60)->nullable()->after('nombre_rubro');
            $table->date('fecha_entrega')->nullable()->after('tipo_evidencia');

            $table->index('id_evaluacion', 'idx_criterios_evaluacion');
            $table->foreign('id_evaluacion', 'fk_criterios_evaluacion')
                ->references('id_evaluacion')->on('evaluaciones')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            // DROP CONSTRAINT funciona igual en MySQL 8 y MariaDB (XAMPP).
            DB::statement('ALTER TABLE `criterios_evaluacion` DROP CONSTRAINT `chk_criterios_porcentaje`');
            DB::statement('ALTER TABLE `criterios_evaluacion` MODIFY `porcentaje` DECIMAL(7,2) NOT NULL');
            DB::statement('ALTER TABLE `asistencias` ADD CONSTRAINT `chk_asistencias_estatus` CHECK (`estatus` IN (\'presente\', \'falta\', \'retardo\', \'justificada\'))');
            DB::statement('ALTER TABLE `evaluaciones` ADD CONSTRAINT `chk_evaluaciones_parcial` CHECK (`parcial` IN (1, 2, 3))');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `criterios_evaluacion` MODIFY `porcentaje` DECIMAL(5,2) NOT NULL');
            DB::statement('ALTER TABLE `criterios_evaluacion` ADD CONSTRAINT `chk_criterios_porcentaje` CHECK (`porcentaje` > 0.00 AND `porcentaje` <= 100.00)');
        }

        Schema::table('criterios_evaluacion', function (Blueprint $table) {
            $table->dropForeign('fk_criterios_evaluacion');
            $table->dropIndex('idx_criterios_evaluacion');
            $table->dropColumn(['id_evaluacion', 'tipo_evidencia', 'fecha_entrega']);
        });

        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('asistencias');
    }
};
