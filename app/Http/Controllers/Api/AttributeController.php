<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute;
use Carbon\Carbon;

class AttributeController extends Controller
{
    public function store_attribute(Request $request)
    {
        try {
            $optionAttrs = $request->optionAttr;
            if ($optionAttrs) {
                foreach ($optionAttrs as $optionAttr) {
                    $attribute = $optionAttr['attribute'];
                    $values = $optionAttr['value'];
                    // Tạo đối tượng ProductAttribute
                    $proAttribute = new ProductAttribute();
                    $proAttribute->product_id = $request->product_id;
                    $proAttribute->attribute_id = $attribute['id'];
                    if ($proAttribute->save()) { // Lưu vào CSDL thành công
                        foreach ($values as $value) {
                            DB::table('db_product_attribute_value')->insert([
                                'product_attribute_id' => $proAttribute->id,
                                'attribute_value_id' => $value['attribute_value_id'],
                                'image' => json_encode($value['image']) // Giả sử bạn muốn lưu JSON cho hình ảnh
                            ]);
                        }
                    }
                }
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Thêm thành công'
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Không có dữ liệu optionAttr'
                    ],
                    422
                );
            }
        } catch (\Exception $e) {
            // Ghi lại lỗi hoặc xử lý khi cần
            error_log('Lỗi xử lý: ' . $e->getMessage());
            // Thêm phần xử lý lỗi chi tiết ở đây nếu cần
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Lỗi hệ thống: ' . $e->getMessage()
                ],
                500
            );
        }
    }

    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Attribute::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Attribute::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = Attribute::where('status','=', 0)
        ->orderBy('created_at', 'DESC')
            ->select('id', 'name' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_attribute.name', 'like', '%' . $key . '%');
            });
        }
        $total = Attribute::where('status','!=', 0)->count();
        $attributes = $query->paginate(5);
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'attributes' => $attributes,
            'total' => $total,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = Attribute::where('status','!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_attribute.name', 'like', '%' . $key . '%');
            });
        }
        $attributesAll = $query->get(); 
        $total = $query->count();
        $attributes = $query->paginate(5);
        $trash = Attribute::where('status', '=', 0)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'attributes' => $attributes,
            'total' => $total,
            'attributesAll' => $attributesAll,
            'trash' => $trash,
        ];
        return response()->json($result,200);

    }
    public function show($id)
    {
        if(is_numeric($id)){
            $attribute = Attribute::find($id);        }
        else{
            $attribute = Attribute::where('slug', $id)->first();
        }
        
        return response()->json(
            [   'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'attribute' => $attribute
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $attribute = new attribute();
        $attribute->name = $request->name; //form
        $attribute->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attribute->created_by = 1;
        if($attribute->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'attribute' => $attribute
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
                    'attribute' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $attribute = Attribute::find($id);
        if($attribute == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attribute' => null
                ],
                404
            );    
        }
        $attribute->name = $request->name; //form
        $attribute->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attribute->updated_by = 1;
        if($attribute->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'attribute' => $attribute
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
                    'attribute' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $attribute = Attribute::find($id);
        if($attribute == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'attribute' => null
                ],
                404
            );    
        }
        $attribute->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attribute->updated_by = 1;
        $attribute->status = 0; 
        if($attribute->save())
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'attribute' => $attribute
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $attribute = Attribute::find($id);
        if($attribute == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attribute' => null
                ],
                404
            );    
        }
        $attribute->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $attribute->updated_by = 1;
        $attribute->status = 2; 
        if($attribute->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'attribute' => $attribute
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        if($attribute == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'attribute' => null
                ],
               404 
            );    
        }
        if($attribute->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'attribute' => $attribute
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
                    'attribute' => null
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
