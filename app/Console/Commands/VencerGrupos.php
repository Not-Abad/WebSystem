<?php

namespace App\Console\Commands;

use App\Models\Grupo;
use App\Services\BitacoraService;
use App\Services\KardexService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VencerGrupos extends Command
{
    protected $signature = 'grupos:vencer {--fecha= : Evalúa el vencimiento con esta fecha (Y-m-d)}';

    protected $description = 'Cierra los grupos cuya fecha de conclusión haya llegado y conserva el avance de los alumnos en el kardex';

    public function handle(KardexService $kardex): int
    {
        $fecha = $this->option('fecha')
            ? Carbon::parse((string) $this->option('fecha'))->startOfDay()
            : now()->startOfDay();

        $grupos = Grupo::whereNotNull('fecha_conclusion')
            ->whereDate('fecha_conclusion', '<=', $fecha)
            ->whereNull('vencimiento_procesado_at')
            ->with(['semestre', 'plan'])
            ->orderBy('fecha_conclusion')
            ->get();

        if ($grupos->isEmpty()) {
            $this->info('No hay grupos pendientes de vencimiento.');

            return self::SUCCESS;
        }

        $alumnos = 0;
        $materias = 0;
        $graduados = 0;

        foreach ($grupos as $grupo) {
            $resultado = DB::transaction(fn () => $kardex->cerrarGrupo($grupo));

            $alumnos += $resultado['alumnos'];
            $materias += $resultado['materias'];
            $graduados += $resultado['graduados'];

            BitacoraService::registrar(
                'grupos',
                'EDICION',
                sprintf(
                    'Vencimiento de %s: %d alumno(s), %d materia(s) cursada(s), %d graduado(s).',
                    $grupo->etiqueta(),
                    $resultado['alumnos'],
                    $resultado['materias'],
                    $resultado['graduados']
                ),
                $grupo->id_grupo
            );
        }

        $this->info(sprintf(
            'Grupos vencidos: %d · alumnos: %d · materias cursadas: %d · graduados: %d.',
            $grupos->count(),
            $alumnos,
            $materias,
            $graduados
        ));

        return self::SUCCESS;
    }
}
