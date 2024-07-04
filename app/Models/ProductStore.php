<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariant;

class ProductStore extends Model
{
    use HasFactory;
    protected $table = 'db_productstore';
    // protected $with = ['product', 'product.category', 'product.brand'];
    public $timestamps = false;
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }
    protected $with = ['variant'];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

}

