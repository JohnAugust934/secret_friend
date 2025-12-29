<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pairing extends Model
{
    // AVISO IMPORTANTE: Como mudamos o nome do Model, 
    // precisamos dizer explicitamente qual é a tabela no banco.
    protected $table = 'matches';

    protected $fillable = [
        'group_id',
        'santa_id',
        'giftee_id'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function santa()
    {
        return $this->belongsTo(User::class, 'santa_id');
    }

    public function giftee()
    {
        return $this->belongsTo(User::class, 'giftee_id');
    }
}
