<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pairing;

class Group extends Model
{
    // Campos que podem ser preenchidos via formulário (Mass Assignment)
    protected $fillable = [
        'name',
        'description',
        'event_date',
        'budget',
        'owner_id',
        'invite_token',
        'is_drawn'
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_drawn' => 'boolean',
    ];

    // O dono do grupo
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Os membros do grupo (Tabela Pivô)
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('wishlist')
            ->withTimestamps();
    }

    // Os pares sorteados (Resultados)
    public function matches()
    {
        // Mudou de Match::class para Pairing::class
        return $this->hasMany(Pairing::class, 'group_id');
    }
}
