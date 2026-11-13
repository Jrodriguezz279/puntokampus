<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_premium', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas')->cascadeOnDelete();
            $table->string('numero_comprobante');
            $table->decimal('monto', 8, 2);
            $table->text('foto_comprobante');
            $table->string('estado')->default('pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->timestamp('foto_borrada_en')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE solicitudes_premium ADD CONSTRAINT check_solicitud_estado CHECK (estado IN ('pendiente', 'aprobada', 'rechazada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_premium');
    }
};
