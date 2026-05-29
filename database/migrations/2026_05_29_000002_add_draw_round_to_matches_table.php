<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adiciona o campo draw_round à tabela matches e recria as constraints únicas
     * para incluir o round — permitindo múltiplos sorteios por grupo (re-sorteio)
     * com histórico preservado.
     *
     * ATENÇÃO: dropUnique() usa o nome convencional do Laravel para a constraint.
     * Se as constraints foram criadas com nomes personalizados, ajuste os nomes abaixo.
     */
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            // Adiciona o round do sorteio. Sorteio inicial = 1.
            $table->unsignedSmallInteger('draw_round')->default(1)->after('group_id');

            // Cria índices normais para as chaves estrangeiras primeiro
            // Isto impede que o MySQL bloqueie o drop das constraints únicas
            $table->index('group_id');
            $table->index('santa_id');
            $table->index('giftee_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            // Agora o MySQL permite apagar os índices únicos porque as FKs têm os seus próprios índices
            $table->dropUnique('matches_group_id_santa_id_unique');
            $table->dropUnique('matches_group_id_giftee_id_unique');
        });

        Schema::table('matches', function (Blueprint $table) {
            // Cria novas constraints únicas que incluem o round
            $table->unique(['group_id', 'santa_id', 'draw_round'], 'matches_group_santa_round_unique');
            $table->unique(['group_id', 'giftee_id', 'draw_round'], 'matches_group_giftee_round_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique('matches_group_santa_round_unique');
            $table->dropUnique('matches_group_giftee_round_unique');

            $table->dropColumn('draw_round');
        });

        Schema::table('matches', function (Blueprint $table) {
            // Restaura as constraints originais
            $table->unique(['group_id', 'santa_id']);
            $table->unique(['group_id', 'giftee_id']);
        });

        Schema::table('matches', function (Blueprint $table) {
            // Removemos os índices adicionais agora que as constraints originais voltaram
            $table->dropIndex(['group_id']);
            $table->dropIndex(['santa_id']);
            $table->dropIndex(['giftee_id']);
        });
    }
};
