<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;
use App\Models\ProductVariantValue;

class ProductAttributeValue extends Model
{
    use HasFactory;
    protected $table = 'db_product_attribute_value';
    protected $fillable = [
        'product_attribute_id',
        'attribute_value_id', 
    ];
    protected $with = ['attribute_value'];
    public $timestamps = false;
    public function attribute_value()
    {
        return $this->belongsTo(AttributeValue::class);
    }
    public function product_variant_values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    protected static function booted()
    {
        static::deleting(function ($productAttributeValue) {
            $productAttributeValue->product_variant_values()->delete();
        });
    }

}

