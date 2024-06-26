<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantValue;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant';
    protected $with = ['product_variant_values'];
    public $timestamps = false;
    public function product_variant_values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }
}

