<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductStore;
use App\Models\Product;
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
             
        $query = ProductStore::joinSub($product, 'product', function($join){
                $join->on('db_productstore.id', '=', 'product.id');
            })
            ->select([
                'db_productstore.id', 
                'db_productstore.variant_id', 
                'product.price', 
                'db_productstore.price_root', 
                'db_productstore.qty', 
                'product.cost', 
                'product.name', 
                'product.image', 
                'db_productstore.date_begin',
                'db_productstore.date_end',
                'product.categoryname',
                'product.brandname',
                'product.category_id',
                'product.brand_id',
            ])
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
            ->orderBy('db_productstore.created_at', 'DESC')
            ->paginate(5);
        $total = ProductStore::count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'prostore' => $query,
                'total' => $total
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
            $product = Product::where('id', $request->product_id);
            Product::where('id', $request->product_id)->update(['cost' => 0]);
    
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
