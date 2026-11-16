<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna 'proposito' a la tabla codigos_acceso si no existe
        if (Schema::hasTable('codigos_acceso') && !Schema::hasColumn('codigos_acceso', 'proposito')) {
            Schema::table('codigos_acceso', function (Blueprint $table) {
                $table->string('proposito', 40)->default('acceso')->after('codigo_hash');
            });
        }

        // 2. Asignar contraseñas a usuarios existentes de prueba sin eliminarlos
        if (Schema::hasTable('usuarios')) {
            // Carlos Vendedor
            DB::table('usuarios')
                ->where('correo', 'carlos.vendedor@est.utb.edu.ec')
                ->whereNull('password')
                ->update(['password' => Hash::make('Vendedor2026!')]);

            // María Compradora
            DB::table('usuarios')
                ->where('correo', 'maria.compradora@est.utb.edu.ec')
                ->whereNull('password')
                ->update(['password' => Hash::make('Compradora2026!')]);

            // Administrador
            DB::table('usuarios')
                ->where('correo', 'admin@utb.edu.ec')
                ->whereNull('password')
                ->update(['password' => Hash::make('PuntoKampus2026!')]);

            // Cualquier otro usuario que no tenga contraseña asignada todavía
            DB::table('usuarios')
                ->whereNull('password')
                ->update(['password' => Hash::make('PuntoKampus2026!')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('codigos_acceso') && Schema::hasColumn('codigos_acceso', 'proposito')) {
            Schema::table('codigos_acceso', function (Blueprint $table) {
                $table->dropColumn('proposito');
            });
        }
    }
};
