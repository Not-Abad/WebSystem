<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_auditoria', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id_bitacora');
            $table->unsignedBigInteger('id_usuario');
            $table->string('tabla_afectada', 60);
            $table->unsignedBigInteger('id_registro_afectado')->nullable();
            $table->string('accion', 40);
            $table->text('descripcion');
            $table->string('ip_origen', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('id_usuario', 'idx_bitacora_usuario');
            $table->index(['tabla_afectada', 'created_at'], 'idx_bitacora_tabla_fecha');
            $table->foreign('id_usuario', 'fk_bitacora_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_auditoria');
    }
};
