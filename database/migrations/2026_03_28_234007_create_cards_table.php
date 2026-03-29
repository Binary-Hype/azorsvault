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
        Schema::create('cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('oracle_id')->nullable()->index();
            $table->string('name', 300);
            $table->string('mana_cost', 100)->nullable();
            $table->decimal('cmc', 12, 1)->nullable();
            $table->string('type_line', 300)->nullable();
            $table->text('oracle_text')->nullable();
            $table->json('colors')->nullable();
            $table->json('color_identity')->nullable();
            $table->json('keywords')->nullable();
            $table->string('power', 10)->nullable();
            $table->string('toughness', 10)->nullable();
            $table->string('loyalty', 10)->nullable();
            $table->string('layout', 30);
            $table->string('set', 10)->index();
            $table->string('set_name', 100);
            $table->string('collector_number', 20);
            $table->string('rarity', 20)->index();
            $table->date('released_at')->nullable();
            $table->boolean('reprint')->default(false);
            $table->boolean('digital')->default(false);
            $table->boolean('reserved')->default(false);
            $table->json('image_uris')->nullable();
            $table->json('legalities')->nullable();
            $table->json('prices')->nullable();
            $table->integer('edhrec_rank')->nullable()->index();
            $table->text('flavor_text')->nullable();
            $table->json('games')->nullable();
            $table->json('finishes')->nullable();
            $table->json('card_faces')->nullable();
            $table->json('all_parts')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('mana_cost');
            $table->index('type_line');
            $table->index('cmc');
            $table->index('power');
            $table->index('toughness');
            $table->fullText('name', 'cards_name_fulltext');
            $table->fullText('oracle_text', 'cards_oracle_text_fulltext');
            $table->unique(['set', 'collector_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
