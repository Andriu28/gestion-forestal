<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Campo booleano con índice para filtros rápidos
            $table->boolean('deforested')->default(false)->after('is_active');
            $table->index('deforested');
        });

        // Poblar retroactivamente: marcar TRUE los polígonos que ya tienen deforestación > 0
        DB::statement("
            UPDATE polygons
            SET deforested = true
            WHERE id IN (
                SELECT DISTINCT polygon_id
                FROM deforestation
                WHERE deforested_area_ha > 0
                  AND deleted_at IS NULL
            )
        ");
    }

    public function down(): void
    {
        Schema::table('polygons', function (Blueprint $table) {
            $table->dropIndex(['deforested']);
            $table->dropColumn('deforested');
        });
    }
};