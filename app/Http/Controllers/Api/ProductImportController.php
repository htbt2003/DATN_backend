<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductImportController extends Controller
{
    public function index()
    {
        $products = ProductImport::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'product_id', 'status')
            ->paginate(5);
        $total = ProductImport::where('status', '!=', 0)->count();
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
    public function show($id)
    {
        $product = ProductImport::find($id);
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

    public function store(Request $request)
    {
        $product = new ProductImport();
        $product->product_id = $request->product_id;
        $product->price_root = $request->price_root; //form
        $product->pty = $request->pty; //form
        $product->created_at = date('Y-m-d H:i:s');
        $product->created_by = 1;
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
        $product = ProductImport::find($id);
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
        $product->pty = $request->pty; //form
        $product->price_root = $request->price_root; //form
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
    
}
