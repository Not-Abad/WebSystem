<?php

namespace App\Data\Reinco;

/**
 * Configuración de presentación e impresión. Viaja separada de los datos de
 * evaluación para que el mismo concentrado pueda imprimirse de distintas
 * formas sin alterar las calificaciones.
 */
final readonly class ReincoPresentacionData
{
    /** @param array<string, float> $margenes */
    public function __construct(
        public int $hojas,
        public string $orientacion,
        public int $papel,
        public int $ajustarAncho,
        public int $ajustarAlto,
        public array $margenes,
        public bool $centrarHorizontal,
        public string $plantillaBase,
        public int $decimales,
        public array $firmas,
    ) {
    }

    /**
     * @param  array<string, mixed>  $opciones
     */
    public static function desdeConfig(array $opciones = [], int $hojas = 5): self
    {
        $config = config('reinco');
        $decimales = (string) ($opciones['formato_calificacion'] ?? $config['formato_calificacion']) === 'entero' ? 0 : 2;

        return new self(
            hojas: $hojas,
            orientacion: (string) ($config['orientacion'] ?? 'landscape'),
            papel: (int) ($config['papel'] ?? 9),
            ajustarAncho: (int) ($config['ajustar_ancho'] ?? 1),
            ajustarAlto: (int) ($config['ajustar_alto'] ?? 0),
            margenes: (array) ($config['margenes'] ?? []),
            centrarHorizontal: (bool) ($config['centrar_horizontal'] ?? true),
            plantillaBase: (string) ($config['plantilla_base'] ?? ''),
            decimales: $decimales,
            firmas: [
                'Docente',
                'Control escolar / Subdirección',
                'Dirección del plantel',
            ],
        );
    }

    /**
     * Estilo de celda que aplica el formato numérico configurado.
     */
    public function estiloNumero(bool $negrita = false): int
    {
        return match (true) {
            $this->decimales === 0 && $negrita => 11,
            $this->decimales === 0 => 6,
            $negrita => 10,
            default => 5,
        };
    }
}
