<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\Clase;
use App\Models\Evaluacion;
use App\Support\CalificacionService;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Llena la plantilla oficial de boleta (formato DGB) con las calificaciones
 * ya capturadas en el portal docente y exporta un XLSX listo para imprimir.
 *
 * La plantilla conserva las fórmulas de la hoja "BOLETA parcial"; al abrir el
 * archivo, Excel recalcula las 25 boletas a partir de las hojas DATOS, DATOS2
 * y CURP que se escriben aquí.
 */
final class BoletaService
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const MAX_ALUMNOS = 25;

    /** @var array<int, string> */
    private const Romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];

    /**
     * @param  array<string, mixed>  $opciones
     */
    public function generar(Clase $clase, Evaluacion $evaluacion, string $destino, array $opciones = []): void
    {
        $columna = strtoupper(trim((string) ($opciones['columna'] ?? '')));
        $columnas = (array) config('boleta.columnas');

        if (! array_key_exists($columna, $columnas)) {
            throw new RuntimeException('Selecciona una columna válida de la boleta oficial.');
        }

        $plantilla = (string) config('boleta.plantilla');
        if (! is_file($plantilla)) {
            throw new RuntimeException('No se encontró la plantilla oficial de la boleta.');
        }

        $alumnos = $clase->alumnosActivos()->take(self::MAX_ALUMNOS)->values();
        $criterios = $evaluacion->criterios()->orderBy('id_criterio')->get()->load('calificaciones');
        $parcial = (int) $evaluacion->parcial;
        $semestre = (int) ($clase->grupo->semestre?->numero_semestre ?? 0);

        $periodo = trim((string) ($opciones['periodo_escolar'] ?? ''));
        if ($periodo === '') {
            $periodo = now()->format('y').'-'.now()->addYear()->format('y');
        }

        $lectura = new ZipArchive();
        if ($lectura->open($plantilla) !== true) {
            throw new RuntimeException('No se pudo abrir la plantilla oficial de la boleta.');
        }

        try {
            $datos = $this->documento((string) $lectura->getFromName('xl/worksheets/sheet1.xml'));
            $datos2 = $this->documento((string) $lectura->getFromName('xl/worksheets/sheet2.xml'));
            $curp = $this->documento((string) $lectura->getFromName('xl/worksheets/sheet3.xml'));

            foreach ([$datos, $datos2] as $hoja) {
                $this->escribirEncabezado($hoja, $clase, $periodo, $parcial, $semestre, $opciones);
                $this->escribirAlumnos($hoja, $alumnos, $criterios, $columna);
            }

            foreach ($alumnos as $indice => $alumno) {
                $fila = $indice + 2;
                $this->escribir($curp, 'B'.$fila, (string) $alumno->apellido_paterno);
                $this->escribir($curp, 'C'.$fila, (string) $alumno->apellido_materno);
                $this->escribir($curp, 'D'.$fila, (string) $alumno->nombre);
                $this->escribir($curp, 'E'.$fila, strtoupper((string) $alumno->curp));
            }

            $partes = [
                'xl/worksheets/sheet1.xml' => $datos->saveXML(),
                'xl/worksheets/sheet2.xml' => $datos2->saveXML(),
                'xl/worksheets/sheet3.xml' => $curp->saveXML(),
            ];

            $escritura = new ZipArchive();
            if ($escritura->open($destino, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No se pudo generar el archivo de la boleta.');
            }

            for ($i = 0; $i < $lectura->numFiles; $i++) {
                $nombre = $lectura->getNameIndex($i);
                $escritura->addFromString($nombre, $partes[$nombre] ?? (string) $lectura->getFromIndex($i));
            }
            $escritura->close();
        } finally {
            $lectura->close();
        }
    }

    /** Nombre sugerido para el archivo exportado. */
    public function nombreArchivo(Clase $clase, Evaluacion $evaluacion): string
    {
        $materia = Str::of($clase->materia->clave ?: $clase->materia->nombre)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '_')
            ->upper()
            ->toString();
        $grupo = Str::of($clase->grupo->etiqueta())
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9_-]+/', '_')
            ->upper()
            ->toString();

        return sprintf('BOLETA_%s_%s_P%d_%s.xlsx', $materia, $grupo, $evaluacion->parcial, now()->format('Ymd_His'));
    }

    /** Columna oficial sugerida a partir del nombre de la materia. */
    public static function columnaSugerida(string $nombreMateria): string
    {
        $normalizado = Str::ascii(mb_strtolower($nombreMateria));

        foreach ((array) config('boleta.sugerencias', []) as $palabra => $columna) {
            if (str_contains($normalizado, Str::ascii(mb_strtolower((string) $palabra)))) {
                return (string) $columna;
            }
        }

        return 'AO';
    }

    /** @param  \Illuminate\Support\Collection<int, Alumno>  $alumnos */
    private function escribirEncabezado(
        DOMDocument $hoja,
        Clase $clase,
        string $periodo,
        int $parcial,
        int $semestre,
        array $opciones
    ): void {
        $this->escribir($hoja, 'Q2', (string) config('boleta.plantel'));
        $this->escribir($hoja, 'AI2', (string) config('boleta.cct'));
        $this->escribir($hoja, 'AR2', (string) config('boleta.localidad'));
        $this->escribir($hoja, 'AZ2', (int) config('boleta.zona'));
        $this->escribir($hoja, 'I5', $periodo);
        $this->escribir($hoja, 'AW5', $clase->grupo->etiqueta());
        $this->escribir($hoja, 'V5', self::Romanos[$semestre] ?? 'I');
        $this->escribir($hoja, 'I43', trim((string) ($opciones['director'] ?? config('boleta.director') ?? '')));
        $this->escribir($hoja, 'U43', trim((string) ($opciones['subdirector'] ?? config('boleta.subdirector') ?? '')));
        $this->escribir($hoja, 'AG44', (int) now()->year);

        foreach (['AQ6', 'AR6', 'AS6'] as $celda) {
            $this->escribir($hoja, $celda, '');
        }

        $marcador = match ($parcial) {
            1 => 'AQ6',
            2 => 'AR6',
            default => 'AS6',
        };
        $this->escribir($hoja, $marcador, 'X');

        foreach (['V6', 'W6', 'X6', 'Y6', 'Z6', 'AA6'] as $celda) {
            $this->escribir($hoja, $celda, '');
        }

        $celdaSemestre = ['V6', 'W6', 'X6', 'Y6', 'Z6', 'AA6'][max(0, min(5, $semestre - 1))];
        $this->escribir($hoja, $celdaSemestre, 'X');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Alumno>  $alumnos
     * @param  \Illuminate\Support\Collection<int, \App\Models\CriterioEvaluacion>  $criterios
     */
    private function escribirAlumnos(DOMDocument $hoja, $alumnos, $criterios, string $columna): void
    {
        foreach ($alumnos as $indice => $alumno) {
            $fila = 11 + $indice;

            $this->escribir($hoja, 'B'.$fila, strtoupper(substr((string) $alumno->curp, -4)));
            $this->escribir($hoja, 'G'.$fila, (string) $alumno->apellido_paterno);
            $this->escribir($hoja, 'M'.$fila, (string) $alumno->apellido_materno);
            $this->escribir($hoja, 'R'.$fila, (string) $alumno->nombre);

            $resultado = CalificacionService::calificacionParcial($criterios, $alumno);
            $nota = $resultado['tieneCalificacion'] ? $resultado['calificacion'] : '-';
            $this->escribir($hoja, $columna.$fila, $nota);

            foreach (['Y', 'AA', 'AC', 'AE', 'AG', 'AI', 'AK', 'AM', 'AO'] as $otra) {
                if ($otra !== $columna) {
                    $this->escribir($hoja, $otra.$fila, '-');
                }
            }
        }
    }

    private function documento(string $xml): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        return $dom;
    }

    private function escribir(DOMDocument $dom, string $referencia, int|float|string $valor): void
    {
        $celda = $this->celda($dom, $referencia);

        foreach (iterator_to_array($celda->childNodes) as $hijo) {
            $celda->removeChild($hijo);
        }

        if (is_string($valor)) {
            $celda->setAttribute('t', 'inlineStr');
            $cadena = $dom->createElementNS(self::NS, 'is');
            $texto = $dom->createElementNS(self::NS, 't');
            $texto->appendChild($dom->createTextNode($valor));
            $cadena->appendChild($texto);
            $celda->appendChild($cadena);

            return;
        }

        $celda->removeAttribute('t');
        $numero = $dom->createElementNS(self::NS, 'v');
        $numero->appendChild($dom->createTextNode((string) $valor));
        $celda->appendChild($numero);
    }

    private function celda(DOMDocument $dom, string $referencia): DOMElement
    {
        $x = new DOMXPath($dom);
        $existente = $x->query('//*[local-name()="c" and @r="'.$referencia.'"]')->item(0);
        if ($existente instanceof DOMElement) {
            return $existente;
        }

        if (! preg_match('/^([A-Z]+)(\d+)$/', $referencia, $coincidencia)) {
            throw new RuntimeException('Referencia de celda no válida: '.$referencia);
        }

        $columna = $coincidencia[1];
        $numeroFila = (int) $coincidencia[2];

        $sheetData = $x->query('//*[local-name()="sheetData"]')->item(0);
        if (! $sheetData instanceof DOMElement) {
            throw new RuntimeException('La plantilla de la boleta no tiene datos de hoja.');
        }

        $fila = $x->query('//*[local-name()="row" and @r="'.$numeroFila.'"]')->item(0);
        if (! $fila instanceof DOMElement) {
            $fila = $dom->createElementNS(self::NS, 'row');
            $fila->setAttribute('r', (string) $numeroFila);

            $insertada = false;
            foreach ($x->query('//*[local-name()="row"]') as $otra) {
                if ((int) $otra->getAttribute('r') > $numeroFila) {
                    $sheetData->insertBefore($fila, $otra);
                    $insertada = true;
                    break;
                }
            }
            if (! $insertada) {
                $sheetData->appendChild($fila);
            }
        }

        $celda = $dom->createElementNS(self::NS, 'c');
        $celda->setAttribute('r', $referencia);

        $insertada = false;
        foreach ($x->query('./*[local-name()="c"]', $fila) as $otra) {
            $otraColumna = preg_replace('/\d/', '', $otra->getAttribute('r')) ?: '';
            if ($this->indiceColumna($otraColumna) > $this->indiceColumna($columna)) {
                $fila->insertBefore($celda, $otra);
                $insertada = true;
                break;
            }
        }
        if (! $insertada) {
            $fila->appendChild($celda);
        }

        return $celda;
    }

    private function indiceColumna(string $columna): int
    {
        $indice = 0;
        foreach (str_split($columna) as $caracter) {
            $indice = $indice * 26 + (ord($caracter) - ord('A') + 1);
        }

        return $indice;
    }
}
