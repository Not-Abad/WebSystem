<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchivosSeeder extends Seeder
{
    public function run(): void
    {
        $carpetas = [
            'alumnos',
            'tutores',
            'boletas',
            'certificados',
            'documentos/convocatorias',
            'documentos/formatos',
            'documentos/manuales',
            'documentos/normatividad',
            'documentos/reportes',
            'imagenes/noticias',
            'imagenes/galeria',
        ];

        foreach ($carpetas as $carpeta) {
            Storage::disk('local')->makeDirectory($carpeta);
        }

        // Archivos de demostración para que las descargas públicas funcionen.
        $archivos = [
            'documentos/convocatorias/convocatoria-2027.pdf' => 'Convocatoria de inscripción 2027',
            'documentos/formatos/baja-temporal.docx' => 'Formato de solicitud de baja temporal',
            'documentos/normatividad/reglamento-interno.pdf' => 'Reglamento interno del estudiantado',
            'documentos/manuales/plataforma-educativa.pdf' => 'Guía de uso de la plataforma educativa',
            'documentos/convocatorias/calendario-2026-2027.pdf' => 'Calendario escolar 2026-2027',
            'documentos/formatos/reinco.pdf' => 'Formatos de evaluación REINCO',
            'documentos/formatos/boleta-oficial.xlsx' => 'Formato oficial de boleta de calificaciones',
            'documentos/reportes/reporte-2026-09.pdf' => 'Reporte financiero septiembre 2026',
            'documentos/reportes/reporte-2026-10.pdf' => 'Reporte financiero octubre 2026',
        ];

        foreach ($archivos as $ruta => $titulo) {
            if (Storage::disk('local')->exists($ruta)) {
                continue;
            }

            if ($ruta === 'documentos/formatos/boleta-oficial.xlsx') {
                $plantilla = resource_path('boleta/plantilla.xlsx');
                if (is_file($plantilla)) {
                    Storage::disk('local')->put($ruta, (string) file_get_contents($plantilla), 'private');
                }
                continue;
            }

            $contenido = str_ends_with($ruta, '.docx')
                ? $this->docxDemo($titulo)
                : $this->pdfDemo($titulo);

            Storage::disk('local')->put($ruta, $contenido, 'private');
        }
    }

    private function pdfDemo(string $titulo): string
    {
        $texto = 'BT /F1 16 Tf 72 720 Td ('.$this->escaparPdf($this->ascii($titulo)).') Tj ET';
        $texto .= ' BT /F1 11 Tf 72 695 Td (Documento de demostracion - Escuela de Bachilleres Lic. Antonio Maria de Rivera) Tj ET';

        $objetos = [
            '<</Type/Catalog/Pages 2 0 R>>',
            '<</Type/Pages/Kids[3 0 R]/Count 1>>',
            '<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Resources<</Font<</F1 5 0 R>>>>/Contents 4 0 R>>',
            '<</Length '.strlen($texto).'>>stream'.$texto.'endstream',
            '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>',
        ];

        $pdf = "%PDF-1.4\n";
        $desplazamientos = [];

        foreach ($objetos as $indice => $objeto) {
            $desplazamientos[] = strlen($pdf);
            $pdf .= ($indice + 1).' 0 obj'."\n".$objeto."\nendobj\n";
        }

        $inicioXref = strlen($pdf);
        $pdf .= 'xref'."\n".'0 6'."\n".'0000000000 65535 f '."\n";

        foreach ($desplazamientos as $desplazamiento) {
            $pdf .= sprintf('%010d 00000 n '."\n", $desplazamiento);
        }

        $pdf .= 'trailer'."\n".'<</Size 6/Root 1 0 R>>'."\n".'startxref'."\n".$inicioXref."\n".'%%EOF'."\n";

        return $pdf;
    }

    private function docxDemo(string $titulo): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            .'<w:p><w:r><w:t>'.htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>'
            .'<w:p><w:r><w:t>Documento de demostracion - Escuela de Bachilleres Lic. Antonio Maria de Rivera</w:t></w:r></w:p>'
            .'</w:body></w:document>';

        $ruta = tempnam(sys_get_temp_dir(), 'boleta_docx_');
        $zip = new ZipArchive();
        $zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>');
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        $contenido = (string) file_get_contents($ruta);
        @unlink($ruta);

        return $contenido;
    }

    private function ascii(string $valor): string
    {
        return preg_replace('/[^\x20-\x7E]/', ' ', $valor) ?: 'Documento';
    }

    private function escaparPdf(string $valor): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $valor);
    }
}
