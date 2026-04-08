<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keyword_rankings', function (Blueprint $table) {
            $table->unsignedSmallInteger('kd')->nullable()->after('search_volume');
            $table->unsignedInteger('organic_traffic')->default(0)->after('kd');
        });
    }

    public function down(): void
    {
        Schema::table('keyword_rankings', function (Blueprint $table) {
            $table->dropColumn(['kd', 'organic_traffic']);
        });
    }
};
