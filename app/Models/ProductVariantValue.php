<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;

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
    public function product_variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    protected static function booted()
    {
        static::deleting(function ($productVariantValue) {
            $productVariantValue->product_attribute_value()->delete();
        });
        static::deleting(function ($productVariantValue) {
            $productVariantValue->product_variant()->delete();
        });
    }
}

