<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('codigo_entrega', 500)->nullable()->after('estado');
            $table->tinyInteger('intentos_codigo')->default(0)->after('codigo_entrega');
            $table->boolean('codigo_bloqueado')->default(false)->after('intentos_codigo');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['codigo_entrega', 'intentos_codigo', 'codigo_bloqueado']);
        });
    }
};
