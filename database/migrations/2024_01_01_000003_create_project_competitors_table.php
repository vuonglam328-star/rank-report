<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();
            $table->foreignId('competitor_project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();
            $table->timestamps();

            // Prevent duplicate competitor assignments
            $table->unique(['main_project_id', 'competitor_project_id'], 'unique_main_competitor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_competitors');
    }
};
