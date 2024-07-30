<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class ApiAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     if(Auth::check())
    //     {
    //         if(auth()->user()->tokenCan('server:admin'))
    //         {
    //             return $next($request);
    //         }
    //         else{
    //             return response()->json([
    //             'message'=> 'Forbidden!',
    //             ], 403);
    //         }
    //         }
    //     else
    //     {
    //         return response()->json([
    //         'status'=>401,
    //         'message'=>'Please Login First',
    //         ]);
    //     }
    // }
    public function handle(Request $request, Closure $next)
{
    if (Auth::check()) // Check if the user is authenticated using JWT
    {
        $user = Auth::guard('api')->user();

        // Directly compare the role
        if ($user->roles == 'admin') {
            return $next($request); // Allow the request to proceed if the role is 'admin'
        } else {
            return response()->json([
                'message' => 'Forbidden!',
            ], 403); // Deny access if the role does not match
        }
    } else {
        return response()->json([
            'status' => 401,
            'message' => 'Vui lòng đăng nhập trước',
        ]); // Return 401 Unauthorized if not authenticated
    }
}

}

