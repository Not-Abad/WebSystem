<?php

namespace App\Services;

use App\Models\Clase;

/**
 * Genera un PDF sencillo del programa de la materia cuando no hay un archivo
 * institucional registrado en la ficha de la materia.
 *
 * Se construye sin dependencias externas para conservar el stack del proyecto.
 */
class PlanEstudioPdfService
{
    private const ANCHO_LINEA = 94;

    public function generar(Clase $clase): string
    {
        $materia = $clase->materia;
        $grupo = $clase->grupo;

        $lineas = [
            ['Escuela de Bachilleres Lic. Antonio María de Rivera', 15],
            ['Programa de estudio de la materia', 11],
            ['', 6],
            ['Materia: '.$materia->clave.' · '.$materia->nombre, 10],
            ['Semestre: '.($grupo?->semestre?->nombreCompleto() ?? '—'), 10],
            ['Plan de estudio: '.($materia->plan?->etiqueta() ?? 'Plan vigente'), 10],
            ['Tipo de materia: '.$materia->tipo_materia, 10],
            ['Horas por semana: '.$materia->horas_semana, 10],
            ['Docente: '.$clase->docenteNombre(), 10],
            ['Correo institucional: '.($clase->docente?->correo_institucional ?? '—'), 10],
            ['Grupo: '.($grupo?->etiqueta() ?? '—'), 10],
            ['', 6],
            ['Descripción', 11],
        ];

        foreach ($this->envolver($materia->descripcion ?: 'La materia no tiene una descripción registrada.') as $texto) {
            $lineas[] = [$texto, 9];
        }

        $lineas[] = ['', 6];
        $lineas[] = ['Objetivo', 11];
        foreach ($this->envolver($materia->objetivo ?: 'El objetivo de la materia no ha sido registrado.') as $texto) {
            $lineas[] = [$texto, 9];
        }

        $lineas[] = ['', 6];
        $lineas[] = ['Documento generado por el portal institucional para consulta del alumno.', 8];

        return $this->pdf($lineas);
    }

    /**
     * @return array<int, string>
     */
    private function envolver(string $texto): array
    {
        $texto = preg_replace('/\s+/u', ' ', trim($texto)) ?: '';
        if ($texto === '') {
            return ['—'];
        }

        $palabras = preg_split('/\s+/u', $texto) ?: [];
        $lineas = [];
        $actual = '';

        foreach ($palabras as $palabra) {
            $candidata = $actual === '' ? $palabra : $actual.' '.$palabra;
            if (mb_strlen($candidata) > self::ANCHO_LINEA && $actual !== '') {
                $lineas[] = $actual;
                $actual = $palabra;
            } else {
                $actual = $candidata;
            }
        }

        if ($actual !== '') {
            $lineas[] = $actual;
        }

        return $lineas;
    }

    /**
     * @param  array<int, array{0: string, 1: int}>  $lineas
     */
    private function pdf(array $lineas): string
    {
        $contenido = '';
        $y = 742;

        foreach ($lineas as [$texto, $tamano]) {
            if ($y < 54) {
                break;
            }
            if ($texto !== '') {
                $contenido .= 'BT /F1 '.$tamano.' Tf 54 '.$y.' Td ('
                    .$this->escapar($texto).") Tj ET\n";
            }
            $y -= $tamano + 6;
        }

        $objetos = [
            1 => '/Type /Catalog /Pages 2 0 R',
            2 => '/Type /Pages /Kids [3 0 R] /Count 1',
            3 => '/Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] '
                .'/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R',
            4 => '/Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding',
            5 => "<< /Length ".strlen($contenido)." >>\nstream\n".$contenido."endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objetos as $numero => $cuerpo) {
            $offsets[$numero] = strlen($pdf);
            $pdf .= $numero." 0 obj\n".$cuerpo."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n".'0 6'."\n".'0000000000 65535 f '."\n";
        foreach ($objetos as $numero => $cuerpo) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$numero])."\n";
        }
        $pdf .= 'trailer'."\n".'<< /Size 6 /Root 1 0 R >>'."\n";
        $pdf .= 'startxref'."\n".$xref."\n".'%%EOF';

        return $pdf;
    }

    private function escapar(string $texto): string
    {
        $texto = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
    }
}
