<?php

namespace Tests\Feature;

use App\Services\XlsxWriter;
use Tests\TestCase;
use ZipArchive;

class XlsxWriterTest extends TestCase
{
    public function test_escribe_numeros_como_valores_numericos_y_configura_impresion(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'xlsx_');

        try {
            $writer = new XlsxWriter();
            $writer->agregarHoja(
                'REINCO',
                [
                    [XlsxWriter::texto('Calificación', 8)],
                    [XlsxWriter::numero(8.5, 5), XlsxWriter::texto('A')],
                ],
                [],
                [18, 10],
                [
                    'impresion' => [
                        'orientacion' => 'landscape',
                        'ajustar_ancho' => 1,
                        'margenes' => ['left' => 0.2],
                    ],
                    'pie' => 'Hoja &P de &N',
                ]
            );
            $writer->guardar($ruta);

            $zip = new ZipArchive();
            $this->assertTrue($zip->open($ruta) === true);
            $hoja = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $estilos = (string) $zip->getFromName('xl/styles.xml');
            $zip->close();

            $this->assertMatchesRegularExpression('/<c r="A2" s="5"><v>8\.5<\/v><\/c>/', $hoja);
            $this->assertDoesNotMatchRegularExpression('/<c r="A2"[^>]*t="inlineStr"/', $hoja);
            $this->assertStringContainsString('formatCode="0.00"', $estilos);
            $this->assertStringContainsString('orientation="landscape"', $hoja);
            $this->assertStringContainsString('fitToWidth="1"', $hoja);
            $this->assertStringContainsString('left="0.2"', $hoja);
            $this->assertStringContainsString('Hoja &amp;P de &amp;N', $hoja);
        } finally {
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
    }

    public function test_toma_la_configuracion_de_pagina_de_la_plantilla_base_si_existe(): void
    {
        $plantilla = tempnam(sys_get_temp_dir(), 'plantilla_');
        $salida = tempnam(sys_get_temp_dir(), 'xlsx_');

        try {
            $zip = new ZipArchive();
            $zip->open($plantilla, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?>'
                .'<worksheet><pageSetup orientation="portrait" paperSize="9"/>'
                .'<pageMargins left="0.5" right="0.5"/></worksheet>');
            $zip->close();

            $writer = new XlsxWriter();
            $writer->usarPlantilla($plantilla);
            $writer->agregarHoja('REINCO', [[XlsxWriter::texto('Plantel')]]);
            $writer->guardar($salida);

            $zip = new ZipArchive();
            $zip->open($salida);
            $hoja = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            $this->assertStringContainsString('orientation="portrait"', $hoja);
            $this->assertStringContainsString('left="0.5"', $hoja);
        } finally {
            foreach ([$plantilla, $salida] as $archivo) {
                if (is_file($archivo)) {
                    @unlink($archivo);
                }
            }
        }
    }
}
