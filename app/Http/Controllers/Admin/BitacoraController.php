<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraAuditoria;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->only(['tabla', 'accion', 'id_usuario', 'desde', 'hasta', 'q']);

        $registros = $this->consultar($request)
            ->with('usuario')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bitacora.index', [
            'titulo' => 'Bitácora de auditoría',
            'subtitulo' => 'Consulta, filtrado y exportación de los eventos críticos del sistema.',
            'icono' => 'fa-file-circle-check',
            'etiqueta' => 'SuperUsuario',
            'registros' => $registros,
            'tablas' => BitacoraAuditoria::distinct()->orderBy('tabla_afectada')->pluck('tabla_afectada'),
            'acciones' => BitacoraAuditoria::distinct()->orderBy('accion')->pluck('accion'),
            'usuarios' => User::orderBy('username')->pluck('username', 'id_usuario'),
            'filtros' => $filtros,
        ]);
    }

    public function show(BitacoraAuditoria $bitacora)
    {
        $bitacora->load('usuario');

        return view('admin.bitacora.show', [
            'titulo' => 'Detalle del evento',
            'subtitulo' => 'Registro inalterable de la bitácora de auditoría.',
            'icono' => 'fa-file-circle-check',
            'etiqueta' => 'SuperUsuario',
            'registro' => $bitacora,
        ]);
    }

    public function export(Request $request)
    {
        $registros = $this->consultar($request)
            ->with('usuario')
            ->orderByDesc('created_at')
            ->get();

        BitacoraService::registrar(
            'bitacora_auditoria',
            'EXPORTACION',
            sprintf('Exportación CSV de bitácora (%d registros).', $registros->count())
        );

        $nombre = 'bitacora_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($registros) {
            $handle = fopen('php://output', 'w+');

            // BOM para que Excel interprete correctamente los acentos.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'ID',
                'Fecha',
                'Usuario',
                'Tabla afectada',
                'ID registro',
                'Acción',
                'Descripción',
                'IP de origen',
            ], ';');

            foreach ($registros as $registro) {
                fputcsv($handle, [
                    $registro->id_bitacora,
                    $registro->created_at?->format('d/m/Y H:i:s'),
                    $registro->usuario?->username ?? 'Sistema',
                    $registro->tabla_afectada,
                    $registro->id_registro_afectado,
                    $registro->accion,
                    $registro->descripcion,
                    $registro->ip_origen,
                ], ';');
            }

            fclose($handle);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function consultar(Request $request)
    {
        $query = BitacoraAuditoria::query();

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->input('tabla'));
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->input('id_usuario'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->input('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->input('hasta'));
        }

        if ($request->filled('q')) {
            $termino = '%'.addcslashes(trim((string) $request->input('q')), '%_\\').'%';
            $query->where(function ($query) use ($termino) {
                $query->where('descripcion', 'like', $termino)
                    ->orWhere('tabla_afectada', 'like', $termino)
                    ->orWhere('accion', 'like', $termino)
                    ->orWhere('ip_origen', 'like', $termino)
                    ->orWhereHas('usuario', fn ($q) => $q->where('username', 'like', $termino));
            });
        }

        return $query;
    }
}
