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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')->constrained()->onDelete('cascade');

            // Santa = Quem dá o presente
            $table->foreignId('santa_id')->constrained('users')->onDelete('cascade');

            // Giftee = Quem recebe o presente
            $table->foreignId('giftee_id')->constrained('users')->onDelete('cascade');

            // Garante que nesse grupo, o Santa só tira uma pessoa
            $table->unique(['group_id', 'santa_id']);

            // Garante que nesse grupo, o Giftee só é tirado por uma pessoa
            $table->unique(['group_id', 'giftee_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
