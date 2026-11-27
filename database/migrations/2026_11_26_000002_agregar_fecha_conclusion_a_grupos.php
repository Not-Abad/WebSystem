<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fecha en que el grupo deja de estar disponible. Al llegar, el comando
     * grupos:vencer cierra el semestre y conserva el avance de los alumnos.
     */
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->date('fecha_conclusion')->nullable()->after('cupo_maximo');
            $table->timestamp('vencimiento_procesado_at')->nullable()->after('fecha_conclusion');
        });

        DB::table('grupos')->whereNull('fecha_conclusion')->update([
            'fecha_conclusion' => '2027-06-30',
        ]);

        Schema::table('grupos', function (Blueprint $table) {
            $table->date('fecha_conclusion')->nullable(false)->default('2027-06-30')->change();
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropColumn(['fecha_conclusion', 'vencimiento_procesado_at']);
        });
    }
};
