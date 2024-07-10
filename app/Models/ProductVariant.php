<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantValue;
use App\Models\ProductSale;
use Carbon\Carbon;
use App\Models\ProductStore;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    use HasFactory;
    protected $table = 'db_product_variant';
    protected $with = ['variant_values', 'sale'];
    public $timestamps = false;
    public function variant_values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }
    public function sale()
    {

        return $this->belongsTo(ProductSale::class)
        ->join('db_promotion.id', '=', 'db_productsale.promotion_id')
        ->select(
            'db_productsale.id',
            'db_productsale.price_sale',
            'db_productsale.qty',
            'db_productsale.created_at',
            'db_productsale.variant_id',
            // 'db_productsale.product_id',
            DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.variant_id = db_productsale.variant_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.variant_id) as sum_qty_sale_selled'),
                )
        ->where('date_begin', '<=', Carbon::now())
        ->where('date_end', '>=', Carbon::now())
        ->where('db_promotion.status', '=', 1);
    }
}

