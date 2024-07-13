<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Product;
use Carbon\Carbon;

class ProductSaleController extends Controller
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
             
        $query = ProductSale::joinSub($product, 'product', function($join){
                $join->on('db_productsale.id', '=', 'product.id');
            })
            ->select([
                'db_productsale.id', 
                'db_productsale.variant_id', 
                'product.price', 
                'db_productsale.price_sale', 
                'db_productsale.qty', 
                'product.cost', 
                'product.name', 
                'product.image', 
                'db_productsale.date_begin',
                'db_productsale.date_end',
                'product.categoryname',
                'product.brandname',
                'product.category_id',
                'product.brand_id',
            ])
            ->orderBy('db_productsale.created_at', 'DESC')
            ->paginate(5);

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
            };
        $total = ProductSale::count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'prosales' => $query,
                'total' => $total
            ],
            200
        );
    }
    public function show($id)
    {
        $prosale = ProductSale::find($id);
        return response()->json(
            [   
                'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'prosale' => $prosale
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $prosale = new ProductSale();
        $prosale->product_id = $request->product_id; 
        $prosale->price_sale = $request->price_sale; 
        $prosale->qty = $request->qty; 
        $prosale->date_begin = $request->date_begin;
        $prosale->date_end = $request->date_end;
        $prosale->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $prosale->created_by = 1;
        if($prosale->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'prosale' => $prosale
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
                    'prosale' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $prosale = ProductSale::find($id);
        if($prosale == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prosale' => null
                ],
                404
            );    
        }
        $prosale->product_id = $request->product_id; 
        $prosale->price_sale = $request->price_sale; 
        $prosale->qty = $request->qty; 
        $prosale->date_begin = $request->date_begin;
        $prosale->date_end = $request->date_end;
        $prosale->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $prosale->updated_by = 1;
        $prosale->status = $request->status; //form
        if($prosale->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'prosale' => $prosale
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
                    'prosale' => null
                ],
                422
            );
        }
    }
    public function delete($product_id, $promotion_id)
    {
        $prosale = ProductSale::find($id);
        if($prosale == null)//Luuu vao CSDL
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
        $prosale->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $prosale->updated_by = 1;
        $prosale->status = 0; 
        if($prosale->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'prosale' => $prosale
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $prosale = ProductSale::find($id);
        if($prosale == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'prosale' => null
                ],
                404
            );    
        }
        $prosale->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $prosale->updated_by = 1;
        $prosale->status = 2; 
        if($prosale->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'prosale' => $prosale
                ],
                201
            );    
        }
    }

    public function destroy($product_id, $promotion_id)
    {
        $deleted = ProductSale::where('product_id', $product_id)
                              ->where('promotion_id', $promotion_id)
                              ->delete();
    
        if ($deleted === 0) {
            return response()->json([
                'status' => false, 
                'message' => 'Không tìm thấy dữ liệu để xóa', 
                'prosale' => null
            ], 404);    
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công',
            'count' => $deleted
        ], 200);
    }
}
