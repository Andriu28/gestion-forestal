<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Eliminar los campos detected_*
            $table->dropColumn([
                'detected_parish',
                'detected_municipality', 
                'detected_state'
            ]);
            
            // Opcional: puedes renombrar location_data a osm_raw_data para mayor claridad
            // $table->renameColumn('location_data', 'osm_raw_data');
        });
    }

    public function down()
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Por si necesitas revertir
            $table->string('detected_parish')->nullable()->after('parish_id');
            $table->string('detected_municipality')->nullable()->after('detected_parish');
            $table->string('detected_state')->nullable()->after('detected_municipality');
        });
    }
};