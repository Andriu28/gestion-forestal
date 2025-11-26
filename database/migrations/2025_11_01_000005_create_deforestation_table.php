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
            $table->integer('year');
            $table->decimal('deforested_area_ha', 10, 2);
            $table->decimal('percentage_loss', 5, 2);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['polygon_id', 'year']);
            $table->index(['polygon_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deforestation');
    }
};