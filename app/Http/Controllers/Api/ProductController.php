<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Review;
use App\Models\ProductStore;
use App\Models\ProductSale;
use App\Models\Category;
use App\Models\OrderDetail;
use App\Models\Post;
use App\Models\ProductAttribute;
use App\Models\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    private $currentDateUtc;
    private $currentDate;

    public function __construct()
    {
        // Initialize current date and time in UTC
        $this->currentDateUtc = Carbon::now();
        // Convert to local time zone
        $this->currentDate = $this->currentDateUtc->setTimezone('Asia/Ho_Chi_Minh');
    }     
    function product_new($limit)
    {

        $productstore = ProductStore::where('status', '=', 1)
            ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id');

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
                )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $products = Product::where('status', '=', 1)
            ->joinSub($productstore, 'productstore', function ($join) {
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function ($join) {
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->orderBy('db_product.created_at', 'DESC')
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                'productsale.sum_qty_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            )
            ->limit($limit)
            ->get();
            $products->load(['images', 'productattributes']);
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
            ],
            200
        );
    }   
    function product_sale($limit)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

            $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $products = Product::where([
                ['db_product.status', '=', 1],
            ])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->joinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->orderBy('db_product.created_at', 'DESC')
            ->select(
                    'db_product.id',
                    'db_product.name',
                    'db_product.image',
                    'db_product.price',
                    'db_product.slug',
                    'productsale.price_sale',
                    'productsale.sum_qty_sale_selled',
                    'productstore.sum_qty_store',
                    'orderdetail.sum_qty_selled',
                    'review.avg_rating',
                    )
            -> limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products
            ],
            200
        );
    }
    function product_bestSeller($limit)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id');

            $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $products = Product::where([
                ['db_product.status', '=', 1],
            ])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->joinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->orderBy('orderdetail.sum_qty_selled', 'DESC')
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store as sum_qty_store',
                'orderdetail.sum_qty_selled as sum_qty_selled',
                'review.avg_rating',
            )            
            ->limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products
            ],
            200
        );
    }

    public function product_home($limit, $category_id = 0)
    {
        $listid = array();
        array_push($listid, $category_id + 0);
        $args_cat1 = [
            ['parent_id', '=', $category_id + 0],
            ['status', '=', 1]
        ];
        $list_category1 = Category::where($args_cat1)->get();
        if (count($list_category1) > 0) {
            foreach ($list_category1 as $row1) {
                array_push($listid, $row1->id);
                // $args_cat2 = [
                //     ['parent_id', '=', $row1->id],
                //     ['status', '=', 1]
                // ];
                // $list_category2 = Category::where($args_cat2)->get();
                // if (count($list_category2) > 0) {
                //     foreach ($list_category2 as $row2) {
                //         array_push($listid, $row2->id);
                //     }
                // }
            }
        }
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
        ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                ->whereNotIn('db_order.status', [5, 6, 7])
                ->groupBy('product_id'); 

        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
                ->groupBy('product_id'); 
    
        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $products = Product::where('db_product.status', '=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('db_product.category_id', $listid)
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->orderBy('db_product.created_at', 'DESC')
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            )
            ->limit($limit)
            ->get();
        if(count($products)>0){
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Tải dữ liệu thành công',
                    'products' => $products
                ],
                200
            );
        }
        else{
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Không có dữ liệu',
                    'products' => null
                ],
                200
            );
        }
    }

    public function product_allAction(Request $condition)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                ->whereNotIn('db_order.status', [5, 6, 7])
                ->groupBy('product_id'); 

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');
        

        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
                ->groupBy('product_id'); 
    
        $query = Product::where('db_product.status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                    'db_product.id',
                    'db_product.name',
                    'db_product.image',
                    'db_product.price',
                    'db_product.slug',
                    'db_product.created_at',
                    'productsale.price_sale',
                    'productsale.sum_qty_sale',
                    'productsale.sum_qty_sale_selled',
                    'productstore.sum_qty_store',
                    'orderdetail.sum_qty_selled',
                    'review.avg_rating',
            );            
            // ->groupBy('db_product.id');

        if ($condition->input('brands') != null) {
            $query->whereIn('brand_id', $condition->input('brands'));
        }

        if ($condition->input('categories') != null ) {
            
            $query->whereIn('category_id', $condition->input('categories'));
        }

        if ($condition->has('prices')) {
            $query->whereBetween('price', [
                $condition->prices['from'] ?? 0,
                $condition->prices['to'] ?? 1000000,
            ]);
        }
        if ($condition->has('sort')) {
            $query->orderBy('price', $condition->input('sort'));
        }
        else{
            $query->orderBy('db_product.created_at', 'DESC');
        }
        $priceMax = Product::max('price');
        $total = $query->count();
        $products = $query->paginate(8);
        $categories = Category::where('status', '=', '1')
            ->select('id', 'name')
            ->get();
        $brands = Brand::where('status', '=', '1')
            ->select('id', 'name')
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'categories' => $categories,
                'brands' => $brands,
                'priceMax' => $priceMax,
            ],
            200
        );
    }

    public function product_category($category_id, Request $condition)
    {
        $listid = array();
        array_push($listid, $category_id + 0);
        $args_cat1 = [
            ['parent_id', '=', $category_id + 0],
            ['status', '=', 1]
        ];
        $list_category1 = Category::where($args_cat1)->get();
        if (count($list_category1) > 0) {
            foreach ($list_category1 as $row1) {
                array_push($listid, $row1->id);
                // $args_cat2 = [
                //     ['parent_id', '=', $row1->id],
                //     ['status', '=', 1]
                // ];
                // $list_category2 = Category::where($args_cat2)->get();
                // if (count($list_category2) > 0) {
                //     foreach ($list_category2 as $row2) {
                //         array_push($listid, $row2->id);
                //     }
                // }
            }
        }
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');



        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $query = Product::where('status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('category_id', $listid)
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'db_product.created_at',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            );
            // ->groupBy('db_product.id');
            
        if ($condition->has('prices')) {
            $query->whereBetween('price', [
                $condition->prices['from'] ?? 0,
                $condition->prices['to'] ?? 1000000,
            ]);
        }
        if ($condition->has('sort')) {
            $query->orderBy('price', $condition->input('sort'));
        }
        else{
            $query->orderBy('created_at', 'DESC');
        }
        $priceMax = Product::max('price');
        $products = $query->paginate(8);
        $total = $products->total();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'priceMax' => $priceMax,
            ],
            200
        );

    }

    public function product_brand($brand_id, Request $condition)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


            
        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $query = Product::where([['brand_id', '=', $brand_id], ['status', '=', 1]])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'db_product.created_at',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            );
            // ->groupBy('db_product.id'); 

        if ($condition->has('prices')) {
            $query->whereBetween('price', [
                $condition->prices['from'],
                $condition->prices['to'],
            ]);
        }
        if ($condition->has('sort')) {
            $query->orderBy('price', $condition->input('sort'));
        }
        else{
            $query->orderBy('created_at', 'DESC');
        }
        $priceMax = Product::max('price');
        $products = $query->paginate(8);
        $total = $products->total();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'priceMax' => $priceMax,
            ],
            200
        );
    }
    // function product_order($id, $limit)
    // {
    //     $args = [
    //         ['id', '=', $id],
    //         ['status', '=', 1]
    //     ];
    //     $products = Product::where($args)
    //         ->orderBy('created_at', 'DESC')
    //         -> limit($limit)
    //         ->get();
    //     return response()->json(
    //         [
    //             'status' => true,
    //             'message' => 'Tải dữ liệu thành công',
    //             'products' => $products
    //         ],
    //         200
    //     );
    // }
    public function product_detail($slug)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
        ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $args = [
            ['slug', '=', $slug],
            ['status', '=', 1]
        ];

        $product = Product::where($args)
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'db_product.detail',
                'db_product.metadesc',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            )
            // ->groupBy('db_product.id')
            ->first();
        if($product == null){
            return response()->json(
                ['status' => false, 
                 'message' => 'Không tìm thấy dữ liệu', 
                 'product' =>null
                ],
                400
            );
        }
        $listid = array();
        array_push($listid, $product->category_id);
        $args_cat1 = [
            ['parent_id', '=', $product->category_id],
            ['status', '=', 1]
        ];
        $list_category1 = Category::where($args_cat1)->get();
        if (count($list_category1) > 0) {
            foreach ($list_category1 as $row1) {
                array_push($listid, $row1->id);
                $args_cat2 = [
                    ['parent_id', '=', $row1->id],
                    ['status', '=', 1]
                ];
                $list_category2 = Category::where($args_cat2)->get();
                if (count($list_category2) > 0) {
                    foreach ($list_category2 as $row2) {
                        array_push($listid, $row2->id);
                    }
                }
            }
        }


        $product_other = Product::where([['db_product.id', '!=', $product->id],['status', '=', 1]])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('category_id', $listid)
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            )
            // ->groupBy('db_product.id')
            ->orderBy("db_product.created_at", 'DESC')
            ->limit(8)
            ->get();
            return response()->json(
                ['status' => true, 
                 'message' => 'Tải dữ liệu thành công', 
                 'product' => $product,
                 'product_other'=>$product_other
                ],
                200
            );
        
    }
    public function search(Request $request)
    {
        $search = $request->input('key');
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

        $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');

        $review = Review::select('product_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('product_id'); 

        $products = Product::where('db_product.status', '=', 1)
            ->joinSub($productstore, 'productstore', function ($join) {
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id')
            ->where(function ($query) use ($search) {
                $query->where('db_product.name', 'LIKE', "%$search%")
                    ->orWhere('db_category.name', 'like', '%' . $search . '%')
                    ->orWhere('db_brand.name', 'like', '%' . $search . '%');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })    
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($review, 'review', function($join){
                $join->on('db_product.id', '=', 'review.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'review.avg_rating',
            )
            // ->groupBy('db_product.id')
            ->orderBy('db_product.created_at', "DESC")
            ->get();
        
        $posts = Post::where([['status', '=', 1], ['type', '=', 'post']])
            ->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%")
                    ->orWhere('metadesc', 'LIKE', "%$search%");
            })
            ->orWhereHas('topic', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%$search%");
            })
            ->orderBy('created_at', "DESC")
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'posts' => $posts,
                'search' => $search,

            ],
            200
        );   
    }
    public function product_stores(Request $condition)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
        ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

            $productsale = ProductSale::select(
                'db_productsale.product_id',
                DB::raw('SUM(db_productsale.qty) as sum_qty_sale'),
                DB::raw('MIN(db_productsale.price_sale) as price_sale'),
                DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    INNER JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled')
            )
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->where('db_promotion.date_begin', '<=', $this->currentDate)
            ->where('db_promotion.date_end', '>=', $this->currentDate)
            ->groupBy('db_productsale.product_id', 'sum_qty_sale_selled')
            ->havingRaw('CASE 
                    WHEN sum_qty_sale IS NULL THEN TRUE 
                    ELSE sum_qty_sale - COALESCE(sum_qty_sale_selled, 0) > 0 
                 END');


        $query = Product::where('db_product.status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id')
            ->leftJoinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'productsale.price_sale',
                // 'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'db_category.name as categoryname',
                'db_brand.name as brandname',
            )
            ->orderBy('db_product.created_at', 'DESC');

        if ($condition->input('brandId') != null) {
            $query->where('brand_id', $condition->input('brandId'));
        }

        if ($condition->input('catId') != null ) {
            
            $query->where('category_id', $condition->input('catId'));
        }

        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_product.name', 'like', '%' . $key . '%')
                    ->orWhere('db_category.name', 'like', '%' . $key . '%')
                    ->orWhere('db_brand.name', 'like', '%' . $key . '%');
            });
        }

        $total = $query->count();
        $products = $query->paginate(5);
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();

        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'categories' => $categories,
                'brands' => $brands,
            ],
            200
        );
    }
    public function product_stores_toSale(Request $condition)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
        ->groupBy('product_id');

        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id'); 

        $productsale = ProductSale::select('db_productsale.product_id')
            ->join('db_promotion', 'db_productsale.promotion_id', '=', 'db_promotion.id')
            ->whereDate('db_promotion.date_begin', '<=', $condition['date_begin'])
            ->whereDate('db_promotion.date_end', '>=', $condition['date_begin'])
            ->groupBy('db_productsale.product_id');


        $query = Product::where('db_product.status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id')
            ->whereNotIn('db_product.id', function($query) use ($productsale) {
                $query->select('product_id')
                      ->fromSub($productsale, 'productsale');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            }) 
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                // 'productsale.price_sale',
                // 'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
                'db_category.name as categoryname',
                'db_brand.name as brandname',
            )
            ->orderBy('db_product.created_at', 'DESC');

        if ($condition->input('productIdSelected') != null) {
            $query->whereNotIn('db_product.id', $condition->input('productIdSelected'));
        }

        if ($condition->input('brandId') != null) {
            $query->where('brand_id', $condition->input('brandId'));
        }

        if ($condition->input('catId') != null ) {
            
            $query->where('category_id', $condition->input('catId'));
        }

        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_product.name', 'like', '%' . $key . '%')
                    ->orWhere('db_category.name', 'like', '%' . $key . '%')
                    ->orWhere('db_brand.name', 'like', '%' . $key . '%');
            });
        }

        $total = $query->count();
        $products = $query->paginate(5);
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();

        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'categories' => $categories,
                'brands' => $brands,
            ],
            200
        );
    }

    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Product::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có sản phẩm nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Product::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }
    public function trash(Request $condition)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');
        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                ->whereNotIn('db_order.status', [5, 6, 7])
                ->groupBy('product_id');
        $query = Product::where('db_product.status','=', 0)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id')
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->select('db_category.name as categoryname', 'db_brand.name as brandname','db_product.id','db_product.name', 'db_product.status','db_product.image', 'db_product.price','db_product.slug', 'db_product.created_at', 'productstore.sum_qty_store', 'orderdetail.sum_qty_selled');

        if ($condition->input('brandId') != null) {
            $query->where('brand_id', $condition->input('brandId'));
        }

        if ($condition->input('catId') != null ) {
            
            $query->where('category_id', $condition->input('catId'));
        }

        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_product.name', 'like', '%' . $key . '%')
                    ->orWhere('db_category.name', 'like', '%' . $key . '%')
                    ->orWhere('db_brand.name', 'like', '%' . $key . '%');
            });
        }
        $products = $query->orderBy('db_product.created_at', 'DESC')->paginate(5);
        $total = Product::where('db_product.status', '!=', 0)->count();
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();
        $publish = Product::where('status', '=', 1)->count();
        $trash = Product::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
                'categories' => $categories,
                'brands' => $brands,
            ],
            200
        );
    }
    public function index(Request $condition)
    {
        $productstore = ProductStore::where('status', '=', 1)
        ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');
        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                ->whereNotIn('db_order.status', [5, 6, 7])
                ->groupBy('product_id'); 
        $query = Product::where('db_product.status','!=', 0)
            ->leftJoinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id')
            ->leftJoinSub($orderdetail, 'orderdetail', function($join){
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->select(
                'db_category.name as categoryname',
                'db_brand.name as brandname',
                'db_product.id',
                'db_product.name',
                'db_product.status',
                'db_product.image',
                'db_product.price',
                'db_product.slug',
                'db_product.created_at',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled'
            )
            ->orderBy('db_product.created_at', 'DESC');
        if ($condition->input('brandId') != null) {
            $query->where('brand_id', $condition->input('brandId'));
        }

        if ($condition->input('catId') != null ) {
            
            $query->where('category_id', $condition->input('catId'));
        }

        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_product.name', 'like', '%' . $key . '%')
                    ->orWhere('db_category.name', 'like', '%' . $key . '%')
                    ->orWhere('db_brand.name', 'like', '%' . $key . '%');
            });
        }
        $total = $query->count();
        $products = $query->paginate(5);
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();
        $publish = Product::where('status', '=', 1)->count();
        $trash = Product::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
                'categories' => $categories,
                'brands' => $brands,
            ],
            200
        );
    }
    public function show($id)
    {
        $product = Product::find($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'product' => null
                ],
                404
            );    
        }
        else{
            return response()->json(
                [   
                    'status' => true, 
                    'message' => 'Tải dữ liệu thành công', 
                    'product' => $product
                ],
                200
            );    
        }
    }
    public function changeStatus($id)
    {
        $product = Product::find($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'product' => null
                ],
                404
            );    
        }
        $product->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $product->updated_by = 1;
        $product->status = ($product->status == 1) ? 2 : 1; //form
        if($product->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'product' => $product
                ],
                201
            );    
        }
        else
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Cập nhật dữ liệu không thành công', 
                    'product' => null
                ],
                422
            );
        }
    }
    public function store(Request $request)
    {
        $product = new Product();
        $product->category_id = $request->category_id; // form
        $product->brand_id = $request->brand_id; // form
        $product->name = $request->name; // form
        $product->slug = Str::of($request->name)->slug('-');
        $product->price = $request->price; // form
        $files1 = $request->image;
        if ($files1 != null) {
            $extension = $files1->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $product->image = $filename;
                $files1->move(public_path('images/product'), $filename);
            }
        }
        $product->detail = $request->detail; // form
        $product->metakey = $request->metakey; // form
        $product->metadesc = $request->metadesc; // form
        $product->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $product->created_by = 1;
        $product->status = $request->status; // form
    
        if ($product->save()) { // Save to the database
            // Save additional images if available
            $files2 = $request->images;
                for ($i = 0; $i < count($files2); $i++) {
                        $extension = $files2[$i]->getClientOriginalExtension();
                        if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                            $filename = date('YmdHis') . '_' . $i . '.' . $extension; // Ensure unique filenames
                            $files2[$i]->move(public_path('images/pro_image'), $filename);
                            DB::table('db_pro_image')->insert([
                                'image' => $filename,
                                'product_id' => $product->id,
                            ]);
                        }
            }
            // Process and save optionAttr
            if ($request->isVariant == 1) {
                $optionAttrs = $request->optionAttrs;
                if ($optionAttrs) {
                    foreach ($optionAttrs as $optionAttr) {
                        $values = $optionAttr['values'];
                        // Create a ProductAttribute object
                        $proAttribute = new ProductAttribute();
                        $proAttribute->product_id = $product->id;
                        $proAttribute->attribute_id = $optionAttr['attribute_id'];
                        if ($proAttribute->save()) {
                            foreach ($values as $value) {
                                DB::table('db_product_attribute_value')->insert([
                                    'product_attribute_id' => $proAttribute->id,
                                    'attribute_value_id' => $value['attribute_value_id'],
                                    // 'image' => $value['image'],
                                ]);
                            }
                        }
                    }
                }
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Thành công',
                    'product' => Product::find($product->id),
                ],
                201
            );
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thêm không thành công',
                    'product' => null
                ],
                422
            );
        }
    }
    function generateCombinations($arrays) {
        $result = [[]]; // Khởi tạo với một mảng rỗng
    
        foreach ($arrays as $property_values) {
            $temp = [];    
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $temp[] = array_merge($result_item, [$property_value]);
                }
            }
    
            $result = $temp; // Cập nhật lại kết quả
        }
        return $result;
    }
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'product' => null
                ],
                404
            );    
        }
        $product->category_id = $request->category_id; //form
        $product->brand_id = $request->brand_id; //form
        $product->name = $request->name; //form
        $product->slug = Str::of($request->name)->slug('-');
        $product->price = $request->price; //form
        $product->detail = $request->detail; //form
        $product->metakey = $request->metakey; //form
        $product->metadesc = $request->metadesc; //form
        $product->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $product->updated_by = 1;
        $product->status = $request->status; //form

        if($request->has('imageNews')){
            $imageNews = $request->imageNews;
            for ($i = 0; $i < count($request->imageNews); $i++) {
                $extension = $imageNews[$i]->getClientOriginalExtension();
                if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                    $filename = date('YmdHis') . '_' . $i . '.' . $extension; // Ensure unique filenames
                    $imageNews[$i]->move(public_path('images/pro_image'), $filename);
                    DB::table('db_pro_image')->insert([
                        'image' => $filename,
                        'product_id' => $product->id,
                    ]);
                }
            }
        }

        if ($request->has('imageDeletes')) {
            foreach ($request->imageDeletes as $imageId) {
                $image = DB::table('db_pro_image')->find($imageId);
                if ($image) {
                    $filePath = public_path('images/pro_image/' . $image->image);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    DB::table('db_pro_image')->where('id', $imageId)->delete();
                }
            }
        }

        if($product->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'product' => $product
                ],
                201
            );    
        }
        else
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Cập nhật dữ liệu không thành công', 
                    'product' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $product = Product::find($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'product' => null
                ],
                404
            );    
        }
        $product->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $product->updated_by = 1;
        $product->status = 0; 
        if($product->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'product' => $product
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $product = Product::find($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'product' => null
                ],
                404
            );    
        }
        $product->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $product->updated_by = 1;
        $product->status = 2; 
        if($product->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'product' => $product
                ],
                201
            );    
        }
    }
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if($product == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'product' => null
                ],
               404 
            );    
        }
        if($product->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'product' => $product
                ],
                200
            );    
        }
        else
        {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Xóa không thành công',
                    'product' => null
                ],
                422
            );    
        }
    }


}