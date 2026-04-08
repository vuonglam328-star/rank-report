<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('keyword', 500);
            $table->string('normalized_keyword', 500); // lowercase + trim
            $table->string('keyword_type')->nullable(); // from Entities column
            $table->boolean('brand_flag')->default(false);
            $table->string('tag')->nullable();
            $table->timestamps();

            // One keyword per project (normalized)
            $table->unique(['project_id', 'normalized_keyword'], 'unique_project_keyword');
            $table->index(['project_id', 'brand_flag']);
            $table->index('normalized_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
