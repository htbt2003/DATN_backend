<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function category_list($parent_id = 0)
    {
        $args = [
            ['parent_id', '=', $parent_id],
            ['status', '=', 1]
        ];
        $categories = Category::where($args)
            ->orderBy('sort_order', 'ASC')
            ->select('id', 'name', 'slug', 'status' )
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'categories' => $categories
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $category = Category::find($id);
        if($category == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'category' => null
                ],
                404
            );    
        }
        $category->updated_at = date('Y-m-d H:i:s');
        $category->updated_by = 1;
        $category->status = ($category->status == 1) ? 2 : 1; //form
        if($category->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'category' => $category
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
                    'category' => null
                ],
                422
            );
        }
    }
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Category::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Category::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = Category::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'image', 'status' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_category.name', 'like', '%' . $key . '%');
            });
        }
        $total = Category::where('status', '!=', 0)->count();
        $categories = $query->paginate(5);
        $trash = Category::where('status', '=', 0)->count();
        $publish = Category::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'categories' => $categories,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = Category::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'slug', 'image', 'status' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_category.name', 'like', '%' . $key . '%');
            });
        }
        $categoriesAll = $query->get(); 
        $total = $query->count();
        $categories = $query->paginate(5);
        $trash = Category::where('status', '=', 0)->count();
        $publish = Category::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'categories' => $categories,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
            'categoriesAll' => $categoriesAll,
        ];
        return response()->json($result,200);

    }
    public function show($id)
    {
        if(is_numeric($id)){
            $category = Category::find($id);
        }
        else{
            $category = Category::where('slug', $id)->first();
        }
        return response()->json(
            ['status' => true, 
             'message' => 'Tải dữ liệu thành công',
             'category' => $category],
            200
        );
    }
    public function store(Request $request)
    {
        $category = new Category();
        $category->name = $request->name; //form
        $category->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $category->image = $filename;
                $files->move(public_path('images/category'), $filename);
            }
        }
        //
        $category->parent_id = $request->parent_id; //form
        $category->sort_order = $request->sort_order; //form
        $category->metakey = $request->metakey; //form
        $category->metadesc = $request->metadesc; //form
        $category->created_at = date('Y-m-d H:i:s');
        $category->created_by = 1;
        $category->status = $request->status; //form
        if($category->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'category' => $category
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
                    'category' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if($category == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'category' => null
                ],
                404
            );    
        }
        $category->name = $request->name; //form
        $category->slug = Str::of($request->name)->slug('-');
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $category->image = $filename;
                $files->move(public_path('images/category'), $filename);
            }
        }
        //
        $category->parent_id = $request->parent_id; //form
        $category->sort_order = $request->sort_order; //form
        $category->metakey = $request->metakey; //form
        $category->metadesc = $request->metadesc; //form
        $category->updated_at = date('Y-m-d H:i:s');
        $category->updated_by = 1;
        $category->status = $request->status; //form
        if($category->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'category' => $category
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
                    'category' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $category = Category::find($id);
        if($category == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'category' => null
                ],
                404
            );    
        }
        $category->updated_at = date('Y-m-d H:i:s');
        $category->updated_by = 1;
        $category->status = 0; 
        if($category->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'category' => $category
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $category = Category::find($id);
        if($category == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'category' => null
                ],
                404
            );    
        }
        $category->updated_at = date('Y-m-d H:i:s');
        $category->updated_by = 1;
        $category->status = 2; 
        if($category->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'category' => $category
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if($category == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'category' => null
                ],
               404 
            );    
        }
        if($category->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'category' => $category
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
                    'category' => null
                ],
                422
            );    
        }
    }

}
