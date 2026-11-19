<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puestos_administrativos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_puesto');
            $table->string('nombre', 50)->unique('uk_puestos_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puestos_administrativos');
    }
};
