<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;

class MenuController extends Controller
{
    public function menu_list($position, $parent_id = 0)
    {
        $args = [
            ['position', '=', $position],
            ['parent_id', '=', $parent_id],
            ['status', '=', 1]
        ];
        $menus = Menu::where($args)
            ->orderBy('created_at', 'ASC')
            ->get();
        foreach($menus as $menu){
            $menu->children = Menu::where('parent_id', $menu->id)
                ->where('status', 1)
                ->orderBy('created_at', 'ASC')
                ->get();
        }
        if(count($menus)){
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Tải dữ liệu thành công',
                    'menus' => $menus,
                ],
                200
            );
        }
        else{
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Không có dữ liệu',
                    'menus' => null
                ],
                200
            );
        }
    }
    public function search($key)
    {
        $args = [
            ["name","Like" , "%$key%"],
            ['status', '!=', 0]
        ];
        $menus = Menu::where($args)
            ->get();
        if(count($menus) > 0){
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Tải dữ liệu thành công',
                    'menus' => $menus
                ],
                200
            );    
        }
        else{
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Không có dữ liệu',
                    'menus' => null
                ],
                200
            );    
        }
    }
    public function changeStatus($id)
    {
        $menu = Menu::find($id);
        if($menu == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'menu' => null
                ],
                404
            );    
        }
        $menu->updated_at = date('Y-m-d H:i:s');
        $menu->updated_by = 1;
        $menu->status = ($menu->status == 1) ? 2 : 1; //form
        if($menu->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'menu' => $menu
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
                    'menu' => null
                ],
                422
            );
        }
    }
    public function trash()
    {
        $menus = Menu::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->paginate(5);
        $menusAll = Menu::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $total = Menu::where('status', '!=', 0)->count();
        $publish = Menu::where('status', '=', 1)->count();
        $trash = Menu::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'menus' => $menus,
                'menusAll' => $menusAll,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }

    public function index()
    {
        $menus = Menu::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->paginate(5);
        $menusAll = Menu::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get(0);
        $categoryies = Menu::where([['type', '=', 'danh-muc-san-pham'],['status', '!=', 0]])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $brands = Menu::where([['type', '=', 'thuong-hieu'],['status', '!=', 0]])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $pages = Menu::where([['type', '=', 'trang-don'],['status', '!=', 0]])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $topics = Menu::where([['type', '=', 'chu-de-bai-viet'],['status', '!=', 0]])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $options = Menu::where([['type', '=', 'tuy-bien'],['status', '!=', 0]])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'link', 'position', 'status' )
            ->get();
        $total = Menu::where('status', '!=', 0)->count();
        $publish = Menu::where('status', '=', 1)->count();
        $trash = Menu::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'menus' => $menus,
                'menusAll' => $menusAll,
                'categoryies' => $categoryies,
                'brands' => $brands,
                'pages' => $pages,
                'topics' => $topics,
                'options' => $options,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function show($id)
    {
        $menu = Menu::find($id);
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'menu' => $menu],
            200
        );
    }
    public function store(Request $request)
    {
        $listid =  $request->listid;
        $type =  $request->type;
        $flag = false;
        switch ($type) {
            case "danh-muc-san-pham":
                foreach ($listid as $id)
                {
                    // $intValue = intval($id);
                    $category = Category::find($id);
                    $menu = new Menu();
                    $menu->name = $category->name;
                    $menu->link = '/danh-muc-san-pham/'.$category->slug; 
                    $menu->parent_id = 0;
                    $menu->type = $request->type;
                    $menu->created_at = date('Y-m-d H:i:s');
                    $menu->created_by = 1;
                    $menu->status = 2;
                    $menu->position = $request->position;    
                    $menu->save();      
                }
                $flag = true;
                break;
            case "thuong-hieu":
                foreach($listid as $id)
                {
                    $brand = Brand::find($id);
                    $menu = new Menu();
                    $menu->name = $brand->name;
                    $menu->link = '/thuong-hieu/'.$brand->slug; 
                    $menu->parent_id = 0;
                    $menu->type = $request->type;
                    $menu->created_at = date('Y-m-d H:i:s');
                    $menu->created_by = 1;
                    $menu->status = 2;
                    $menu->position = $request->position;    
                    $menu->save();      
                }
                $flag = true;
                break;
                case "chu-de-bai-viet":
                    foreach($listid as $id)
                    {
                        $topic = Topic::find($id);
                        $menu = new Menu();
                        $menu->name = $brand->name;
                        $menu->link = '/chu-de-bai-viet/'.$topic->slug; 
                        $menu->parent_id = 0;
                        $menu->type = $request->type;
                        $menu->created_at = date('Y-m-d H:i:s');
                        $menu->created_by = 1;
                        $menu->status = 2;
                        $menu->position = $request->position;    
                        $menu->save();      
                    }
                    $flag = true;
                    break;
                case "trang-don":
                    foreach($listid as $id)
                    {
                        $page = Post::find($id);
                        $menu = new Menu();
                        $menu->name = $brand->name;
                        $menu->link = '/trang-don/'.$page->slug; 
                        $menu->parent_id = 0;
                        $menu->type = $request->type;
                        $menu->created_at = date('Y-m-d H:i:s');
                        $menu->created_by = 1;
                        $menu->status = 2;
                        $menu->position = $request->position;    
                        $menu->save();      
                    }
                    $flag = true;
                    break;
        }
        if($flag)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thêm thành công', 
                    'type' => $type, 
                    'listid' => $listid, 
                ],
                201
            );    
        }
        else
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không thành công', 
                    'type' => $type, 
                    'listid' => $listid, 
                ],
                422
            );
        }
    }
    public function tao($position, $type, $listid)
    {
        $flag = true;
        switch ($type) {
            case "danh-muc-san-pham":
                for ($i = 0; $i < strlen($listid); $i++)
                {
                    $category = Category::find($listid[$i]);
                    $menu = new Menu();
                    $menu->name = $category->name;
                    $menu->link = 'danh-muc-san-pham/'.$category->slug; 
                    $menu->parent_id = 0;
                    $menu->type = $type;
                    $menu->created_at = date('Y-m-d H:i:s');
                    $menu->created_by = 1;
                    $menu->status = 2;
                    $menu->position = $position;    
                    $menu->save();      
                }
                $flag = true;
                break;
            case "thuong-hieu":
                for ($i = 0; $i < strlen($listid); $i++)
                {
                    $brand = Brand::find($listid[$i]);
                    $menu = new Menu();
                    $menu->name = $brand->name;
                    $menu->link = 'thuong-hieu/'.$brand->slug; 
                    $menu->parent_id = 0;
                    $menu->type = $type;
                    $menu->created_at = date('Y-m-d H:i:s');
                    $menu->created_by = 1;
                    $menu->status = 2;
                    $menu->position = $position;    
                    $menu->save();      
                }
                $flag = true;
                break;
                case "chu-de-bai-viet":
                    for ($i = 0; $i < strlen($listid); $i++)
                    {
                        $topic = Topic::find($listid[$i]);
                        $menu = new Menu();
                        $menu->name = $topic->name;
                        $menu->link = 'chu-de-bai-viet/'.$topic->slug; 
                        $menu->parent_id = 0;
                        $menu->type = $type;
                        $menu->created_at = date('Y-m-d H:i:s');
                        $menu->created_by = 1;
                        $menu->status = 2;
                        $menu->position = $position;    
                        $menu->save();      
                    }
                    $flag = true;
                    break;
                case "trang-don":
                    for ($i = 0; $i < strlen($listid); $i++)
                    {
                        $page = Post::find($listid[$i]);
                        $menu = new Menu();
                        $menu->name = $page->title;
                        $menu->link = 'trang-don/'.$page->slug; 
                        $menu->parent_id = 0;
                        $menu->type = $type;
                        $menu->created_at = date('Y-m-d H:i:s');
                        $menu->created_by = 1;
                        $menu->status = 2;
                        $menu->position = $position;    
                        $menu->save();      
                    }
                    $flag = true;
                    break;
        }
        if($flag)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thêm thành công', 
                    'type' => $type, 
                    'listid' => $listid, 
                ],
                201
            );    
        }
        else
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không thành công', 
                    'type' => $type, 
                    'listid' => $listid, 
                ],
                422
            );
        }
    }
    
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);
        if($menu == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'menu' => null
                ],
                404
            );  
        }
        $menu->name = $request->name; //form
        $menu->link = $request->link; //form
        $menu->parent_id = $request->parent_id; //form
        //$menu->type = $request->type; //form
        $menu->updated_at = date('Y-m-d H:i:s');
        $menu->updated_by = 1;
        $menu->status = $request->status; //form
        $menu->position = $request->position; //form
        if($menu->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'menu' => $menu
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
                    'menu' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $menu = Menu::find($id);
        if($menu == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'menu' => null
                ],
                404
            );    
        }
        $menu->updated_at = date('Y-m-d H:i:s');
        $menu->updated_by = 1;
        $menu->status = 0; 
        if($menu->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'menu' => $menu
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $menu = Menu::find($id);
        if($menu == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'menu' => null
                ],
                404
            );    
        }
        $menu->updated_at = date('Y-m-d H:i:s');
        $menu->updated_by = 1;
        $menu->status = 2; 
        if($menu->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'menu' => $menu
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        if($menu == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'menu' => null
                ],
               404 
            );    
        }
        if($menu->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'menu' => $menu
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
                    'menu' => null
                ],
                422
            );    
        }
    }

}
