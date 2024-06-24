<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductStore;
use App\Models\Category;
use App\Models\OrderDetail;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    function product_new($limit)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $products = Product::where('status', '=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->orderBy('db_product.created_at', 'DESC')
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price', 'db_product.slug', 'db_productsale.price_sale')
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
    function product_sale($limit)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $products = Product::where([
                ['db_product.status', '=', 1],
                ['db_productsale.date_begin', '<=', Carbon::now()],
                ['db_productsale.date_end', '>=', Carbon::now()]
            ])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->join('db_productsale', 'db_product.id', '=', 'db_productsale.product_id')
            ->orderBy('db_product.created_at', 'DESC')
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price', 'db_product.slug', 'db_productsale.price_sale', 'db_productsale.date_begin', 'db_productsale.date_end')
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
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
            ->groupBy('product_id');
        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty'))
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
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->orderBy('orderdetail.sum_qty', 'DESC')
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_productsale.price_sale')
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
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $products = Product::where('db_product.status', '=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('db_product.category_id', $listid)
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->orderBy('db_product.created_at', 'DESC')
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_productsale.price_sale')
            -> limit($limit)
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

    public function products()
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $products = Product::where('db_product.status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->join('db_category', 'db_product.category_id','=','db_category.id')
            ->join('db_brand', 'db_product.brand_id','=','db_brand.id')
            ->orderBy('db_product.created_at', 'DESC')
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_category.name as category_name', 'db_brand.name as brand_name')
            ->paginate(8);
        $total = $products->total();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
            ],
            200
        );
    }
    public function product_allAction(Request $condition)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
            ->groupBy('product_id');
        $query = Product::where('db_product.status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_product.created_at', 'db_productsale.price_sale');

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
            $query->orderBy('created_at', 'DESC');
        }
        $products = $query->paginate(8);
        $total = $products->total();
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
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $query = Product::where('status','=', 1)
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('category_id', $listid)
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_product.created_at', 'db_product.created_at', 'db_productsale.price_sale');
            
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
        $products = $query->paginate(8);
        $total = $products->total();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
            ],
            200
        );

    }

    public function product_brand($brand_id, Request $condition)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $query = Product::where([['brand_id', '=', $brand_id], ['status', '=', 1]])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_product.created_at', 'db_productsale.price_sale');
            
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
        $products = $query->paginate(8);
        $total = $products->total();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'products' => $products,
                'total' => $total,
            ],
            200
        );
    }
    function product_order($id, $limit)
    {
        $args = [
            ['id', '=', $id],
            ['status', '=', 1]
        ];
        $products = Product::where($args)
            ->orderBy('created_at', 'DESC')
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
    public function product_detail($slug)
    {
        $args = [
            ['slug', '=', $slug],
            ['status', '=', 1]
        ];
        $product = Product::where($args)
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug', 'db_productsale.price_sale')
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
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
        ->groupBy('product_id');
        $product_other = Product::where([['db_product.id', '!=', $product->id],['status', '=', 1]])
            ->joinSub($productstore, 'productstore', function($join){
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->whereIn('category_id', $listid)
            ->leftJoin('db_productsale', function ($join) {
                $join->on('db_product.id', '=', 'db_productsale.product_id')
                    ->where('db_productsale.date_begin', '<=', Carbon::now())
                    ->where('db_productsale.date_end', '>=', Carbon::now());
            })
            ->select('db_product.id','db_product.name', 'db_product.image', 'db_product.price','db_product.slug')
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
            $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'))
            ->groupBy('product_id');
            $products = Product::where('status', '=', 1)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('metadesc', 'LIKE', "%$search%");
                })
                ->orWhereHas('category', function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%");
                })
                ->orWhereHas('brand', function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%");
                })
                ->joinSub($productstore, 'productstore', function ($join) {
                    $join->on('db_product.id', '=', 'productstore.product_id');
                })
                ->leftJoin('db_productsale', function ($join) {
                    $join->on('db_product.id', '=', 'db_productsale.product_id')
                        ->where('db_productsale.date_begin', '<=', Carbon::now())
                        ->where('db_productsale.date_end', '>=', Carbon::now());
                })
                ->select('db_product.id', 'db_product.name', 'db_product.image', 'db_product.price', 'db_product.slug', 'db_productsale.price_sale')
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
            // $protoltal = $products->total(); 
            // $posttoltal = $posts->total(); 
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Tải dữ liệu thành công',
                    'products' => $products,
                    'posts' => $posts,
                    // 'protoltal' => $protoltal,
                    // 'posttoltal' => $posttoltal,
                    'search' => $search,

                ],
                200
            );   
    }
    public function trash()
    {
        $products = Product::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'category_id', 'brand_id', 'image', 'status')
            ->paginate(5);
        $total = Product::where('status', '!=', 0)->count();
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

    public function index(Request $condition)
    {
        $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');
        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
        ->groupBy('product_id');
        $query = Product::where('db_product.status','!=', 0)
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
        $products = $query->orderBy('db_product.created_at', 'DESC')->paginate(8);
        $total = $products->total();
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();
        $trash = Product::where('status', '=', 0)->count();
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
        $product->updated_at = date('Y-m-d H:i:s');
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
        $product->category_id = $request->category_id; //form
        $product->brand_id = $request->brand_id; //form
        $product->name = $request->name; //form
        $product->slug = Str::of($request->name)->slug('-');
        $product->price = $request->price; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $product->image = $filename;
                $files->move(public_path('images/product'), $filename);
            }
        }
        //
        $product->detail = $request->detail; //form
        $product->metakey = $request->metakey; //form
        $product->metadesc = $request->metadesc; //form
        $product->created_at = date('Y-m-d H:i:s');
        $product->created_by = 1;
        $product->status = $request->status; //form
        if($product->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
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
                    'message' => 'Thêm không thành công', 
                    'product' => null
                ],
                422
            );
        }
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
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $product->image = $filename;
                $files->move(public_path('images/product'), $filename);
            }
        }
        //
        $product->detail = $request->detail; //form
        $product->metakey = $request->metakey; //form
        $product->metadesc = $request->metadesc; //form
        $product->updated_at = date('Y-m-d H:i:s');
        $product->updated_by = 1;
        $product->status = $request->status; //form
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
        $product->updated_at = date('Y-m-d H:i:s');
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
        $product->updated_at = date('Y-m-d H:i:s');
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
