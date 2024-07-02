<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'loginGoogle', 'loginFacebook']]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    public function loginFacebook(Request $request)
    {
        $user = User::where('facebook_id', $request->input('id'))->first();
    
        // If user not found, find by email
        if (!$user) {
            $user = User::where('email', $request->input('email'))->first();
    
            if ($user) {
                $user->facebook_id = $request->input('id');
                $user->save();
            } else {
                // If user not found by email, create a new user
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'facebook_id' => $request->input('id'),
                    'image' => $request->input('picture.data.url'),
                    // Add other necessary fields
                ]);
            }
        }
    
        // Generate JWT token
        $token = auth()->login($user);
    
        return $this->respondWithToken($token);
    }
    public function loginGoogle(Request $request)
    {
        $user = User::where('google_sub', $request->input('sub'))->first();
    
        // If user not found, find by email
        if (!$user) {
            $user = User::where('email', $request->input('email'))->first();    
            if ($user) {
                $user->google_sub = $request->input('sub');
                $user->save();
            } else {
                // If user not found by email, create a new user
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'google_sub' => $request->input('sub'),
                    'image' => $request->input('picture'),
                    // Add other necessary fields
                ]);
            }
        }
    
        // Generate JWT token
        $token = auth()->login($user);
    
        return $this->respondWithToken($token);
    }

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
        $user = User::where('email', $request->email)->first();
        if($user){
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Email đã tồn tại',
                    'user' => $user
                ]
            );    
        }
        $user = new User();
        $user->name = $request->name; //form
        $user->gender = $request->gender; //form
        $user->email = $request->email; //form
        $user->phone = $request->phone; //form
        $user->username = $request->username; //form
        $user->password = Hash::make($request->password); //form
        $user->address = $request->address; //form
        $user->roles = $request->roles; //form
        $files1 = $request->image;
        if ($files1 != null) {
            $extension = $files1->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $user->image = $filename;
                $files1->move(public_path('images/user'), $filename);
            }
        }
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
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => true,
            'message' => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }

}
