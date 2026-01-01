<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'event_date',
        'budget',
        'owner_id',
        'invite_token',
        'is_drawn',
    ];

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
}
