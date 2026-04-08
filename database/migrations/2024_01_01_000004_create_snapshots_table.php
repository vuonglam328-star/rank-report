<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('snapshot_name');
            $table->date('report_date');
            $table->enum('snapshot_type', ['ahrefs', 'semrush', 'manual'])->default('ahrefs');
            $table->string('source_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending');
            $table->unsignedInteger('total_keywords')->default(0);
            $table->timestamps();

            // Allow duplicate dates per project with unique constraint that can be bypassed (revision)
            $table->unique(['project_id', 'report_date'], 'unique_project_snapshot_date');
            $table->index(['project_id', 'report_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
