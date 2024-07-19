<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeValue;

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
}

