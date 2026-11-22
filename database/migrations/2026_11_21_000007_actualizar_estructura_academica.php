<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $esSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::create('salones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_salon');
            $table->string('clave', 20)->unique('uk_salones_clave');
            $table->string('nombre', 100);
            $table->unsignedInteger('capacidad');
            $table->string('edificio', 60)->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->boolean('es_tronco_comun')->default(false)->after('tipo_materia');
        });

        DB::table('materias')
            ->where('id_semestre', '<=', 4)
            ->update(['es_tronco_comun' => true]);

        Schema::table('grupos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_area_propedeutica')->nullable()->after('id_semestre');
        });

        if ($esSqlite) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign(['id_periodo']);
            });
        } else {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign('fk_grupos_periodo');
            });
        }

        Schema::table('grupos', function (Blueprint $table) {
            $table->dropUnique('uk_grupos_periodo_nombre');
            $table->unique(
                ['id_plan', 'id_semestre', 'id_area_propedeutica', 'nombre'],
                'uk_grupos_plan_semestre_area_nombre'
            );
            $table->dropColumn('id_periodo');
        });

        Schema::table('grupos', function (Blueprint $table) {
            $table->foreign('id_area_propedeutica', 'fk_grupos_area')
                ->references('id_area_propedeutica')->on('areas_propedeuticas')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        if ($esSqlite) {
            Schema::table('clases', function (Blueprint $table) {
                $table->dropForeign(['id_periodo']);
            });
        } else {
            Schema::table('clases', function (Blueprint $table) {
                $table->dropForeign('fk_clases_periodo');
            });
        }

        Schema::table('clases', function (Blueprint $table) {
            $table->dropUnique('uk_clases_materia_docente_grupo_periodo');
            $table->dropIndex('idx_clases_periodo');
            $table->unique(
                ['id_materia', 'id_docente', 'id_grupo'],
                'uk_clases_materia_docente_grupo'
            );
            $table->dropColumn('id_periodo');
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_salon')->nullable()->after('aula');
            $table->index('id_salon', 'idx_horarios_salon');
            $table->unique(
                ['id_salon', 'dia_semana', 'hora_inicio', 'hora_fin'],
                'uk_horarios_salon_dia_hora'
            );
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->foreign('id_salon', 'fk_horarios_salon')
                ->references('id_salon')->on('salones')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::dropIfExists('periodos_academicos');
    }

    public function down(): void
    {
        $esSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->bigIncrements('id_periodo');
            $table->string('clave', 20)->unique('uk_periodos_academicos_clave');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('is_active')->default(true);
        });

        DB::table('periodos_academicos')->insert([
            'id_periodo' => 1,
            'clave' => '2026-B',
            'fecha_inicio' => '2026-08-17',
            'fecha_fin' => '2026-12-18',
            'is_active' => true,
        ]);

        if ($esSqlite) {
            Schema::table('horarios', function (Blueprint $table) {
                $table->dropForeign(['id_salon']);
            });
        } else {
            Schema::table('horarios', function (Blueprint $table) {
                $table->dropForeign('fk_horarios_salon');
            });
        }

        Schema::table('horarios', function (Blueprint $table) {
            $table->dropUnique('uk_horarios_salon_dia_hora');
            $table->dropIndex('idx_horarios_salon');
            $table->dropColumn('id_salon');
        });

        Schema::table('clases', function (Blueprint $table) {
            $table->dropUnique('uk_clases_materia_docente_grupo');
            $table->unsignedBigInteger('id_periodo')->nullable()->after('id_grupo');
            $table->index('id_periodo', 'idx_clases_periodo');
            $table->unique(
                ['id_materia', 'id_docente', 'id_grupo', 'id_periodo'],
                'uk_clases_materia_docente_grupo_periodo'
            );
        });

        DB::table('clases')->update(['id_periodo' => 1]);

        if ($esSqlite) {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign(['id_area_propedeutica']);
            });
        } else {
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign('fk_grupos_area');
            });
        }

        Schema::table('grupos', function (Blueprint $table) {
            $table->dropUnique('uk_grupos_plan_semestre_area_nombre');
            $table->unsignedBigInteger('id_periodo')->nullable()->after('id_semestre');
            $table->unique(['id_periodo', 'nombre'], 'uk_grupos_periodo_nombre');
            $table->dropColumn('id_area_propedeutica');
        });

        DB::table('grupos')->update(['id_periodo' => 1]);

        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('es_tronco_comun');
        });

        Schema::dropIfExists('salones');
    }
};
