<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttributeValue;

class ProductVariantValue extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant';
    protected $with = ['product_attribute_values'];

    public function product_attribute_values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}

