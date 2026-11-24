<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            if (!Schema::hasColumn('tiendas', 'deuna_qr_url')) {
                $table->string('deuna_qr_url')->nullable()->after('whatsapp');
            }
        });

        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'entregado_con')) {
                $table->string('entregado_con')->nullable()->after('codigo_bloqueado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            if (Schema::hasColumn('tiendas', 'deuna_qr_url')) {
                $table->dropColumn('deuna_qr_url');
            }
        });

        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'entregado_con')) {
                $table->dropColumn('entregado_con');
            }
        });
    }
};
