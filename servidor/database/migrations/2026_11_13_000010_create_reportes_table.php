<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas')->cascadeOnDelete();
            $table->foreignId('reportado_por')->constrained('usuarios')->cascadeOnDelete();
            $table->string('motivo');
            $table->string('detalle', 300)->nullable();
            $table->string('estado')->default('nuevo');
            $table->foreignId('revisado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->text('nota_admin')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE reportes ADD CONSTRAINT check_reporte_motivo CHECK (motivo IN ('prohibido', 'no_entrego', 'informacion_falsa', 'trato_inadecuado', 'otro'))");
        DB::statement("ALTER TABLE reportes ADD CONSTRAINT check_reporte_estado CHECK (estado IN ('nuevo', 'sin_accion', 'tienda_dada_de_baja'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
