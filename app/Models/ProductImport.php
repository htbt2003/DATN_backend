<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductImport extends Model
{
    use HasFactory;
    protected $table = 'db_productimport';
    protected $with = ['product'];
    public $timestamps = false;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
