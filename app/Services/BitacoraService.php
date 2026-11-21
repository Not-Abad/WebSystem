<?php

namespace App\Services;

use App\Models\BitacoraAuditoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class BitacoraService
{
    /**
     * Registra una operación crítica en la bitácora inalterable.
     */
    public static function registrar(
        string $tabla,
        string $accion,
        string $descripcion,
        ?int $idRegistro = null,
        ?User $usuario = null
    ): BitacoraAuditoria {
        $usuario ??= Auth::user();

        return BitacoraAuditoria::create([
            'id_usuario' => $usuario?->id_usuario ?? 1,
            'tabla_afectada' => $tabla,
            'id_registro_afectado' => $idRegistro,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ip_origen' => Request::ip(),
        ]);
    }
}
