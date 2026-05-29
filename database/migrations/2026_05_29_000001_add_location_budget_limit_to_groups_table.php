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
        Schema::table('groups', function (Blueprint $table) {
            // Local físico do evento (ex: "Casa da Avó", "Restaurante XYZ")
            $table->string('location')->nullable()->after('description');

            // Descrição textual do intervalo de orçamento (ex: "10€ - 20€")
            // Complementa o campo 'budget' numérico já existente.
            // Se preenchido, é exibido em vez do 'budget' na UI.
            $table->string('budget_limit', 100)->nullable()->after('budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['location', 'budget_limit']);
        });
    }
};
