<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;

class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'db_orderdetail';
    public $timestamps = false;
    protected $with = ['variant'];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
