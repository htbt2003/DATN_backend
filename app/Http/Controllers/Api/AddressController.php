<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Address::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Address::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function index(Request $condition)
    {
        
        $query = Address::where('status', '!=', 0)
        ->select('id', 'name', 'phone', 'address', 'status' )
        ->orderBy('db_address.created_at', 'DESC');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_address.name', 'like', '%' . $key . '%');
            });
        }
        $total = $query->count();
        $addresses = $query->paginate(8);
        $total = $addresses->total();
        $trash = Address::where('status', '=', 0)->count();
        $publish = Address::where('status', '=', 1)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'addresses' => $addresses,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $address = Address::find($id);
        if($address == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'address' => null
                ],
                404
            );    
        }
        $address->updated_at = date('Y-m-d H:i:s');
        $address->updated_by = 1;
        $address->status = ($address->status == 1) ? 2 : 1; //form
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'address' => $address
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
                    'address' => null
                ],
                422
            );
        }
    }

    public function show($id)
    {
        $address = Address::find($id);
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'address' => $address],
            200
        );
    }
    public function store(Request $request)
    {
        $address = new address();
        $address->name = $request->name; //form
        $address->description = $request->description; //form
        // $address->link = $request->link; //form
        $address->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $address->image = $filename;
                $files->move(public_path('images/address'), $filename);
            }
        }
        $address->created_at = date('Y-m-d H:i:s');
        $address->created_by = 1;
        $address->status = $request->status; //form
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thêm thành công', 
                    'address' => $address
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
                    'address' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $address = Address::find($id);
        if($address == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'address' => null
                ],
                404
            );    
        }
        $address->name = $request->name; //form
        $address->description = $request->description; //form
        // $address->link = $request->link; //form
        $address->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $address->image = $filename;
                $files->move(public_path('images/address'), $filename);
            }
        }
        $address->updated_at = date('Y-m-d H:i:s');
        $address->updated_by = 1;
        $address->status = $request->status; //form
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'address' => $address
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
                    'address' => null
                ],
                422
            );
        }
    }
    public function trash(Request $condition)
    {
        $query = Address::where('status', '!=', 0)
        ->select('id', 'name', 'phone', 'address', 'status' )
        ->orderBy('db_address.created_at', 'DESC');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_address.name', 'like', '%' . $key . '%');
            });
        }
        $total = $query->count();
        $addresses = $query->paginate(8);
        $total = $addresses->total();
        $trash = Address::where('status', '=', 0)->count();
        $publish = Address::where('status', '=', 1)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'addresses' => $addresses,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function delete($id)
    {
        $address = Address::find($id);
        if($address == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'address' => null
                ],
                404
            );    
        }
        $address->updated_at = date('Y-m-d H:i:s');
        $address->updated_by = 1;
        $address->status = 0; 
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'address' => $address
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $address =Address::find($id);
        if($address == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'address' => null
                ],
                404
            );    
        }
        $address->updated_at = date('Y-m-d H:i:s');
        $address->updated_by = 1;
        $address->status = 2; 
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'address' => $address
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $address =Address::findOrFail($id);
        if($address == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'address' => null
                ],
               404 
            );    
        }
        if($address->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'address' => $address
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
                    'address' => null
                ],
                422
            );    
        }
    }

}
