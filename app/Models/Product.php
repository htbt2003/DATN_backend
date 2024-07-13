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
            ->leftJoinSub($productstoreV, 'productstoreV', function($join){
                $join->on('db_product_variant.id', '=', 'productstoreV.variant_id');
            })
            ->leftJoinSub($orderdetailV, 'orderdetailV', function($join){
                $join->on('db_product_variant.id', '=', 'orderdetailV.variant_id');
            })
            ->select('db_product_variant.*', 'orderdetailV.sum_qty_selled', 'productstoreV.sum_qty_store');
    }
    
    public function variants_promotion($id)
    {
        $productstoreV = ProductStore::select('variant_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('variant_id');

        $orderdetailV = OrderDetail::select('variant_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('variant_id');

        $productsaleV = ProductSale::where([['promotion_id','=', $id]])
            ->join('db_promotion', 'db_promotion.id', '=', 'db_productsale.promotion_id')
            ->select(
                'db_productsale.price_sale',
                'db_productsale.qty',
                'db_productsale.variant_id',
                DB::raw('(SELECT SUM(od.qty) 
                        FROM db_orderdetail od 
                        LEFT JOIN db_order o ON od.order_id = o.id 
                        WHERE o.status NOT IN (5, 6, 7) 
                        AND od.variant_id = db_productsale.variant_id 
                        AND od.created_at >= db_promotion.date_begin 
                        AND od.created_at <= db_promotion.date_end
                        GROUP BY od.variant_id) as sum_qty_sale_selled'),
            );
            
        $query = $this->hasMany(ProductVariant::class)
            ->joinSub($productsaleV, 'productsaleV', function ($join) {
                $join->on('db_product_variant.id', '=', 'productsaleV.variant_id');
            })
            ->joinSub($productstoreV, 'productstoreV', function($join){
                $join->on('db_product_variant.id', '=', 'productstoreV.variant_id');
            })
            ->leftJoinSub($orderdetailV, 'orderdetailV', function($join){
                $join->on('db_product_variant.id', '=', 'orderdetailV.variant_id');
            })
            ->select(
                'db_product_variant.*',
                'orderdetailV.sum_qty_selled',
                'productstoreV.sum_qty_store',
                'productsaleV.price_sale',
                'productsaleV.qty',
                'productsaleV.sum_qty_sale_selled',
            );

        return $query;
    }
}


