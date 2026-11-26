<?php

namespace App\Support;

/**
 * Validación y normalización de fechas capturadas en formularios tipo date.
 *
 * La regla `date` de Laravel acepta valores como `20026-11-28` porque PHP los
 * interpreta como el año 2006, pero MySQL los rechaza con SQLSTATE 22007 y la
 * petición termina en error 500. Todas las fechas que se persisten deben
 * validarse con estas reglas antes de llegar a la base de datos.
 */
final class FechaSegura
{
    public const FORMATO = 'Y-m-d';
    public const MINIMA = '2000-01-01';
    public const MAXIMA = '2100-12-31';

    /** @return array<int, string> */
    public static function reglas(bool $requerida = true): array
    {
        return array_values(array_filter([
            $requerida ? 'required' : 'nullable',
            'date_format:'.self::FORMATO,
            'after_or_equal:'.self::MINIMA,
            'before_or_equal:'.self::MAXIMA,
        ]));
    }

    /**
     * @return array<string, string>
     */
    public static function mensajes(string $campo): array
    {
        return [
            $campo.'.required' => 'Ingresa la fecha.',
            $campo.'.date_format' => 'Ingresa la fecha en formato AAAA-MM-DD.',
            $campo.'.after_or_equal' => 'La fecha debe ser del año 2000 en adelante.',
            $campo.'.before_or_equal' => 'La fecha no puede ser posterior al 2100.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function mensajesHora(string $campo): array
    {
        return array_merge(self::mensajes($campo), [
            $campo.'.date_format' => 'Ingresa una fecha y hora válidas.',
        ]);
    }

    /** Reglas para fechas con hora ya normalizadas a AAAA-MM-DD HH:MM:SS. */
    public static function reglasHora(bool $requerida = true): array
    {
        return array_values(array_filter([
            $requerida ? 'required' : 'nullable',
            'date_format:Y-m-d H:i:s',
            'after_or_equal:'.self::MINIMA,
            'before_or_equal:'.self::MAXIMA,
        ]));
    }

    public static function normalizar(mixed $valor): ?string
    {
        if (! is_string($valor)) {
            return null;
        }

        $valor = trim($valor);

        return $valor === '' ? null : $valor;
    }

    /**
     * Convierte un datetime-local (AAAA-MM-DDTHH:MM) o una fecha con hora a
     * formato MySQL DATETIME. Los valores mal formados se devuelven tal cual
     * para que la validación los rechace con un mensaje claro.
     */
    public static function normalizarHora(mixed $valor): ?string
    {
        $valor = self::normalizar($valor);

        if ($valor === null) {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})$/', $valor, $partes)) {
            return $partes[1].' '.$partes[2].':00';
        }

        return $valor;
    }
}
