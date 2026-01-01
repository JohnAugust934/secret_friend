<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupMember extends Pivot
{
    // Define explicitamente o nome da tabela
    protected $table = 'group_members';

    // Indica que as chaves estrangeiras são incrementais? Não.
    public $incrementing = true;

    protected $fillable = [
        'group_id',
        'user_id',
        'wishlist',
    ];
}
