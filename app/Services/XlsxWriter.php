<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Escritor mínimo de archivos .xlsx (OOXML) sin dependencias externas.
 * Soporta varias hojas, celdas de texto/número, estilos básicos y celdas combinadas.
 */
class XlsxWriter
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_PACKAGE_REL = 'http://schemas.openxmlformats.org/package/2006/relationships';

    /** @var array<int, array<string, mixed>> */
    private array $hojas = [];

    public static function texto(?string $valor, int $estilo = 0): array
    {
        return ['tipo' => 'texto', 'valor' => $valor ?? '', 'estilo' => $estilo];
    }

    public static function numero(int|float|null $valor, int $estilo = 0): array
    {
        return ['tipo' => 'numero', 'valor' => $valor, 'estilo' => $estilo];
    }

    /**
     * @param array<int, array<int, mixed>> $filas
     * @param array<int, string> $combinaciones Ej. ['A1:H1']
     * @param array<int, int> $anchos Ancho por columna, empezando en la A.
     */
    public function agregarHoja(string $nombre, array $filas, array $combinaciones = [], array $anchos = []): self
    {
        $this->hojas[] = [
            'nombre' => mb_substr(preg_replace('/[\\\\\/*?:\[\]]/', '', $nombre) ?: 'Hoja', 0, 31),
            'filas' => $filas,
            'combinaciones' => $combinaciones,
            'anchos' => $anchos,
        ];

        return $this;
    }

    public function guardar(string $rutaAbsoluta): int
    {
        $directorio = dirname($rutaAbsoluta);
        if (! is_dir($directorio) && ! mkdir($directorio, 0755, true) && ! is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear el directorio del archivo XLSX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaAbsoluta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->raizRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->hojas as $indice => $hoja) {
            $zip->addFromString(
                'xl/worksheets/sheet'.($indice + 1).'.xml',
                $this->hojaXml($hoja)
            );
        }

        $zip->close();

        return (int) filesize($rutaAbsoluta);
    }

    private function contentTypes(): string
    {
        $hojas = '';
        foreach ($this->hojas as $indice => $hoja) {
            $hojas .= '<Override PartName="/xl/worksheets/sheet'.($indice + 1).'.xml" '
                .'ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .$hojas
            .'</Types>';
    }

    private function raizRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE_REL.'">'
            .'<Relationship Id="rId1" Type="'.self::NS_REL.'/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        $hojas = '';
        foreach ($this->hojas as $indice => $hoja) {
            $hojas .= '<sheet name="'.$this->esc($hoja['nombre']).'" sheetId="'.($indice + 1).'" r:id="rId'.($indice + 1).'"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="'.self::NS_MAIN.'" xmlns:r="'.self::NS_REL.'">'
            .'<sheets>'.$hojas.'</sheets>'
            .'</workbook>';
    }

    private function workbookRels(): string
    {
        $relaciones = '';
        foreach ($this->hojas as $indice => $hoja) {
            $relaciones .= '<Relationship Id="rId'.($indice + 1).'" Type="'.self::NS_REL.'/worksheet" Target="worksheets/sheet'.($indice + 1).'.xml"/>';
        }
        $relaciones .= '<Relationship Id="rId'.(count($this->hojas) + 1).'" Type="'.self::NS_REL.'/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="'.self::NS_PACKAGE_REL.'">'.$relaciones.'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::NS_MAIN.'">'
            .'<fonts count="4">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="10"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="5">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    /**
     * @param array<string, mixed> $hoja
     */
    private function hojaXml(array $hoja): string
    {
        $filasXml = '';
        foreach ($hoja['filas'] as $numeroFila => $celdas) {
            $celdasXml = '';
            foreach ($celdas as $numeroColumna => $celda) {
                if ($celda === null) {
                    continue;
                }
                $referencia = $this->columna($numeroColumna + 1).($numeroFila + 1);
                $celdasXml .= $this->celdaXml($referencia, $celda);
            }
            $filasXml .= '<row r="'.($numeroFila + 1).'">'.$celdasXml.'</row>';
        }

        $columnasXml = '';
        if ($hoja['anchos']) {
            $columnasXml = '<cols>';
            foreach ($hoja['anchos'] as $indice => $ancho) {
                $numero = $indice + 1;
                $columnasXml .= '<col min="'.$numero.'" max="'.$numero.'" width="'.$ancho.'" customWidth="1"/>';
            }
            $columnasXml .= '</cols>';
        }

        $combinacionesXml = '';
        if ($hoja['combinaciones']) {
            $combinacionesXml = '<mergeCells count="'.count($hoja['combinaciones']).'">';
            foreach ($hoja['combinaciones'] as $combinacion) {
                $combinacionesXml .= '<mergeCell ref="'.$this->esc($combinacion).'"/>';
            }
            $combinacionesXml .= '</mergeCells>';
        }

        $ultimaColumna = $this->columna(max(1, ...array_map(
            fn ($fila) => count($fila),
            $hoja['filas'] ?: [[]]
        )));
        $ultimaFila = max(1, count($hoja['filas']));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::NS_MAIN.'">'
            .'<dimension ref="A1:'.$ultimaColumna.$ultimaFila.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .$columnasXml
            .'<sheetData>'.$filasXml.'</sheetData>'
            .$combinacionesXml
            .'</worksheet>';
    }

    private function celdaXml(string $referencia, mixed $celda): string
    {
        if (! is_array($celda)) {
            $celda = is_int($celda) || is_float($celda)
                ? self::numero($celda)
                : self::texto((string) $celda);
        }

        $estilo = (int) ($celda['estilo'] ?? 0);
        $atributo = $estilo ? ' s="'.$estilo.'"' : '';

        if (($celda['tipo'] ?? 'texto') === 'numero') {
            $valor = $celda['valor'];

            return '<c r="'.$referencia.'"'.$atributo.'><v>'.($valor === null ? '' : (string) $valor).'</v></c>';
        }

        return '<c r="'.$referencia.'" t="inlineStr"'.$atributo.'><is><t xml:space="preserve">'
            .$this->esc((string) ($celda['valor'] ?? ''))
            .'</t></is></c>';
    }

    private function columna(int $numero): string
    {
        $columna = '';
        while ($numero > 0) {
            $numero--;
            $columna = chr(65 + ($numero % 26)).$columna;
            $numero = intdiv($numero, 26);
        }

        return $columna;
    }

    private function esc(string $valor): string
    {
        return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
