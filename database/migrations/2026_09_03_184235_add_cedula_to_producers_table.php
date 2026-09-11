<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable(false)->unique()->after('lastname');
            // Si deseas que sea obligatorio, usa ->nullable(false) pero asegura que todos los registros existentes tengan valor.
        });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropColumn('cedula');
        });
    }
};