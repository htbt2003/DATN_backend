<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttributeValue;

class AttributeValueController extends Controller
{
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = AttributeValue::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = AttributeValue::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = AttributeValue::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'status', 'image' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_brand.name', 'like', '%' . $key . '%');
            });
        }
        $total = AttributeValue::where('status', '!=', 0)->count();
        $brands = $query->paginate(5);
        $total = $brands->total();
        $trash = AttributeValue::where('status', '=', 0)->count();
        $publish = AttributeValue::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'brands' => $brands,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = AttributeValue::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'slug', 'status', 'image' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_brand.name', 'like', '%' . $key . '%');
            });
        }
        $brandsAll = $query->get(); 
        $total = $query->count();
        $brands = $query->paginate(5);
        $total = $brands->total();
        $trash = AttributeValue::where('status', '=', 0)->count();
        $publish = AttributeValue::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'brands' => $brands,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
            'brandsAll' => $brandsAll,
        ];
        return response()->json($result,200);

    }
    public function show($id)
    {
        if(is_numeric($id)){
            $brand = AttributeValue::find($id);        }
        else{
            $brand = AttributeValue::where('slug', $id)->first();
        }
        
        return response()->json(
            [   'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'brand' => $brand
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $brand = new Brand();
        $brand->name = $request->name; //form
        $brand->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $brand->image = $filename;
                $files->move(public_path('images/brand'), $filename);
            }
        }
        //
        $brand->sort_order = $request->sort_order; //form
        $brand->metakey = $request->metakey; //form
        $brand->metadesc = $request->metadesc; //form
        $brand->created_at = date('Y-m-d H:i:s');
        $brand->created_by = 1;
        $brand->status = $request->status; //form
        if($brand->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'brand' => $brand
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
                    'brand' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $brand = AttributeValue::find($id);
        if($brand == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'brand' => null
                ],
                404
            );    
        }
        $brand->name = $request->name; //form
        $brand->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $brand->image = $filename;
                $files->move(public_path('images/brand'), $filename);
            }
        }
        //
        $brand->sort_order = $request->sort_order; //form
        $brand->metakey = $request->metakey; //form
        $brand->metadesc = $request->metadesc; //form
        $brand->updated_at = date('Y-m-d H:i:s');
        $brand->updated_by = 1;
        $brand->status = $request->status; //form
        if($brand->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'brand' => $brand
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
                    'brand' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $brand = AttributeValue::find($id);
        if($brand == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'brand' => null
                ],
                404
            );    
        }
        $brand->updated_at = date('Y-m-d H:i:s');
        $brand->updated_by = 1;
        $brand->status = 0; 
        if($brand->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'brand' => $brand
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $brand = AttributeValue::find($id);
        if($brand == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'brand' => null
                ],
                404
            );    
        }
        $brand->updated_at = date('Y-m-d H:i:s');
        $brand->updated_by = 1;
        $brand->status = 2; 
        if($brand->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'brand' => $brand
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $brand = AttributeValue::findOrFail($id);
        if($brand == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'brand' => null
                ],
               404 
            );    
        }
        if($brand->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'brand' => $brand
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
                    'brand' => null
                ],
                422
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
}
