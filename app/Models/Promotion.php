<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductSale;

class Promotion extends Model
{
    use HasFactory;
    protected $table = 'db_promotion';
    public $timestamps = false;

}