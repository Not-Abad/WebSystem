<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El alta de un alumno solo requiere nombre, apellido y cuenta de acceso.
     * El resto de datos institucionales se capturan cuando estén disponibles.
     */
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_grupo')->nullable()->default(null)->change();
            $table->unsignedBigInteger('id_estado_alumno')->nullable()->change();
            $table->unsignedBigInteger('id_plan')->nullable()->change();
            $table->unsignedBigInteger('id_semestre')->nullable()->change();
            $table->char('curp', 18)->nullable()->change();
            $table->string('acta_nacimiento_ruta', 255)->nullable()->change();
            $table->string('boleta_secundaria_ruta', 255)->nullable()->change();
            $table->string('correo', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('alumnos')->whereNull('id_grupo')->update(['id_grupo' => 1]);
        DB::table('alumnos')->whereNull('id_estado_alumno')->update(['id_estado_alumno' => 1]);
        DB::table('alumnos')->whereNull('id_plan')->update(['id_plan' => 1]);
        DB::table('alumnos')->whereNull('id_semestre')->update(['id_semestre' => 1]);
        DB::table('alumnos')->whereNull('curp')->update(['curp' => '']);
        DB::table('alumnos')->whereNull('acta_nacimiento_ruta')->update(['acta_nacimiento_ruta' => '']);
        DB::table('alumnos')->whereNull('boleta_secundaria_ruta')->update(['boleta_secundaria_ruta' => '']);
        DB::table('alumnos')->whereNull('correo')->update(['correo' => '']);

        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_grupo')->nullable(false)->default(1)->change();
            $table->unsignedBigInteger('id_estado_alumno')->nullable(false)->change();
            $table->unsignedBigInteger('id_plan')->nullable(false)->change();
            $table->unsignedBigInteger('id_semestre')->nullable(false)->change();
            $table->char('curp', 18)->nullable(false)->change();
            $table->string('acta_nacimiento_ruta', 255)->nullable(false)->change();
            $table->string('boleta_secundaria_ruta', 255)->nullable(false)->change();
            $table->string('correo', 120)->nullable(false)->change();
        });
    }
};
