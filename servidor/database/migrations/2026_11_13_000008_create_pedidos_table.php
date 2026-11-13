<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('comprador_id')->constrained('usuarios');
            $table->foreignId('tienda_id')->constrained('tiendas');
            $table->foreignId('punto_entrega_id')->constrained('puntos_entrega');
            $table->timestamp('hora_entrega');
            $table->string('forma_pago');
            $table->string('nota', 200)->nullable();
            $table->decimal('total', 8, 2);
            $table->string('estado')->default('pendiente');
            $table->string('cancelado_por')->nullable();
            $table->timestamp('estado_cambiado_en')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT check_pedido_forma_pago CHECK (forma_pago IN ('efectivo', 'transferencia', 'deuna'))");
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT check_pedido_estado CHECK (estado IN ('pendiente', 'entregado', 'cancelado', 'vencido'))");
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT check_pedido_cancelado_por CHECK (cancelado_por IS NULL OR cancelado_por IN ('comprador', 'vendedor', 'sistema'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};

