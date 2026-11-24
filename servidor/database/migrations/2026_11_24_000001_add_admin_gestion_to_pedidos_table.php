<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'admin_nota')) {
                $table->text('admin_nota')->nullable()->after('entregado_con');
            }
            if (!Schema::hasColumn('pedidos', 'admin_id')) {
                $table->foreignId('admin_id')->nullable()->after('admin_nota')->constrained('usuarios')->nullOnDelete();
            }
            if (!Schema::hasColumn('pedidos', 'admin_gestionado_en')) {
                $table->timestamp('admin_gestionado_en')->nullable()->after('admin_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'admin_id')) {
                $table->dropForeign(['admin_id']);
                $table->dropColumn('admin_id');
            }
            if (Schema::hasColumn('pedidos', 'admin_nota')) {
                $table->dropColumn('admin_nota');
            }
            if (Schema::hasColumn('pedidos', 'admin_gestionado_en')) {
                $table->dropColumn('admin_gestionado_en');
            }
        });
    }
};
