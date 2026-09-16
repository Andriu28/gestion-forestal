<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('cedula');
        });

        // Backfill: rellenar el código de los registros existentes
        DB::table('producers')
            ->orderBy('id')
            ->each(function ($producer) {
                if ($producer->cedula_type && $producer->cedula) {
                    DB::table('producers')
                        ->where('id', $producer->id)
                        ->update([
                            'code' => 'CSJ'
                                . strtoupper($producer->cedula_type)
                                . preg_replace('/\D/', '', $producer->cedula),
                        ]);
                }
            });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};