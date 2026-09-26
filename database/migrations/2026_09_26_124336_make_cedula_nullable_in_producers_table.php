<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('cedula')->nullable()->change();
            // Si code también se genera de la cédula, hazlo nullable también:
            $table->string('code')->nullable()->change();
            // cedula_type ya tiene default 'V' en Livewire, pero por seguridad:
            $table->string('cedula_type', 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('cedula')->nullable(false)->change();
            $table->string('code')->nullable(false)->change();
            $table->string('cedula_type', 2)->nullable(false)->change();
        });
    }
};