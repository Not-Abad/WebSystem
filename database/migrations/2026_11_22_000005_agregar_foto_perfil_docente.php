<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imagen de perfil que el docente puede actualizar desde su portal.
     */
    public function up(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->string('foto_perfil_ruta', 255)->nullable()->after('cedula_profesional');
        });
    }

    public function down(): void
    {
        Schema::table('docentes', function (Blueprint $table) {
            $table->dropColumn('foto_perfil_ruta');
        });
    }
};
