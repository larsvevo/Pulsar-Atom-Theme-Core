<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('website_shop_articles', function (Blueprint $table) {
            $table->boolean('is_giftable')->default(true)->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_shop_articles', function (Blueprint $table) {
            $table->dropColumn('is_giftable');
        });
    }
};
