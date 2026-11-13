<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->decimal('precio_premium', 8, 2)->nullable();
            $table->string('banco')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('titular_cuenta')->nullable();
            $table->string('usuario_deuna')->nullable();
            $table->date('fin_periodo_academico')->nullable();
            $table->unsignedInteger('dias_borrado_comprobante')->default(30);
            $table->unsignedInteger('limite_pedidos_gratis')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
