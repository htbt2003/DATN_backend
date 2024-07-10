<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStore;
use App\Models\Product;
use App\Models\OrderDetail;
use Carbon\Carbon;

class PromotionController extends Controller
{
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Promotion::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có sản phẩm nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Promotion::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function index(Request $condition)
    {             
        $query = Promotion::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC');

            if ($condition->input('date_begin') != null) {
                $query->where('date_begin','<=', $condition->input('date_begin'));
            }
            if ($condition->input('date_end') != null) {
                $query->where('date_end','>=', $condition->input('date_end'));
            }
            if ($condition->input('keySearch') != null ) {
                $key = $condition->input('keySearch');
                $query->where(function ($query) use ($key) {
                    $query->where('name', 'like', '%' . $key . '%');
                });
            }

        $total = $query->count();
        $promotions = $query->paginate(5);
        $publish = Promotion::where('status', '=', 1)->count();
        $trash = Promotion::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'promotions' => $promotions,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function trash(Request $condition)
    {             
        $query = Promotion::where('status', '=', 0)
        ->orderBy('created_at', 'DESC');

            if ($condition->input('date_begin') != null) {
                $query->where('date_begin','<=', $condition->input('brandId'));
            }
            if ($condition->input('date_end') != null) {
                $query->where('date_end','<=', $condition->input('brandId'));
            }
            if ($condition->input('keySearch') != null ) {
                $key = $condition->input('keySearch');
                $query->where(function ($query) use ($key) {
                    $query->where('product.name', 'like', '%' . $key . '%');
                });
            }

        $total = Promotion::where('status', '!=', 0)->count();
        $promotions = $query->paginate(5);
        $publish = Promotion::where('status', '=', 1)->count();
        $trash = Promotion::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'promotions' => $promotions,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function show($id)
    {
        $promotion = Promotion::find($id);

        $productstore = ProductStore::where('status', '=', 1)
            ->select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
            ->groupBy('product_id');
        $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->groupBy('product_id');

        $productsale = ProductSale::where([['promotion_id','=', $id]])
        ->join('db_promotion', 'db_promotion.id', '=', 'db_productsale.promotion_id')
        ->select(
            'db_productsale.product_id',
            DB::raw('MIN(db_productsale.price_sale) as price_sale'),
            DB::raw('MIN(db_productsale.qty) as qty'),
            DB::raw('(SELECT SUM(od.qty) 
                    FROM db_orderdetail od 
                    LEFT JOIN db_order o ON od.order_id = o.id 
                    WHERE o.status NOT IN (5, 6, 7) 
                    AND od.product_id = db_productsale.product_id 
                    AND od.created_at >= db_promotion.date_begin 
                    AND od.created_at <= db_promotion.date_end
                    GROUP BY od.product_id) as sum_qty_sale_selled'),
        )
        ->groupBy('product_id','sum_qty_sale_selled');

        $products = Product::where('status', '=', 1)
            ->without('variants', 'images', 'productattributes')
            ->joinSub($productsale, 'productsale', function ($join) {
                $join->on('db_product.id', '=', 'productsale.product_id');
            })
            ->joinSub($productstore, 'productstore', function ($join) {
                $join->on('db_product.id', '=', 'productstore.product_id');
            })
            ->leftJoinSub($orderdetail, 'orderdetail', function ($join) {
                $join->on('db_product.id', '=', 'orderdetail.product_id');
            })
            ->select(
                'db_product.id',
                'db_product.name',
                'db_product.image',
                'db_product.price',
                'productsale.price_sale',
                'productsale.qty',
                'productsale.sum_qty_sale_selled',
                'productstore.sum_qty_store',
                'orderdetail.sum_qty_selled',
            )
            // ->orderBy('productstore.created_at', 'DESC')
            ->get();
            foreach ($products as $product) {
                $product->variants = $product->variants_promotion($id)->get();
            }
            
        return response()->json(
            [   
                'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'promotion' => $promotion,
                'productsales'=> $products,                
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $Listproducts = $request->Listproducts;

        $inforPromotion = $request->promotion;

        $promotion = new Promotion();
        $promotion->name = $inforPromotion['name']; 
        $promotion->date_begin = $inforPromotion['date_begin'];
        $promotion->date_end = $inforPromotion['date_end'];
        $promotion->created_at = date('Y-m-d H:i:s');
        $promotion->created_by = $inforPromotion['user_id'];
        if($promotion->save())//Luuu vao CSDL
        {
            foreach ($Listproducts as $item) {
            
                DB::table('db_productsale')->insert([
                    'promotion_id' => $promotion->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'price_sale' => $item['price_sale'],
                    'qty' => $item['qty'],
                    'created_at' => now(),
                    'created_by' => $inforPromotion['user_id'],
                ]);
            }
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'promotion' => $promotion
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
                    'promotion' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $inforPromotion = $request->promotion;
        $ListProductNew = $request->ListProductNew;
        $ListProductUpdate = $request->ListProductUpdate;

        $promotion = Promotion::find($id);
        if (!$promotion) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Không tìm thấy dữ liệu',
                    'promotion' => null
                ],
                404
            );
        }

        // Update promotion details
        $promotion->name = $inforPromotion['name'];
        $promotion->date_begin = $inforPromotion['date_begin'];
        $promotion->date_end = $inforPromotion['date_end'];
        $promotion->updated_at = now();
        $promotion->updated_by = $inforPromotion['user_id'];
        $promotion->save();

        // Insert new ProductSale entries
        foreach ($ListProductNew as $item) {
            DB::table('db_productsale')->insert([
                'promotion_id' => $promotion->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'price_sale' => $item['price_sale'],
                'qty' => $item['qty'],
                'created_at' => now(),
                'created_by' => $inforPromotion['user_id'],
            ]);
        }

        // Update or insert ProductSale entries in ListProductUpdate
        foreach ($ListProductUpdate as $item) {
            $promo = ProductSale::where('promotion_id', $promotion->id)
                                ->where('product_id', $item['product_id'])
                                ->where(function ($query) use ($item) {
                                    $query->whereNotNull('variant_id')
                                        ->where('variant_id', $item['variant_id']);
                                })
                                ->first();

            if ($promo) {
                // Update existing ProductSale record
                $promo->price_sale = $item['price_sale'];
                $promo->qty = $item['qty'];
                $promo->save();
            } else {
                // Insert new ProductSale record
                // DB::table('db_productsale')->insert([
                //     'promotion_id' => $promotion->id,
                //     'product_id' => $item['product_id'],
                //     'variant_id' => $item['variant_id'] ?? null,
                //     'price_sale' => $item['price_sale'],
                //     'qty' => $item['qty'],
                //     'created_at' => now(),
                //     'created_by' => $inforPromotion['user_id'],
                // ]);
            }
        }

        return response()->json(
            [
                'status' => true,
                'message' => 'Cập nhật dữ liệu thành công',
                'promotion' => $promotion,
            ],
            201
        );
    }

    public function delete($id)
    {
        $promotion = Promotion::find($id);
        if($promotion == null)//Luuu vao CSDL
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
        $promotion->updated_at = date('Y-m-d H:i:s');
        $promotion->updated_by = 1;
        $promotion->status = 0; 
        if($promotion->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'promotion' => $promotion
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $promotion = Promotion::find($id);
        if($promotion == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'promotion' => null
                ],
                404
            );    
        }
        $promotion->updated_at = date('Y-m-d H:i:s');
        $promotion->updated_by = 1;
        $promotion->status = 2; 
        if($promotion->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'promotion' => $promotion
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);
        if($promotion == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'promotion' => null
                ],
               404 
            );    
        }
        if($promotion->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'promotion' => $promotion
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
                    'promotion' => null
                ],
                422
            );    
        }
    }
}
