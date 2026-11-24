<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('direccion', 20); // 'a_tienda' o 'a_comprador'
            $table->foreignId('autor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->unsignedTinyInteger('estrellas'); // 1 a 5
            $table->timestamp('creada_en')->useCurrent();

            $table->unique(['pedido_id', 'direccion']);
        });

        DB::statement("ALTER TABLE calificaciones ADD CONSTRAINT check_calificacion_direccion CHECK (direccion IN ('a_tienda', 'a_comprador'))");
        DB::statement("ALTER TABLE calificaciones ADD CONSTRAINT check_calificacion_estrellas CHECK (estrellas BETWEEN 1 AND 5)");
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
