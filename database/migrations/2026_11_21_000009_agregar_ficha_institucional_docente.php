<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ficha institucional y laboral solicitada para el personal docente.
     */
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->string('numero_personal_sep', 30)->nullable()->after('doctorado');
            $table->string('contacto', 60)->nullable()->after('numero_personal_sep');
            $table->string('nombramiento', 100)->nullable()->after('contacto');
            $table->string('categoria', 100)->nullable()->after('nombramiento');
            $table->string('plaza', 60)->nullable()->after('categoria');
            $table->string('sindicato', 100)->nullable()->after('plaza');
            $table->string('titulo', 120)->nullable()->after('sindicato');
            $table->string('cedula_profesional', 40)->nullable()->after('titulo');
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn([
                'numero_personal_sep',
                'contacto',
                'nombramiento',
                'categoria',
                'plaza',
                'sindicato',
                'titulo',
                'cedula_profesional',
            ]);
        });
    }
};
