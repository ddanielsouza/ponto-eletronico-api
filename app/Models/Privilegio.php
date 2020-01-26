<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Privilegio extends Model
{
    use \App\Helpers\ISOSerialization;
    protected $table = "privilegios";
    
    protected $fillable = [
        'id',
        'descricao',
        'alias'
    ];
}
