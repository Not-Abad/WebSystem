<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un alumno puede tener hasta tres tutores. Se mantiene `alumnos.id_tutor`
     * como vínculo principal para compatibilidad, pero la tabla pivote es la
     * fuente de verdad para el límite y para el portal del tutor.
     */
    public function up(): void
    {
        Schema::create('alumno_tutores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_alumno_tutor');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_tutor');
            $table->timestamps();

            $table->unique(['id_alumno', 'id_tutor'], 'uk_alumno_tutor');
            $table->index(['id_tutor', 'id_alumno'], 'idx_tutor_alumno');

            $table->foreign('id_alumno', 'fk_alumnotutor_alumno')
                ->references('id_alumno')->on('alumnos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_tutor', 'fk_alumnotutor_tutor')
                ->references('id_tutor')->on('tutores')->onUpdate('cascade')->onDelete('cascade');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared(<<<'SQL'
                DROP TRIGGER IF EXISTS `trg_alumno_tutores_max3`;
                CREATE TRIGGER `trg_alumno_tutores_max3`
                BEFORE INSERT ON `alumno_tutores`
                FOR EACH ROW
                BEGIN
                    IF (SELECT COUNT(*) FROM `alumno_tutores` WHERE `id_alumno` = NEW.`id_alumno`) >= 3 THEN
                        SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'Un alumno solo puede tener 3 tutores como máximo.';
                    END IF;
                END
            SQL);
        }

        DB::table('alumno_tutores')->insertUsing(
            ['id_alumno', 'id_tutor', 'created_at', 'updated_at'],
            DB::table('alumnos')
                ->whereNotNull('id_tutor')
                ->select(
                    'id_alumno',
                    'id_tutor',
                    DB::raw('CURRENT_TIMESTAMP as created_at'),
                    DB::raw('CURRENT_TIMESTAMP as updated_at')
                )
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS `trg_alumno_tutores_max3`');
        }

        Schema::dropIfExists('alumno_tutores');
    }
};
