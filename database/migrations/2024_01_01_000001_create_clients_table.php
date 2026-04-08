<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('domain')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->enum('report_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly'])
                  ->default('monthly');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
