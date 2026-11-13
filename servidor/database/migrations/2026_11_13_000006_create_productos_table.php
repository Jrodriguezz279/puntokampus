<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas')->cascadeOnDelete();
            $table->string('nombre', 60);
            $table->string('descripcion', 300)->nullable();
            $table->decimal('precio', 8, 2);
            $table->boolean('disponible')->default(true);
            $table->timestamp('eliminado_en')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE productos ADD CONSTRAINT check_producto_precio CHECK (precio > 0 AND precio <= 500)");
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
