<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;

class ProductAttributeValue extends Model
{
    use HasFactory;
    protected $table = 'db_product_attribute_value';
    protected $with = ['attribute_values'];

    public function attribute_values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}

