<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Category;
use App\Models\Brand;

class ProductSale extends Model
{
    use HasFactory;
    protected $table = 'db_productsale';
    protected $with = ['product', 'product.category', 'product.brand'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}