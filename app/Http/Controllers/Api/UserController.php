<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;

class UserController extends Controller
{
    public function update_account(Request $request, $id)
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
        $user->username = $request->username; //form
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
        $user->updated_at = date('Y-m-d H:i:s');
        $user->updated_by = 1;
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

    public function changeStatus($id)
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
        $user->updated_at = date('Y-m-d H:i:s');
        $user->updated_by = 1;
        $user->status = ($user->status == 1) ? 2 : 1; //form
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
    public function trash()
    {
        $users = User::where([['status', '=', 0], ['roles', '=', 'user']])
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'phone', 'email', 'image', 'status')
        ->paginate(5);
        $total = User::where([['status', '!=', 0], ['roles', '=', 'user']])->count();
        $publish = User::where([['status', '=', 1], ['roles', '=', 'user']])->count();
        $trash = User::where([['status', '=', 0], ['roles', '=', 'user']])->count();
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

    public function index()
    {
        $users = User::where([['status', '!=', 0], ['roles', '=', 'user']])
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'phone', 'email', 'image', 'status')
        ->paginate(5);
        $total = User::where([['status', '!=', 0], ['roles', '=', 'user']])->count();
        $publish = User::where('status', '=', 1)->count();
        $trash = User::where('status', '=', 0)->count();
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
        $user->username = $request->username; //form
        $user->password = $request->password; //form
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
        $user->created_at = date('Y-m-d H:i:s');
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
        $user->username = $request->username; //form
        $user->password = $request->password; //form
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
        $user->updated_at = date('Y-m-d H:i:s');
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
        $user->updated_at = date('Y-m-d H:i:s');
        $user->updated_by = 1;
        $user->status = 0; 
        if($user->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
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
        $user->updated_at = date('Y-m-d H:i:s');
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
    // public function loginNNNNN($email, $password){
    //     $user = User::where([['email', '=', $email], ['password', '=', $password]])->first();
    //     if($user != null){
    //         return response()->json(['message' => 'Đăng nhập thành công', 'status' => true, 'user' => $user]);
    //     }else{
    //         return response()->json(['message' => 'Sai email hoặc mật khẩu', 'status' => false, 'user' => null]);
    //     }
    // }
    public function register(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'password' => 'required|string|min:6',
        // ]);

        // $user = new User([
        //     'name' => $request->input('name'),
        //     'email' => $request->input('email'),
        //     'password' => Hash::make($request->input('password')),
        // ]);
        $user = new User();
        $user->name = $request->name; //form
        $user->gender = $request->gender; //form
        $user->email = $request->email; //form
        $user->phone = $request->phone; //form
        $user->username = $request->username; //form
        $user->password = Hash::make($request->password); //form
        $user->address = $request->address; //form
        $user->roles = $request->roles; //form
        $user->image = $request->image; 
        $user->created_at = date('Y-m-d H:i:s');
        $user->created_by = 1;
        $user->status = $request->status; //form
        $user->save();

        return response()->json(
            [
                'status' => true,
                'message' => 'Đăng ký thành công',
                'user' => $user
            ],
            201
        );    
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if($user->roles == 'admin')
                {
                    $token = $user->createToken($user->email.'_AdminToken',['server:admin'])->plainTextToken;
                }
                else
                {
                    $token = $user->createToken($user->email.'_Token', [''])->plainTextToken;
                }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $user,
                    'token' => $token,
                ],
                200
            );    
        } 
        else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Sai mật khẩu hoặc password',
                    'user' => null,
                    'token' => null
                ], 401);
        }
    }

    public function logout(Request $request)
    {
        // $request->user()->token()->revoke();
        // Auth::logout();
        return response()->json([
            'status' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

}
