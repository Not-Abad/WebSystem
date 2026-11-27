<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El kardex ya no solo registra materias cursadas: también refleja en qué
     * recursamiento está la materia y si se aprobó o derivó en baja temporal.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if ($this->tieneRestriccion('chk_kardex_estatus')) {
            DB::statement('ALTER TABLE `kardex_materias` DROP CONSTRAINT `chk_kardex_estatus`');
        }

        if (! $this->tieneRestriccion('chk_kardex_estatus_r')) {
            DB::statement("ALTER TABLE `kardex_materias` ADD CONSTRAINT `chk_kardex_estatus_r` CHECK (`estatus` IN ('cursada', 'r1', 'r2', 'r3', 'r4', 'aprobada_r', 'baja_temporal'))");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if ($this->tieneRestriccion('chk_kardex_estatus_r')) {
            DB::statement('ALTER TABLE `kardex_materias` DROP CONSTRAINT `chk_kardex_estatus_r`');
        }

        if (! $this->tieneRestriccion('chk_kardex_estatus')) {
            DB::statement("ALTER TABLE `kardex_materias` ADD CONSTRAINT `chk_kardex_estatus` CHECK (`estatus` IN ('cursada'))");
        }
    }

    private function tieneRestriccion(string $nombre): bool
    {
        return DB::select(
            'SELECT CONSTRAINT_NAME
               FROM information_schema.TABLE_CONSTRAINTS
              WHERE CONSTRAINT_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
                AND CONSTRAINT_TYPE = ?',
            ['kardex_materias', $nombre, 'CHECK']
        ) !== [];
    }
};
