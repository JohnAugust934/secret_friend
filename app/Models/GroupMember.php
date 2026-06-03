<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupMember extends Pivot
{
    // Define explicitamente o nome da tabela
    protected $table = 'group_members';

    // Pivot com id() próprio (incomum): requer $incrementing = true.
    public $incrementing = true;

    protected $fillable = [
        'group_id',
        'user_id',
        'wishlist',
    ];
}
