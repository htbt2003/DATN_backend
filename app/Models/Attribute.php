<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Brand;

class Attribute extends Model
{
    use HasFactory;
    protected $table = 'db_attribute';
    protected $with = ['product_attribute_values'];

    public function attribute_values()
    {
        return $this->hasMany(Attribute::class);
    }
}
                     
