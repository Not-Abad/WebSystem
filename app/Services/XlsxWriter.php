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

    private ?string $plantilla = null;

    /** @var array<string, string> */
    private array $configuracionPlantilla = [];

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
     * @param array<string, mixed> $opciones Impresión, alturas y pie de página.
     */
    public function agregarHoja(
        string $nombre,
        array $filas,
        array $combinaciones = [],
        array $anchos = [],
        array $opciones = []
    ): self {
        $this->hojas[] = [
            'nombre' => mb_substr(preg_replace('/[\\\\\/*?:\[\]]/', '', $nombre) ?: 'Hoja', 0, 31),
            'filas' => $filas,
            'combinaciones' => $combinaciones,
            'anchos' => $anchos,
            'opciones' => $opciones,
        ];

        return $this;
    }

    /**
     * Si la plantilla existe, se toman de ella printOptions, pageMargins y
     * pageSetup. Si no existe, se construye la hoja desde cero.
     */
    public function usarPlantilla(?string $ruta): self
    {
        $this->plantilla = $ruta && is_file($ruta) ? $ruta : null;

        return $this;
    }

    public function guardar(string $rutaAbsoluta): int
    {
        $this->configuracionPlantilla = $this->leerConfiguracionPlantilla();

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
        $borde = '<left style="thin"><color rgb="FFB0B7C3"/></left>'
            .'<right style="thin"><color rgb="FFB0B7C3"/></right>'
            .'<top style="thin"><color rgb="FFB0B7C3"/></top>'
            .'<bottom style="thin"><color rgb="FFB0B7C3"/></bottom><diagonal/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::NS_MAIN.'">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="0.00"/></numFmts>'
            .'<fonts count="5">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="14"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="10"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFD9E1F2"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1F4E79"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border>'.$borde.'</border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="12">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
            .'<xf numFmtId="1" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf>'
            .'<xf numFmtId="164" fontId="1" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
            .'<xf numFmtId="1" fontId="1" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    /**
     * @param array<string, mixed> $hoja
     */
    private function hojaXml(array $hoja): string
    {
        $opciones = $hoja['opciones'];
        $impresion = (array) ($opciones['impresion'] ?? []);
        $alturas = (array) ($opciones['alturas'] ?? []);

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
            $atributoAltura = isset($alturas[$numeroFila])
                ? ' ht="'.(float) $alturas[$numeroFila].'" customHeight="1"'
                : '';
            $filasXml .= '<row r="'.($numeroFila + 1).'"'.$atributoAltura.'>'.$celdasXml.'</row>';
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

        $ajustarAncho = (int) ($impresion['ajustar_ancho'] ?? 1);
        $sheetPr = $ajustarAncho > 0
            ? '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            : '';

        $printOptions = $this->configuracionPlantilla['printOptions'] ?? null;
        if (! $printOptions && ! empty($impresion['centrar_horizontal'])) {
            $printOptions = '<printOptions horizontalCentered="1"/>';
        }

        $pageMargins = $this->configuracionPlantilla['pageMargins'] ?? null;
        if (! $pageMargins) {
            $margenes = (array) ($impresion['margenes'] ?? []);
            $pageMargins = '<pageMargins'
                .$this->atributoMargen('left', $margenes['left'] ?? 0.25)
                .$this->atributoMargen('right', $margenes['right'] ?? 0.25)
                .$this->atributoMargen('top', $margenes['top'] ?? 0.45)
                .$this->atributoMargen('bottom', $margenes['bottom'] ?? 0.45)
                .$this->atributoMargen('header', $margenes['header'] ?? 0.2)
                .$this->atributoMargen('footer', $margenes['footer'] ?? 0.2)
                .'/>';
        }

        $pageSetup = $this->configuracionPlantilla['pageSetup'] ?? null;
        if (! $pageSetup) {
            $pageSetup = '<pageSetup paperSize="'.(int) ($impresion['papel'] ?? 9).'"'
                .' orientation="'.$this->esc((string) ($impresion['orientacion'] ?? 'landscape')).'"'
                .' fitToWidth="'.$ajustarAncho.'"'
                .' fitToHeight="'.(int) ($impresion['ajustar_alto'] ?? 0).'"/>';
        }

        $pie = (string) ($opciones['pie'] ?? '');
        $headerFooter = $pie !== ''
            ? '<headerFooter><oddFooter>'.$this->esc($pie).'</oddFooter></headerFooter>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::NS_MAIN.'">'
            .$sheetPr
            .'<dimension ref="A1:'.$ultimaColumna.$ultimaFila.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .$columnasXml
            .'<sheetData>'.$filasXml.'</sheetData>'
            .$combinacionesXml
            .$printOptions
            .$pageMargins
            .$pageSetup
            .$headerFooter
            .'</worksheet>';
    }

    private function atributoMargen(string $nombre, mixed $valor): string
    {
        return ' '.$nombre.'="'.(float) $valor.'"';
    }

    /** @return array<string, string> */
    private function leerConfiguracionPlantilla(): array
    {
        if (! $this->plantilla) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($this->plantilla) !== true) {
            return [];
        }
        $xml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($xml === '') {
            return [];
        }

        $configuracion = [];
        foreach (['printOptions', 'pageMargins', 'pageSetup'] as $etiqueta) {
            if (preg_match('/<'.$etiqueta.'\b[^>]*\/?>/', $xml, $coincidencia) === 1) {
                $configuracion[$etiqueta] = $coincidencia[0];
            }
        }

        return $configuracion;
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
