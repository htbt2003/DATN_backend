<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'deviceId', 
    ];
    protected $table = 'db_cart';
    public $timestamps = false;


}
                     