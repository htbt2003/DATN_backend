<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStore;

class ImportInvoice extends Model
{
    use HasFactory;
    protected $table = 'db_import_invoice';
    // protected $with = ['product_invoices'];
    public $timestamps = false;
    public function product_invoices()
    {
        return $this->hasMany(ProductStore::class);
    }
}
                     
