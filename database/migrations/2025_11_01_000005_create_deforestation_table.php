<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deforestation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polygon_id')->constrained()->onDelete('cascade');
            
            // Aquí el orden físico que deseas
            $table->integer('start_year');
            $table->integer('end_year');
            
            // Actualizamos a 4 decimales de una vez
            $table->decimal('deforested_area_ha', 12, 4);
            $table->decimal('percentage_loss', 5, 2);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Actualizamos los índices para que coincidan con los nuevos nombres
            $table->unique(['polygon_id', 'start_year', 'end_year'], 'deforestation_period_unique');
            $table->index(['polygon_id', 'start_year'], 'deforestation_start_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deforestation');
    }
};