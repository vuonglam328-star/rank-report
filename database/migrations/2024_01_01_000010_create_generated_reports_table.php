<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->string('report_title');
            $table->text('summary_text')->nullable();
            $table->json('selected_competitors_json')->nullable();
            // Example: [{"project_id": 3, "label": "Competitor A"}]
            $table->json('selected_sections_json')->nullable();
            // Example: ["kpi_summary","position_chart","top_keywords"]
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'snapshot_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
