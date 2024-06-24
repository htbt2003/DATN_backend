<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProAttributeController extends Controller
{
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
}
