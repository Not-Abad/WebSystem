<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->string('estatus', 16)->default('ordinaria')->after('tipo');
            $table->string('formato_calificacion', 8)->default('decimal')->after('estatus');
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->unsignedTinyInteger('parcial')->nullable()->after('fecha');
        });

        Schema::table('reinco_exportaciones', function (Blueprint $table) {
            $table->string('estatus', 16)->default('ordinaria')->after('parcial');
            $table->string('formato_calificacion', 8)->default('decimal')->after('estatus');
            $table->string('turno', 20)->nullable()->after('formato_calificacion');
            $table->string('periodo_escolar', 16)->nullable()->after('turno');
            $table->string('cct', 16)->nullable()->after('periodo_escolar');
            $table->string('plantel', 150)->nullable()->after('cct');
            $table->unsignedTinyInteger('hojas')->default(5)->after('plantel');
        });
    }

    public function down(): void
    {
        Schema::table('reinco_exportaciones', function (Blueprint $table) {
            $table->dropColumn([
                'hojas',
                'plantel',
                'cct',
                'periodo_escolar',
                'turno',
                'formato_calificacion',
                'estatus',
            ]);
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropColumn('parcial');
        });

        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropColumn(['formato_calificacion', 'estatus']);
        });
    }
};
