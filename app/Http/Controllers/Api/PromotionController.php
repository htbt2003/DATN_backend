<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;

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
                $query->where('date_begin','<=', $condition->input('brandId'));
            }
            if ($condition->input('date_end') != null) {
                $query->where('date_end','<=', $condition->input('brandId'));
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

        $productsale = ProductSale::where([['promotion_id','=', $id]])
        ->join('db_product as p', 'db_productsale.product_id', '=', 'p.id')
        ->select(
            'db_productsale.id',
            'db_productsale.price_root',
            'db_productsale.qty',
            'db_productsale.created_at',
            'db_productsale.variant_id',
            'p.name',
            'p.image', 
            'p.price as price_sell')
        ->get();

        return response()->json(
            [   
                'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'promotion' => $promotion,
                'productsale'=> $productsale,
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
                    'created_by' => $request['user_id'],
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
        $promotion->name = $request->name; 
        $promotion->date_begin = $request->date_begin;
        $promotion->date_end = $request->date_end;
        $promotion->updated_at = date('Y-m-d H:i:s');
        $promotion->updated_by =$request->user_id;
        $promotion->status = $request->status; //form
        if($promotion->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
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
                    'message' => 'Cập nhật dữ liệu không thành công', 
                    'promotion' => null
                ],
                422
            );
        }
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
