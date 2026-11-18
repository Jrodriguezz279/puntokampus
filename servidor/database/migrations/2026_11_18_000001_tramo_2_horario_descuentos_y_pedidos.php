<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Campos de Tienda para Horarios por días y franja, e interruptor de recepción de pedidos
        if (Schema::hasTable('tiendas')) {
            Schema::table('tiendas', function (Blueprint $table) {
                if (!Schema::hasColumn('tiendas', 'recibiendo_pedidos')) {
                    $table->boolean('recibiendo_pedidos')->default(true)->after('horario');
                }
                if (!Schema::hasColumn('tiendas', 'dias_atencion')) {
                    $table->string('dias_atencion', 100)->nullable()->after('recibiendo_pedidos');
                }
                if (!Schema::hasColumn('tiendas', 'hora_apertura')) {
                    $table->string('hora_apertura', 10)->nullable()->after('dias_atencion');
                }
                if (!Schema::hasColumn('tiendas', 'hora_cierre')) {
                    $table->string('hora_cierre', 10)->nullable()->after('hora_apertura');
                }
            });
        }

        // 2. Campos de Productos para descuentos y promociones en Tramo 2
        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                if (!Schema::hasColumn('productos', 'precio_descuento')) {
                    $table->decimal('precio_descuento', 8, 2)->nullable()->after('precio');
                }
                if (!Schema::hasColumn('productos', 'descuento_activo')) {
                    $table->boolean('descuento_activo')->default(false)->after('precio_descuento');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tiendas')) {
            Schema::table('tiendas', function (Blueprint $table) {
                $table->dropColumn(['recibiendo_pedidos', 'dias_atencion', 'hora_apertura', 'hora_cierre']);
            });
        }

        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn(['precio_descuento', 'descuento_activo']);
            });
        }
    }
};
