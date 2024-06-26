<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttributeValue;

class ProductVariantValue extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant_value';
    protected $with = ['product_attribute_value'];
    public $timestamps = false;
    public function product_attribute_value()
    {
        return $this->belongsTo(ProductAttributeValue::class);
    }
}

