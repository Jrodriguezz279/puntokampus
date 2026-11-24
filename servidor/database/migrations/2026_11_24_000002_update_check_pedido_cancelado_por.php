<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS check_pedido_cancelado_por");
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT check_pedido_cancelado_por CHECK (cancelado_por IS NULL OR cancelado_por IN ('comprador', 'vendedor', 'sistema', 'administrador'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS check_pedido_cancelado_por");
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT check_pedido_cancelado_por CHECK (cancelado_por IS NULL OR cancelado_por IN ('comprador', 'vendedor', 'sistema'))");
    }
};
