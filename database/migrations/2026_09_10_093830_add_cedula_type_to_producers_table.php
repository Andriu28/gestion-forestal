<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            // Quitar el unique simple de la migración anterior
            $table->dropUnique('producers_cedula_unique');

            // Añadir el tipo de nacionalidad (V, E, P, J, G)
            $table->char('cedula_type', 1)->nullable()->after('lastname');

            // Unique compuesto: la misma cédula numérica puede existir para V y E
            $table->unique(['cedula_type', 'cedula']);
        });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropUnique(['cedula_type', 'cedula']);
            $table->dropColumn('cedula_type');
            $table->unique('cedula');
        });
    }
};