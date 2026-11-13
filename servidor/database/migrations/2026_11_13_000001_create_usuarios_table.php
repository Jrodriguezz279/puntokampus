<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent;');

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->unique();
            $table->string('nombres');
            $table->string('codigo_estudiante')->nullable();
            $table->string('facultad')->nullable();
            $table->string('carrera')->nullable();
            $table->unsignedSmallInteger('semestre')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('mostrar_whatsapp')->default(false);
            $table->boolean('es_admin')->default(false);
            $table->string('password')->nullable(); // Para panel Filament admin
            $table->timestamp('acepto_privacidad_en')->nullable();
            $table->string('estado')->default('activa');
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT check_usuario_semestre CHECK (semestre IS NULL OR (semestre >= 1 AND semestre <= 10))");
        DB::statement("ALTER TABLE usuarios ADD CONSTRAINT check_usuario_estado CHECK (estado IN ('activa', 'desactivada'))");

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('usuarios');
    }
};

