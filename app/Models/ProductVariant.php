<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantValue;
use App\Models\ProductSale;
use Carbon\Carbon;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant';
    protected $with = ['variant_values', 'sale'];
    public $timestamps = false;
    public function variant_values()
    {
        return $this->hasMany(ProductVariantValue::class);
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

