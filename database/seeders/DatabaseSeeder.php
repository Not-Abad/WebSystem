<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Siembrado de producción: únicamente catálogos y estructura.
     * Los datos de personas y el contenido público se dan de alta desde los
     * módulos administrativos o se importan de la base institucional real.
     */
    public function run(): void
    {
        $this->call(CatalogosBaseSeeder::class);
        $this->call(DatosAcademicosSeeder::class);
        $this->call(CarpetasSeeder::class);
    }
}
