<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttributeValue;
use App\Models\Attribute;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $table = 'db_product_attribute';
    protected $with = ['product_attribute_values', 'attribute'];
    public $timestamps = false;
    public function product_attribute_values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}

