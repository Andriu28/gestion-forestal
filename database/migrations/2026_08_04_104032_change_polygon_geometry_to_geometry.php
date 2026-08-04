<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('polygons', function (Blueprint $table) {
            // Cambiar de POLYGON a GEOMETRY (permite MultiPolygon, etc.)
            $table->geometry('geometry', 'GEOMETRY', 4326)->change();
        });
    }

    public function down()
    {
        Schema::table('polygons', function (Blueprint $table) {
            $table->geometry('geometry', 'POLYGON', 4326)->change();
        });
    }
};