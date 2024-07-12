<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Carbon\Carbon;

class AddressController extends Controller
{
    public function default_address_userId($id)
    {
        $address = Address::where([['user_id','=', $id], ['status','=', 1]])->first();
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        if($address)//Luuu vao CSDL
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

    public function address_userId($id)
    {
        $addresses = Address::where('user_id','=', $id)->get();
        if (!$addresses) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        if($addresses)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thêm thành công', 
                    'addresses' => $addresses
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
                    'addresses' => null
                ],
                422
            );
        }

    }

    public function updateDefaultAddress($id)
    {
        $address = Address::find($id);
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        // Cập nhật địa chỉ mặc định
        Address::where('status', 1)->update(['status' => 0]);
        $address->status = 1;
        $address->save();

        return response()->json(['message' => 'Default address updated successfully']);
    }
    public function store(Request $request)
    {
        $address = new address();
        $address->name = $request->name; //form
        $address->phone = $request->phone; //form
        $address->address = $request->address; //form
        $address->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $address->user_id = $request->user_id;
        $address->status = $request->status;
         // Check if no default address exists
        if ($request->status==1) {
            Address::where('status', 1)->update(['status' => 0]);
        }
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
        $address->phone = $request->phone; //form
        $address->address = $request->address; //form
        $address->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $address->user_id = $request->user_id;
        $address->status = $request->status;
         // Check if no default address exists
        if ($request->status == 1) {
            Address::where('status', 1)->update(['status' => 0]);
        }
        if($address->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật thành công', 
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
        $address->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
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
    // public function store(Request $request)
    // {
    //     $address = new address();
    //     $address->name = $request->name; //form
    //     $address->description = $request->description; //form
    //     // $address->link = $request->link; //form
    //     $address->position = $request->position; //form
    //     $slug = Str::of($request->name)->slug('-');
    //     //upload image
    //     $files = $request->image;
    //     if ($files != null) {
    //         $extension = $files->getClientOriginalExtension();
    //         if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
    //             $filename = date('YmdHis') . '.' . $extension;
    //             $address->image = $filename;
    //             $files->move(public_path('images/address'), $filename);
    //         }
    //     }
    //     $address->created_at = Carbon::now('Asia/Ho_Chi_Minh');
    //     $address->created_by = 1;
    //     $address->status = $request->status; //form
    //     if($address->save())//Luuu vao CSDL
    //     {
    //         return response()->json(
    //             [
    //                 'status' => true, 
    //                 'message' => 'Thêm thành công', 
    //                 'address' => $address
    //             ],
    //             201
    //         );    
    //     }
    //     else
    //     {
    //         return response()->json(
    //             [
    //                 'status' => false, 
    //                 'message' => 'Thêm không thành công', 
    //                 'address' => null
    //             ],
    //             422
    //         );
    //     }
    // }
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
        $address->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
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
        $address->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
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
