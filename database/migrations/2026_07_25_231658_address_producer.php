<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('is_active');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->text('address')->nullable()->after('longitude');
        });
    }

    public function down()
    {
        Schema::table('producers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address']);
        });
    }
};
