<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('current_position')->nullable(); // null = not ranked
            $table->unsignedSmallInteger('previous_position')->nullable();
            $table->smallInteger('position_change')->default(0); // positive = improved (went up)
            $table->unsignedInteger('search_volume')->default(0);
            $table->string('target_url', 1000)->nullable();
            $table->string('location')->nullable();
            $table->string('device')->nullable();
            $table->unsignedInteger('visibility_points')->default(0);
            $table->json('raw_data_json')->nullable(); // full CSV row for tracing
            $table->timestamps();

            $table->unique(['snapshot_id', 'keyword_id'], 'unique_snapshot_keyword');
            $table->index(['snapshot_id', 'current_position']);
            $table->index(['snapshot_id', 'position_change']);
            $table->index('keyword_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_rankings');
    }
};
