<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatidaPonto extends Model
{
    use \App\Helpers\ISOSerialization;

    protected $table = "batidasPonto";
    
    protected $fillable = [
        'id',
        'user_id',
        'horaBatida'
    ];

    protected $casts = [
        'horaBatida' => 'datetime',
    ];
}
