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
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['giftee_id']);
            $table->dropUnique('matches_group_giftee_round_unique');
            $table->text('giftee_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('giftee_id')->change()->constrained('users')->onDelete('cascade');
            $table->unique(['group_id', 'giftee_id', 'draw_round'], 'matches_group_giftee_round_unique');
        });
    }
};
