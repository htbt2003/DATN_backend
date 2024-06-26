<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAttributeValue;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $table = 'db_product_attribute';
    protected $with = ['product_attribute_values'];
    public $timestamps = false;
    public function product_attribute_values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}

