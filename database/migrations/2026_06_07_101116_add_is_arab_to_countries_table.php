<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Arab League member states by country ID
    private const ARAB_COUNTRY_IDS = [4, 14, 39, 48, 53, 80, 86, 93, 97, 100, 112, 120, 132, 142, 152, 161, 166, 171, 179, 185, 194];

    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_arab')->default(false)->after('ar_name');
        });

        DB::table('countries')
            ->whereIn('id', self::ARAB_COUNTRY_IDS)
            ->update(['is_arab' => true]);
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('is_arab');
        });
    }
};
