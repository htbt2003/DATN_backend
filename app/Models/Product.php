<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStore;
use App\Models\OrderDetail;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;
    protected $table = 'db_product';
    protected $with = ['images', 'productattributes', 'variants'];
    public $timestamps = false;
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function productattributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
    public function variants()
    {
        $productstoreV = ProductStore::select('variant_id', DB::raw('SUM(qty) as sum_qty_store'))
        ->groupBy('variant_id');

        $orderdetailV = OrderDetail::select('variant_id', DB::raw('SUM(qty) as sum_qty_selled'))
        ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
        ->whereNotIn('db_order.status', [5, 6, 7])
        ->groupBy('variant_id'); 

        return $this->hasMany(ProductVariant::class)
            ->joinSub($productstoreV, 'productstoreV', function($join){
                $join->on('db_product_variant.id', '=', 'productstoreV.variant_id');
            })
            ->leftJoinSub($orderdetailV, 'orderdetailV', function($join){
                $join->on('db_product_variant.id', '=', 'orderdetailV.variant_id');
            })
            ->select('db_product_variant.*', 'orderdetailV.sum_qty_selled', 'productstoreV.sum_qty_store',);
    }

}

