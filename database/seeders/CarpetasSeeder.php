<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Estructura de carpetas del gestor de archivos. No crea documentos: los
 * archivos reales se suben desde el módulo de gestión.
 */
class CarpetasSeeder extends Seeder
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
    }
}
