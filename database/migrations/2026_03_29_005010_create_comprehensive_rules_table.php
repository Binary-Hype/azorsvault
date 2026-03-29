<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprehensive_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_number', 100)->unique();
            $table->smallInteger('section')->nullable()->index();
            $table->string('chapter', 10)->nullable()->index();
            $table->text('content');
            $table->boolean('is_glossary')->default(false);
            $table->date('effective_date');
            $table->timestamps();

            $table->fullText('content', 'comprehensive_rules_content_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprehensive_rules');
    }
};
