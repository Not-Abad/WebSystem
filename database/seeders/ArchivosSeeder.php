<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
            'documentos/reportes',
            'imagenes/noticias',
            'imagenes/galeria',
        ];

        foreach ($carpetas as $carpeta) {
            Storage::disk('local')->makeDirectory($carpeta);
        }
    }
}
