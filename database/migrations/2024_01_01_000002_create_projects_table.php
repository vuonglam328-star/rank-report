<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('domain');
            $table->enum('project_type', ['main', 'competitor', 'partner', 'benchmark'])
                  ->default('main');
            $table->string('country_code', 10)->default('VN');
            $table->enum('device_type', ['desktop', 'mobile', 'all'])->default('desktop');
            $table->enum('status', ['active', 'paused', 'archived'])->default('active');
            $table->boolean('is_main_project')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'project_type']);
            $table->index('domain');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
