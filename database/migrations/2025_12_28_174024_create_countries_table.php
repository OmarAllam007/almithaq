<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        if ($isMysql) {
            DB::statement('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
        }

        // Convert whole table

        Schema::create('countries', function (Blueprint $table) use ($isMysql) {
            $table->id();
            $table->string('name');
            $flag = $table->string('flag', 10);
            if ($isMysql) {
                $flag->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            }

            $table->timestamps();
        });

        if ($isMysql) {
            DB::statement('
                ALTER TABLE countries
                CONVERT TO CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
