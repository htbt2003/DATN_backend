<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantValue;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant';
    protected $with = ['variant_values'];
    public $timestamps = false;
    public function variant_values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }
}

