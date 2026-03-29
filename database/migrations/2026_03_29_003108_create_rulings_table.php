<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rulings', function (Blueprint $table) {
            $table->id();
            $table->uuid('oracle_id')->index();
            $table->string('source', 20);
            $table->date('published_at');
            $table->text('comment');
            $table->string('content_hash', 64)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rulings');
    }
};
