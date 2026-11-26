<?php

namespace App\Support;

/**
 * Estructura común de los horarios mostrados en los portales docente y de alumnos.
 */
class HorariosPortal
{
    /** @var array<int, string> */
    public const DIAS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

    /** @var array<int, array{inicio: string, fin: string, tipo: string, etiqueta?: string}> */
    public const BLOQUES = [
        ['inicio' => '15:00:00', 'fin' => '15:50:00', 'tipo' => 'clase'],
        ['inicio' => '15:50:00', 'fin' => '16:40:00', 'tipo' => 'clase'],
        ['inicio' => '16:40:00', 'fin' => '17:30:00', 'tipo' => 'clase'],
        ['inicio' => '17:30:00', 'fin' => '17:50:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 20 minutos'],
        ['inicio' => '17:50:00', 'fin' => '18:40:00', 'tipo' => 'clase'],
        ['inicio' => '18:40:00', 'fin' => '19:30:00', 'tipo' => 'clase'],
        ['inicio' => '19:30:00', 'fin' => '19:40:00', 'tipo' => 'receso', 'etiqueta' => 'Receso 10 minutos'],
        ['inicio' => '19:40:00', 'fin' => '20:30:00', 'tipo' => 'clase'],
        ['inicio' => '20:30:00', 'fin' => '21:20:00', 'tipo' => 'clase'],
    ];

    public static function claveCelda(string $dia, string $inicio, string $fin): string
    {
        return $dia.'|'.$inicio.'|'.$fin;
    }
}
