<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    /**
     * Campos mass-assignable.
     * SEGURANÇA: owner_id, invite_token e is_drawn são INTENCIONALMENTE excluídos —
     * são sempre definidos programaticamente no controller/service e jamais
     * devem ser controláveis pelo usuário via requisição HTTP.
     */
    protected $fillable = [
        'name',
        'description',
        'event_date',
        'budget',
        'location',
        'budget_limit',
    ];

    // ISTO É ESSENCIAL PARA O POSTGRES
    protected $casts = [
        'event_date' => 'datetime',
        'is_drawn' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->using(GroupMember::class) // <--- ADICIONADO: Usa o Pivot Personalizado
            ->withPivot('wishlist')
            ->withTimestamps();
    }

    public function exclusions()
    {
        return $this->hasMany(Exclusion::class);
    }

    /**
     * Histórico de sorteios do grupo (todos os rounds).
     * Usar ->where('draw_round', N) para filtrar por round específico.
     */
    public function pairings()
    {
        return $this->hasMany(Pairing::class);
    }
}
