<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['keyword_group_id', 'keyword_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_group_items');
    }
};
