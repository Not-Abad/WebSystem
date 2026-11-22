<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->string('curp', 18)->nullable()->after('apellido_materno');
            $table->string('rfc', 20)->nullable()->after('curp');
            $table->unsignedInteger('horas_semanales')->default(35)->after('correo_institucional');
            $table->string('licenciatura', 120)->nullable()->after('horas_semanales');
            $table->string('especialidad', 120)->nullable()->after('licenciatura');
            $table->string('maestria', 120)->nullable()->after('especialidad');
            $table->string('doctorado', 120)->nullable()->after('maestria');
        });

        // Los perfiles existentes usaban matricula_rfc como identificador fiscal.
        DB::table('docentes')
            ->whereNotNull('matricula_rfc')
            ->whereNull('rfc')
            ->update(['rfc' => DB::raw('matricula_rfc')]);

        Schema::table('docentes', function (Blueprint $table) {
            $table->unique('curp', 'uk_docentes_curp');
            $table->unique('rfc', 'uk_docentes_rfc');
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropUnique('uk_docentes_curp');
            $table->dropUnique('uk_docentes_rfc');
            $table->dropColumn([
                'curp',
                'rfc',
                'horas_semanales',
                'licenciatura',
                'especialidad',
                'maestria',
                'doctorado',
            ]);
        });
    }
};
