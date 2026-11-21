<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_grupo')->default(1)->after('id_tutor');
            $table->index('id_grupo', 'idx_alumnos_grupo');
            $table->foreign('id_grupo', 'fk_alumnos_grupo')
                ->references('id_grupo')->on('grupos')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        DB::table('alumnos')->whereNull('id_grupo')->update(['id_grupo' => 1]);

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropUnique('uk_alumnos_matricula');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('matricula');
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->string('matricula', 25)->nullable()->after('id_area_propedeutica');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->unique('matricula', 'uk_alumnos_matricula');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign('fk_alumnos_grupo');
            $table->dropIndex('idx_alumnos_grupo');
            $table->dropColumn('id_grupo');
        });
    }
};
