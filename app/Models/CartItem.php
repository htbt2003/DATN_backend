<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;

class CartItem extends Model
{
    use HasFactory;
    protected $table = 'db_cart_item';
    protected $fillable = [
        'cart_id', 
        'product_id', 
        'variant_id ', 
        'quantity', 
        'price', 
        'cost', 
    ];
    public $timestamps = false;
    protected $with = ['variant'];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

}
                     
