<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_acceso', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->index();
            $table->string('codigo_hash');
            $table->timestamp('expira_en');
            $table->unsignedSmallInteger('intentos')->default(0);
            $table->timestamp('usado_en')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_acceso');
    }
};
