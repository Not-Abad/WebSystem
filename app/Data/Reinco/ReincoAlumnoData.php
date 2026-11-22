<?php

namespace App\Data\Reinco;

/**
 * Renglo de alumno del concentrado REINCO. La CURP es la clave de control
 * principal; el número progresivo sólo ordena la impresión.
 */
final readonly class ReincoAlumnoData
{
    /**
     * @param  array<int|string, array{presente: int, falta: int, retardo: int, justificada: int, porcentaje: float}>  $asistenciasPorParcial
     * @param  array<int, array{puntaje: float, porcentaje: float}>  $evidencias
     */
    public function __construct(
        public int $numero,
        public string $curp,
        public string $nombreCompleto,
        public array $asistenciasPorParcial,
        public array $evidencias,
        public float $calificacionFinal,
        public bool $examinado,
        public string $observaciones,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'numero' => $this->numero,
            'curp' => $this->curp,
            'nombre' => $this->nombreCompleto,
            'asistencias' => $this->asistenciasPorParcial,
            'evidencias' => $this->evidencias,
            'calificacion_final' => $this->calificacionFinal,
            'examinado' => $this->examinado,
            'observaciones' => $this->observaciones,
        ];
    }
}
