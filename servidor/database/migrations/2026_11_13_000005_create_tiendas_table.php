<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->string('nombre', 40);
            $table->string('slug')->unique();
            $table->string('descripcion', 300)->nullable();
            $table->text('logo_url')->nullable();
            $table->text('portada_url')->nullable();
            $table->foreignId('categoria_1_id')->constrained('categorias');
            $table->foreignId('categoria_2_id')->nullable()->constrained('categorias');
            $table->string('horario', 100)->nullable();
            $table->foreignId('punto_entrega_id')->nullable()->constrained('puntos_entrega');
            $table->string('plan')->default('gratis');
            $table->date('premium_hasta')->nullable();
            $table->string('estado')->default('activa');
            $table->text('motivo_baja')->nullable();
            $table->timestamp('dada_de_baja_en')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE tiendas ADD CONSTRAINT check_tienda_plan CHECK (plan IN ('gratis', 'premium'))");
        DB::statement("ALTER TABLE tiendas ADD CONSTRAINT check_tienda_estado CHECK (estado IN ('activa', 'dada_de_baja'))");
        DB::statement("ALTER TABLE tiendas ADD CONSTRAINT check_tienda_categorias_distintas CHECK (categoria_2_id IS NULL OR categoria_1_id <> categoria_2_id)");
    }

    public function down(): void
    {
        Schema::dropIfExists('tiendas');
    }
};
