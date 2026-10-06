<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rule numbers sort wrongly as strings ("702.100" before "702.11"), so the
 * importer records each rule's place in the rules file. Existing rows stay at
 * 0 until the next `rules:import-comprehensive --force`.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comprehensive_rules', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('is_glossary')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprehensive_rules', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn('position');
        });
    }
};
