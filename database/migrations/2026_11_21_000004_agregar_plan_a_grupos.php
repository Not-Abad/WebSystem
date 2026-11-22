<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_plan')->nullable()->after('id_semestre');
            $table->index('id_plan', 'idx_grupos_plan');
        });

        $idPlan = DB::table('planes_estudio')->min('id_plan');

        if ($idPlan !== null) {
            DB::table('grupos')->whereNull('id_plan')->update(['id_plan' => $idPlan]);
        }

        Schema::table('grupos', function (Blueprint $table) {
            $table->foreign('id_plan', 'fk_grupos_plan')
                ->references('id_plan')->on('planes_estudio')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropForeign('fk_grupos_plan');
            $table->dropIndex('idx_grupos_plan');
            $table->dropColumn('id_plan');
        });
    }
};
