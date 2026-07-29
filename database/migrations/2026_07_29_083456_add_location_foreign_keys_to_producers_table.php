<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            // Agregar columnas de claves foráneas (nullable)
            $table->foreignId('state_id')->nullable()->after('address')->constrained()->nullOnDelete();
            $table->foreignId('municipality_id')->nullable()->after('state_id')->constrained()->nullOnDelete();
            $table->foreignId('parish_id')->nullable()->after('municipality_id')->constrained()->nullOnDelete();

            // Índices para búsquedas rápidas
            $table->index('state_id');
            $table->index('municipality_id');
            $table->index('parish_id');
        });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['parish_id']);
            $table->dropColumn(['state_id', 'municipality_id', 'parish_id']);
        });
    }
};