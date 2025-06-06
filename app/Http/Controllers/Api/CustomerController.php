<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function changeStatus($id)
    {
        $customer = User::find($id);
        if($customer == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'customer' => null
                ],
                404
            );    
        }
        $customer->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $customer->updated_by = 1;
        $customer->status = ($customer->status == 1) ? 2 : 1; //form
        if($customer->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'customer' => $customer
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
                    'customer' => null
                ],
                422
            );
        }
    }
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = User::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = User::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }
    public function trash(Request $condition)
    {
        $query = User::where([['status', '=', 0], ['roles', '=', 'customer']])
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'phone', 'email', 'image', 'status','address');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_user.name', 'like', '%' . $key . '%');
            });
        }
        $users = $query->paginate(5);
        $total = User::where([['status', '!=', 0], ['roles', '=', 'customer']])->count();
        $publish = User::where([['status', '=', 1], ['roles', '=', 'customer']])->count();
        $trash = User::where([['status', '=', 0], ['roles', '=', 'customer']])->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'users' => $users,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }

    public function index(Request $condition)
    {
        $query = User::where([['status', '!=', 0], ['roles', '=', 'customer']])
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'phone', 'email', 'image', 'status','address');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_user.name', 'like', '%' . $key . '%');
            });
        }
        $users = $query->paginate(5);
        $total = User::where([['status', '!=', 0], ['roles', '=', 'customer']])->count();
        $publish = User::where([['status', '=', 1], ['roles', '=', 'customer']])->count();
        $trash = User::where([['status', '=', 0], ['roles', '=', 'customer']])->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'users' => $users,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function show($id)
    {
        $user = User::find($id);
        return response()->json(
            ['status' => true, 
             'message' => 'Tải dữ liệu thành công', 
             'user' => $user],
            200
        );
    }
    public function store(Request $request)
    {
        $user = new User();
        $user->name = $request->name; //form
        $user->gender = $request->gender; //form
        $user->email = $request->email; //form
        $user->phone = $request->phone; //form
        $user->address = $request->address; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $user->image = $filename;
                $files->move(public_path('images/user'), $filename);
            }
        }
        //
        $user->roles = $request->roles; //form
        $user->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $user->created_by = 1;
        $user->status = $request->status; //form
        if($user->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'user' => $user
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
                    'user' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if($user == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'user' => null
                ],
                404
            );    
        }
        $user->name = $request->name; //form
        $user->gender = $request->gender; //form
        $user->email = $request->email; //form
        $user->phone = $request->phone; //form
        $user->address = $request->address; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $user->image = $filename;
                $files->move(public_path('images/user'), $filename);
            }
        }
        //
        $user->roles = $request->roles; //form
        $user->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $user->updated_by = 1;
        $user->status = $request->status; //form
        if($user->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'user' => $user
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
                    'user' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $user = User::find($id);
        if($user == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'user' => null
                ],
                404
            );    
        }
        $user->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $user->updated_by = 1;
        $user->status = 0; 
        if($user->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Đã chuyển vào thành công', 
                    'user' => $user
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $user = User::find($id);
        if($user == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'user' => null
                ],
                404
            );    
        }
        $user->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $user->updated_by = 1;
        $user->status = 2; 
        if($user->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'user' => $user
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if($user == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'user' => null
                ],
               404 
            );    
        }
        if($user->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'user' => $user
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
                    'user' => null
                ],
                422
            );    
        }
    }
    public function login($email, $password){
        $user = User::where([['email', '=', $email], ['password', '=', $password]])->first();
        if($user != null){
            return response()->json(['message' => 'Đăng nhập thành công', 'status' => true, 'user' => $user]);
        }else{
            return response()->json(['message' => 'Sai email hoặc mật khẩu', 'status' => false, 'user' => null]);
        }
    }

}
