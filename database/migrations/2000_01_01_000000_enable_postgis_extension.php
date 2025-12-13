<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Habilita la extensión PostGIS si no existe.
        // Esto permite usar el tipo de dato 'geometry' en futuras migraciones.
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 2. Elimina la extensión en caso de un 'migrate:rollback'.
        DB::statement('DROP EXTENSION IF EXISTS postgis;');
    }
};