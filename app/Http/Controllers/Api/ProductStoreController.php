<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStore;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\OrderDetail;
use App\Models\ProductVariant;

use Illuminate\Support\Facades\DB;

class ProductStoreController extends Controller
{
    public function index(Request $condition)
    {
        $product = Product::select([
                'db_product.id', 
                'db_product.price', 
                'db_product.cost', 
                'db_product.name', 
                'db_product.image', 
                'db_product.category_id',
                'db_product.brand_id',
                'db_category.name as categoryname',
                'db_brand.name as brandname',
            ])
            ->leftJoin('db_category', 'db_product.category_id', '=', 'db_category.id')
            ->leftJoin('db_brand', 'db_product.brand_id', '=', 'db_brand.id');
             
        $query = ProductStore::where('status', '=', 1)
            ->leftJoinSub($product, 'product', function($join){
                $join->on('db_productstore.product_id', '=', 'product.id');
            })
            ->select([
                'db_productstore.product_id', 
                'db_productstore.variant_id', 
                // 'product.price', 
                'db_productstore.price_root', 
                // 'db_productstore.qty', 
                // 'product.cost', 
                'product.name', 
                'product.image', 
                // 'db_productstore.date_begin',
                // 'db_productstore.date_end',
                'product.categoryname',
                'product.brandname',
                'product.category_id',
                'product.brand_id',
                DB::raw('SUM(qty) as sum_qty_store')
            ])
            ->groupBy(
                'db_productstore.product_id', 
                'db_productstore.variant_id', 
                // 'product.price', 
                'db_productstore.price_root', 
                // 'db_productstore.qty', 
                // 'product.cost', 
                'product.name', 
                'product.image', 
                'product.categoryname',
                'product.brandname',
                'product.category_id',
                'product.brand_id'
            );
            // ->orderBy('db_productstore.created_at', 'DESC');

            if ($condition->input('brandId') != null) {
                $query->where('product.brand_id', $condition->input('brandId'));
            }
    
            if ($condition->input('catId') != null ) {
                
                $query->where('product.category_id', $condition->input('catId'));
            }
    
            if ($condition->input('keySearch') != null ) {
                $key = $condition->input('keySearch');
                $query->where(function ($query) use ($key) {
                    $query->where('product.name', 'like', '%' . $key . '%')
                        ->orWhere('product.categoryname', 'like', '%' . $key . '%')
                        ->orWhere('product.brandname', 'like', '%' . $key . '%');
                });
            }
        $total = $query->count();
        $prostore = $query->paginate(5);
        $categories = Category::where('status', '=', '1')->select('id', 'name')->get();
        $brands = Brand::where('status', '=', '1')->select('id', 'name')->get();

        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'prostores' => $prostore,
                'total' => $total,
                'categories' => $categories,
                'brands' => $brands,
            ],
            200
        );
    }

    public function show_history($product_id, $variant_id = null)
    {
        $query = ProductStore::where('product_id', '=', $product_id)->where('status', '=', 1);
    
        if ($variant_id !== null) {
            $query->where('variant_id', '=', $variant_id);
        }
    
        $prostores = $query->get();
    
        if ($prostores->isEmpty()) {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prostore' => null
                ],
                404
            );
        } else {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Tải dữ liệu thành công', 
                    'prostores' => $prostores
                ],
                200
            );
        }
    }
    public function show($id)
    {
        $prostores = ProductStore::where('id', '=', $id)
        ->join('db_product', 'db_productstore.product_id', 'db_product.id')
        ->select([
            'db_product.id', 
            'db_product.price', 
            'db_product.cost', 
            'db_product.name', 
            'db_product.image', 
        ])->get();
        
        if ($prostores->isEmpty()) {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prostore' => null
                ],
                404
            );
        } else {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Tải dữ liệu thành công', 
                    'prostores' => $prostores
                ],
                200
            );
        }
    }
    // public function store(Request $request)
    // {
    //     // // Kiểm tra quyền admin
    //     // $user = Auth::user();
    //     // if (!$user->isAdmin()) {
    //     //     return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này'], 403);
    //     // }
        

    //     $prostore = new ProductStore();
    //     $prostore->product_id = $request->product_id;
    //     $prostore->variant_id = $request->variant_id; //form
    //     $prostore->price_root = $request->price_root; //form
    //     $prostore->qty = $request->qty; //form
    //     $prostore->created_at = date('Y-m-d H:i:s');
    //     $prostore->created_by = 1;
    //     if($prostore->save())//Luuu vao CSDL
    //     {
    //         if ($request->variant_id) {
    //             $product = ProductVariant::select('cost')
    //                 ->where('db_product_variant.variant_id', '=', $request->variant_id)
    //                 ->first();
    //             $productstore->where('variant_id', $variantId)
    //                 ->groupBy('product_id', 'variant_id')->first();
    //             $orderdetail->where('variant_id', $variantId)
    //                 ->groupBy('product_id', 'variant_id')->first();

    //             $cost = $product->cost;
    //             $qty_inventory = $productstore->sum_qty_store - $orderdetail->sum_qty_selled;
            
    //             $newCost = (($cost * $qty_inventory) + ($request->price_root * $request->qty)) / ($qty_inventory + $request->qty);
    //             ProductVariant::where('id', $request->variant_id)->update(['cost' => $newcost]);
        
    //         } else {
    //             $product = Product::select('cost')
    //             ->where('db_product.id', '=', $request->product_id)
    //             ->first();
    //             $productstore->groupBy('product_id')->first();
    //             $orderdetail->groupBy('product_id')->first();
                
    //             $cost = $product->cost;
    //             $qty_inventory = $productstore->sum_qty_store - $orderdetail->sum_qty_selled;
        
    //             $newCost = (($cost * $qty_inventory) + ($request->price_root * $request->qty)) / ($qty_inventory + $request->qty);
    //             Product::where('id', $request->product_id)->update(['cost' => $newcost]);

    //         }
        
    //         return response()->json(
    //             [
    //                 'status' => true, 
    //                 'message' => 'Thành công', 
    //                 'prostore' => $prostore
    //             ],
    //             201
    //         );    
    //     }
    //     else
    //     {
    //         return response()->json(
    //             [
    //                 'status' => false, 
    //                 'message' => 'Thêm không thành công', 
    //                 'prostore' => null
    //             ],
    //             422
    //         );
    //     }
    // }

    public function update(Request $request, $id)
    {
        // // Kiểm tra quyền admin
        // $user = Auth::user();
        // if (!$user->isAdmin()) {
        //     return response()->json(['message' => 'Bạn không có quyền thực hiện hành động này'], 403);
        // }
        $prostore = ProductStore::find($id);
        if($prostore == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prostore' => null
                ],
                404
            );    
        }
        //lấy giá, sô lượng cũ
        $price_root_old = $prostore->price_root;
        $qty_old = $prostore->qty;
    
        //Cập nhật
        $prostore->qty = $request->qty; //form
        $prostore->price_root = $request->price_root; //form
        $prostore->updated_at = date('Y-m-d H:i:s');
        $prostore->updated_by = $request->user_id;

        if($prostore->save())//Luuu vao CSDL
        {
            if ($prostore->variant_id != null) {
                $product = ProductVariant::select('cost')
                    ->where('id', '=', $prostore->variant_id)
                    ->first();

                $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                    ->where([
                        ['status', '=', 1],
                        ['product_id', '=', $prostore->product_id],
                        ['variant_id', $prostore->variant_id],
                    ])
                    ->groupBy('product_id', 'variant_id')
                    ->first();

                $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                    ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                    ->whereNotIn('db_order.status', [0, 5, 6])
                    ->where('db_orderdetail.product_id', '=', $prostore->product_id)
                    ->where('db_orderdetail.variant_id', $prostore->variant_id)
                    ->groupBy('product_id', 'variant_id')
                    ->first();

                $cost = $product->cost ?? 0;
                $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);
                //tính lại trung bình giá gốc trên một sản phẩm = tổng giá trị đúng / số lượng kho đúng => đây là giá gốc hiện tại
                $rightCost = (($cost * $qty_inventory) - ($price_root_old * $qty_old)) / ($qty_inventory - $qty_old);
                //tính trung bình giá gốc mới 
                $newCost = (($rightCost * ($qty_inventory - $qty_old)) + ($request->price_root * $request->qty)) / (($qty_inventory - $qty_old) + $request->qty);
                //cập nhật cost
                ProductVariant::where('id', $prostore->variant_id)->update(['cost' => $newCost]);
            } else {
                $product = Product::select('cost')
                    ->where('db_product.id', '=', $prostore->product_id)
                    ->first();

                $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                    ->where([
                        ['status', '=', 1],
                        ['product_id', '=', $prostore->product_id],
                    ])
                    ->groupBy('product_id')
                    ->first();

                $orderdetail = OrderDetail::select('db_orderdetail.product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                    ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                    ->whereNotIn('db_order.status', [0, 5, 6])
                    ->where('db_orderdetail.product_id', '=', $prostore->product_id)
                    ->groupBy('db_orderdetail.product_id')
                    ->first();

                $cost = $product->cost ?? 0;
                $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);

                //tính lại trung bình giá gốc trên một sản phẩm = tổng giá trị đúng / số lượng kho đúng => đây là giá gốc hiện tại
                $rightCost = (($cost * $qty_inventory) - ($price_root_old * $qty_old)) / ($qty_inventory - $qty_old);
                //tính trung bình giá gốc mới 
                $newCost = (($rightCost * ($qty_inventory - $qty_old)) + ($request->price_root * $request->qty)) / (($qty_inventory - $qty_old) + $request->qty);
                //cập nhật cost
                Product::where('id', $prostore->product_id)->update(['cost' => $newCost]);
            }
    
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'prostore' => $prostore
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
                    'prostore' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $prostore = ProductStore::find($id);
        if($prostore == null)//Luuu vao CSDL
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
        //lấy giá, sô lượng cũ
        $price_root_old = $prostore->price_root;
        $qty_old = $prostore->qty;
        
        //delete
        $prostore->updated_at = date('Y-m-d H:i:s');
        $prostore->updated_by = 1;
        $prostore->status = 0; 
        if($prostore->save())//Luuu vao CSDL
        {
            if ($prostore->variant_id != null) {
                $product = ProductVariant::select('cost')
                    ->where('id', '=', $prostore->variant_id)
                    ->first();

                $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                    ->where([
                        ['status', '=', 1],
                        ['product_id', '=', $prostore->product_id],
                        ['variant_id', $prostore->variant_id],
                    ])
                    ->groupBy('product_id', 'variant_id')
                    ->first();

                $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                    ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                    ->whereNotIn('db_order.status', [0, 5, 6])
                    ->where('db_orderdetail.product_id', '=', $prostore->product_id)
                    ->where('db_orderdetail.variant_id', $prostore->variant_id)
                    ->groupBy('product_id', 'variant_id')
                    ->first();

                $cost = $product->cost ?? 0;
                $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);
                //tính lại trung bình giá gốc trên một sản phẩm = tổng giá trị đúng / số lượng kho đúng => đây là giá gốc hiện tại
                $rightCost = (($cost * $qty_inventory) - ($price_root_old * $qty_old)) / ($qty_inventory - $qty_old);
                ProductVariant::where('id', $prostore->variant_id)->update(['cost' => $rightCost]);
            } else {
                $product = Product::select('cost')
                    ->where('db_product.id', '=', $prostore->product_id)
                    ->first();

                $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                    ->where([
                        ['status', '=', 1],
                        ['product_id', '=', $prostore->product_id],
                    ])
                    ->groupBy('product_id')
                    ->first();

                $orderdetail = OrderDetail::select('db_orderdetail.product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                    ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                    ->whereNotIn('db_order.status', [0, 5, 6])
                    ->where('db_orderdetail.product_id', '=', $prostore->product_id)
                    ->groupBy('db_orderdetail.product_id')
                    ->first();

                $cost = $product->cost ?? 0;
                $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);

                //tính lại trung bình giá gốc trên một sản phẩm = tổng giá trị đúng / số lượng kho đúng => đây là giá gốc hiện tại
                $rightCost = (($cost * $qty_inventory) - ($price_root_old * $qty_old)) / ($qty_inventory - $qty_old);
                //cập nhật cost
                Product::where('id', $prostore->product_id)->update(['cost' => $rightCost]);
            }

            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'prostore' => $prostore
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $prostore = ProductStore::findOrFail($id);
        if($prostore == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prostore' => null
                ],
                404 
            );    
        }
        if($prostore->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'prostore' => $prostore
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
                    'prostore' => null
                ],
                422
            );    
        }    
    }   
}
