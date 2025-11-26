<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('state_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['name', 'state_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('municipalities');
    }
};