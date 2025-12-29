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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('event_date')->nullable(); // Data da revelação
            $table->decimal('budget', 10, 2)->nullable(); // Valor estipulado (ex: 50.00)

            // Chave estrangeira para quem criou o grupo (Admin)
            // 'constrained' assume que a tabela se chama 'users'
            // 'onDelete' cascade deleta o grupo se o dono for deletado
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            // Status do sorteio: false = aberto, true = sorteado
            $table->boolean('is_drawn')->default(false);

            // Token único para convite (ex: site.com/invite/aB3x9Z)
            $table->string('invite_token')->unique();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
