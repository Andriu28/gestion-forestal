<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('polygons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->geometry('geometry', 'POLYGON', 4326);
            $table->foreignId('producer_id')->constrained()->onDelete('cascade');
            $table->foreignId('parish_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('area_ha', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('producer_id');
            $table->index('parish_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('polygons');
    }
};