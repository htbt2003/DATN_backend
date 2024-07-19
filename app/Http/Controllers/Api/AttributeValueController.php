<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttributeValue;
use Carbon\Carbon;

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
            ->where('attribute_id', '=', $condition['attributeId'])
            // ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'status', 'image' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_attributeValue.name', 'like', '%' . $key . '%');
            });
        }
        $total = AttributeValue::where('status', '!=', 0)->count();
        $attributeValues = $query->paginate(5);
        $total = $attributeValues->total();
        $trash = AttributeValue::where('status', '=', 0)->count();
        $publish = AttributeValue::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'attributeValues' => $attributeValues,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = AttributeValue::where('status', '!=', 0)
        ->where('attribute_id', '=', $condition['attributeId'])
        // ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'status' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_attributeValue.name', 'like', '%' . $key . '%');
            });
        }
        $attributeValuesAll = $query->get(); 
        $total = $query->count();
        $attributeValues = $query->paginate(5);
        $total = $attributeValues->total();
        $trash = AttributeValue::where('status', '=', 0)->count();
        $publish = AttributeValue::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'attributeValues' => $attributeValues,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
            'attributeValuesAll' => $attributeValuesAll,
        ];
        return response()->json($result,200);

    }
    public function show($id)
    {
        if(is_numeric($id)){
            $attributeValue = AttributeValue::find($id);        }
        else{
            $attributeValue = AttributeValue::where('slug', $id)->first();
        }
        
        return response()->json(
            [   'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'attributeValue' => $attributeValue
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $attributeValue = new attributeValue();
        $attributeValue->name = $request->name; //form
        $attributeValue->attribute_id = $request->attribute_id;
        // $attributeValue->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        // $attributeValue->created_by = 1;
        if($attributeValue->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'attributeValue' => $attributeValue
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
                    'attributeValue' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $attributeValue = AttributeValue::find($id);
        if($attributeValue == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attributeValue' => null
                ],
                404
            );    
        }
        $attributeValue->name = $request->name; //form
        $attributeValue->attribute_id = $request->attribute_id;
        // $attributeValue->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        // $attributeValue->updated_by = 1;
        if($attributeValue->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'attributeValue' => $attributeValue
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
                    'attributeValue' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $attributeValue = AttributeValue::find($id);
        if($attributeValue == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'attributeValue' => null
                ],
                404
            );    
        }
        $attributeValue->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attributeValue->updated_by = 1;
        $attributeValue->status = 0; 
        if($attributeValue->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'attributeValue' => $attributeValue
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $attributeValue = AttributeValue::find($id);
        if($attributeValue == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attributeValue' => null
                ],
                404
            );    
        }
        $attributeValue->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attributeValue->updated_by = 1;
        $attributeValue->status = 2; 
        if($attributeValue->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'attributeValue' => $attributeValue
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $attributeValue = AttributeValue::findOrFail($id);
        if($attributeValue == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attributeValue' => null
                ],
               404 
            );    
        }
        if($attributeValue->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'attributeValue' => $attributeValue
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
                    'attributeValue' => null
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
        $product->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
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
