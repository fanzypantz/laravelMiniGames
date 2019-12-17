<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lobby extends Model
{
    protected $fillable = [
        'url', 'gameState'
    ];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
