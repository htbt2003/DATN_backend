<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;
use Carbon\Carbon;

class ConfigController extends Controller
{
    public function show()
    {
        $config = Config::first();
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'config' => $config],
            200
        );
    }
    public function update(Request $request)
    {
        $config = Config::first();
        if($config == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'config' => null
                ],
                404
            );    
        }
        $config->author = $request->author; //form
        $config->email = $request->email; //form
        $config->phone = $request->phone; //form
        $config->zalo = $request->zalo; //form
        $config->facebook = $request->facebook; //form
        $config->address = $request->address; //form
        $config->youtube = $request->youtube; //form
        $config->metadesc = $request->metadesc; //form
        $config->metakey = $request->metakey; //form
        $config->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $config->updated_by = 1;
        $config->status = $request->status; //form
        if($config->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'config' => $config
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
                    'config' => null
                ],
                422
            );
        }
    }
}
