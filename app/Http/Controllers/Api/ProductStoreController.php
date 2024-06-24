<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStore;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductStoreController extends Controller
{
    public function index()
    {
        $prostores = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty'), DB::raw('AVG(price_root) as avg_price'))
            ->groupBy('product_id',)
            // ->orderBy('created_at', 'DESC')
            ->paginate(5);        
        $total = $prostores->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'prostores' => $prostores,
                'total' => $total,
            ],
            200
        );
    }
    public function show($id)
    {
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
        else{
            return response()->json(
                [   
                    'status' => true, 
                    'message' => 'Tải dữ liệu thành công', 
                    'prostore' => $prostore
                ],
                200
            );    
        }
    }

    public function store(Request $request)
    {
        $prostore = new ProductStore();
        $prostore->product_id = $request->product_id;
        $prostore->price_root = $request->price_root; //form
        $prostore->qty = $request->qty; //form
        $prostore->created_at = date('Y-m-d H:i:s');
        $prostore->created_by = 1;
        if($prostore->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
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
                    'message' => 'Thêm không thành công', 
                    'prostore' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
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
        $prostore->pty = $request->pty; //form
        $prostore->price_root = $request->price_root; //form
        $prostore->updated_at = date('Y-m-d H:i:s');
        $prostore->updated_by = 1;
        $prostore->status = $request->status; //form
        if($prostore->save())//Luuu vao CSDL
        {
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
