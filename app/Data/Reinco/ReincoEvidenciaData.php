<?php

namespace App\Data\Reinco;

/**
 * Una columna de evidencia configurable del formato REINCO.
 */
final readonly class ReincoEvidenciaData
{
    public function __construct(
        public int $id,
        public int $numero,
        public string $rubro,
        public string $tipo,
        public float $porcentaje,
        public string $fechaEntrega,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'rubro' => $this->rubro,
            'tipo' => $this->tipo,
            'porcentaje' => $this->porcentaje,
            'fecha_entrega' => $this->fechaEntrega,
        ];
    }
}
