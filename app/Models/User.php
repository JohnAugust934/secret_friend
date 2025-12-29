<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relacionamento 1: Grupos que o usuário CRIOU (é dono)
    public function groupsOwned()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    // Relacionamento 2: Grupos que o usuário PARTICIPA
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('wishlist') // Permite acessar a wishlist
            ->withTimestamps();
    }
}
