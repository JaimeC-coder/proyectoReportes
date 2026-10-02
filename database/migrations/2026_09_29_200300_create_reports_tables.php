<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('title');
            $table->string('client_name')->nullable();
            $table->string('client_location')->nullable();
            $table->date('service_date');
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('duplicated_from_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->json('signature')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_date');
        });

        Schema::create('report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30);
            $table->json('content')->nullable();
            $table->timestamps();

            $table->index(['report_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_sections');
        Schema::dropIfExists('reports');
    }
};
