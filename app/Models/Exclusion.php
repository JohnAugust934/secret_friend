<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exclusion extends Model
{
    protected $fillable = ['group_id', 'user_id', 'excluded_id'];

    public function participant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function excluded()
    {
        return $this->belongsTo(User::class, 'excluded_id');
    }
}
