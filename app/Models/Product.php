<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Image;

class Product extends Model
{
    use HasFactory;
    protected $table = 'db_product';
    protected $with = ['images', 'productattributes', 'variants', 'sale'];
    public $timestamps = false;
    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    // public function brand()
    // {
    //     return $this->belongsTo(Brand::class);
    // }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function productattributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function sale()
    {
        return $this->belongsTo(ProductSale::class)
                ->where('date_begin', '<=', Carbon::now())
                ->where('date_end', '>=', Carbon::now())
                ->where('status', '=', 1)
                ->select('id', 'price_sale');
    }

}

