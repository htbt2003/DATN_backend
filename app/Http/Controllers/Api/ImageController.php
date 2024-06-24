<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;


class ImageController extends Controller
{
    public function index()
    {
        $images = Image::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'product_id', 'image', )
            ->get();
        $total = Image::count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'images' => $images,
            'total' => $total
        ]
        return response()->json($result,200);
    }
    public function show($id)
    {
        if(is_numeric($id)){
            $image = Image::find($id);        }
        else{
            $image = Image::where('slug', $id)->first();
        }
        
        return response()->json(
            [   'status' => true, 
                'message' => 'Tải dữ liệu thành công', 
                'image' => $image
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $image = new image();
        $image->name = $request->name; //form
        $image->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $image->image = $filename;
                $files->move(public_path('images/image'), $filename);
            }
        }
        //
        $image->sort_order = $request->sort_order; //form
        $image->metakey = $request->metakey; //form
        $image->metadesc = $request->metadesc; //form
        $image->created_at = date('Y-m-d H:i:s');
        $image->created_by = 1;
        $image->status = $request->status; //form
        if($image->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'image' => $image
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
                    'image' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $image = Image::find($id);
        if($image == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'image' => null
                ],
                404
            );    
        }
        $image->name = $request->name; //form
        $image->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $image->image = $filename;
                $files->move(public_path('images/image'), $filename);
            }
        }
        //
        $image->sort_order = $request->sort_order; //form
        $image->metakey = $request->metakey; //form
        $image->metadesc = $request->metadesc; //form
        $image->updated_at = date('Y-m-d H:i:s');
        $image->updated_by = 1;
        $image->status = $request->status; //form
        if($image->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'image' => $image
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
                    'image' => null
                ],
                422
            );
        }
    }
    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        if($image == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'image' => null
                ],
               404 
            );    
        }
        if($image->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'image' => $image
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
                    'image' => null
                ],
                422
            );    
        }
    }
}
