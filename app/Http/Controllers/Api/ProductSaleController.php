<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductSale;
use App\Models\Product;

class ProductSaleController extends Controller
{
    public function index()
    {
        $prosales = ProductSale::select('id', 'product_id', 'qty', 'date_begin', 'date_end','price_sale')
            ->orderBy('created_at', 'DESC')
            ->paginate(5);
        $total = ProductSale::count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'prosales' => $prosales,
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
        $prosale->created_at = date('Y-m-d H:i:s');
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
        $prosale->updated_at = date('Y-m-d H:i:s');
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
    public function delete($id)
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
        $prosale->updated_at = date('Y-m-d H:i:s');
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
        $prosale->updated_at = date('Y-m-d H:i:s');
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

    public function destroy($id)
    {
        $prosale = ProductSale::findOrFail($id);
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
        if($prosale->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'prosale' => $prosale
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
                    'prosale' => null
                ],
                422
            );    
        }
    }
}
