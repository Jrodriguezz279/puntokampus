<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('nombre_producto');
            $table->decimal('precio_unitario', 8, 2);
            $table->unsignedSmallInteger('cantidad');
            $table->decimal('subtotal', 8, 2);
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE pedido_items ADD CONSTRAINT check_pedido_items_cantidad CHECK (cantidad >= 1 AND cantidad <= 20)");
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};

