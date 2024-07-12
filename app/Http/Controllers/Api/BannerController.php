<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;
use Carbon\Carbon;


class BannerController extends Controller
{
    public function banner_list($position)
    {
        $args = [
            ['position', '=', $position],
            ['status', '=', 1]
        ];
        $banners = Banner::where($args)
        ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'banners' => $banners
            ],
            200
        );
    }
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Banner::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Banner::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = Banner::where('status', '=', 0)
        ->select('id', 'name', 'image', 'slug', 'position', 'status' )
        ->orderBy('db_banner.created_at', 'DESC');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_banner.name', 'like', '%' . $key . '%');
            });
        }
        $total = Banner::where('status', '!=', 0)->count();
        $banners = $query->paginate(5);
        $trash = Banner::where('status', '=', 0)->count();
        $publish = Banner::where('status', '=', 1)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'banners' => $banners,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function index(Request $condition)
    {
        $query = Banner::where('status', '!=', 0)
        ->select('id', 'name', 'image', 'slug', 'position', 'status' )
        ->orderBy('db_banner.created_at', 'DESC');
        
        if ($condition->input('brandId') != null) {
            $query->where('brand_id', $condition->input('brandId'));
        }

        if ($condition->input('catId') != null ) {
            
            $query->where('category_id', $condition->input('catId'));
        }

        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_banner.name', 'like', '%' . $key . '%');
            });
        }
        $total = $query->count();
        $banners = $query->paginate(5);
        $total = $banners->total();
        $trash = Banner::where('status', '=', 0)->count();
        $publish = Banner::where('status', '=', 1)->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'banners' => $banners,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $banner = Banner::find($id);
        if($banner == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'banner' => null
                ],
                404
            );    
        }
        $banner->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $banner->updated_by = 1;
        $banner->status = ($banner->status == 1) ? 2 : 1; //form
        if($banner->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'banner' => $banner
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
                    'banner' => null
                ],
                422
            );
        }
    }

    public function show($id)
    {
        $banner = Banner::find($id);
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'banner' => $banner],
            200
        );
    }
    public function store(Request $request)
    {
        $banner = new Banner();
        $banner->name = $request->name; //form
        $banner->description = $request->description; //form
        // $banner->link = $request->link; //form
        $banner->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $banner->image = $filename;
                $files->move(public_path('images/banner'), $filename);
            }
        }
        $banner->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $banner->created_by = 1;
        $banner->status = $request->status; //form
        if($banner->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thêm thành công', 
                    'banner' => $banner
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
                    'banner' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if($banner == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'banner' => null
                ],
                404
            );    
        }
        $banner->name = $request->name; //form
        $banner->description = $request->description; //form
        // $banner->link = $request->link; //form
        $banner->position = $request->position; //form
        $slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $banner->image = $filename;
                $files->move(public_path('images/banner'), $filename);
            }
        }
        $banner->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $banner->updated_by = 1;
        $banner->status = $request->status; //form
        if($banner->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'banner' => $banner
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
                    'banner' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $banner = banner::find($id);
        if($banner == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'banner' => null
                ],
                404
            );    
        }
        $banner->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $banner->updated_by = 1;
        $banner->status = 0; 
        if($banner->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'banner' => $banner
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $banner =Banner::find($id);
        if($banner == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'banner' => null
                ],
                404
            );    
        }
        $banner->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $banner->updated_by = 1;
        $banner->status = 2; 
        if($banner->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'banner' => $banner
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $banner =Banner::findOrFail($id);
        if($banner == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'banner' => null
                ],
               404 
            );    
        }
        if($banner->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'banner' => $banner
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
                    'banner' => null
                ],
                422
            );    
        }
    }
}
