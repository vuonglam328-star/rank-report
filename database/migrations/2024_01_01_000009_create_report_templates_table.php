<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cover_title')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#3c8dbc');
            $table->string('secondary_color', 7)->default('#ffffff');
            $table->json('layout_config_json')->nullable();
            // Example: {"sections":["cover","kpi_summary","position_chart","top_keywords","competitor_sov","landing_pages"]}
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
