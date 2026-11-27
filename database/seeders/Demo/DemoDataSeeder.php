<?php

namespace Database\Seeders\Demo;

use Database\Seeders\CatalogosBaseSeeder;
use Database\Seeders\DatosAcademicosSeeder;
use Illuminate\Database\Seeder;

/**
 * Datos inventados para demostración y para la suite de pruebas.
 *
 * No es llamado por DatabaseSeeder: solo se carga de forma explícita cuando se
 * quiere mostrar el sistema o ejecutar las pruebas. Para dejar una instalación
 * limpia basta con `php artisan migrate:fresh --seed --force`.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogosBaseSeeder::class);
        $this->call(DatosAcademicosSeeder::class);
        $this->call(UsuariosPruebaSeeder::class);
        $this->call(PersonalBitacoraSeeder::class);
        $this->call(AdminModulosSeeder::class);
        $this->call(PortalDocenteSeeder::class);
        $this->call(DemoContenidoSeeder::class);
        $this->call(HorariosCompletosSeeder::class);
        $this->call(EvaluacionesCompletasSeeder::class);
        $this->call(GruposCompletosSeeder::class);
        (new HorariosCompletosSeeder())->unificarDocentePorMateria();
        $this->call(KardexDemoSeeder::class);
        $this->call(RecursamientosDemoSeeder::class);
        $this->call(AlumnoTutoresDemoSeeder::class);
        $this->call(ArchivosSeeder::class);
    }
}