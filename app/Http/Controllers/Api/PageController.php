<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\Topic;

class PageController extends Controller
{
    function page_list($limit, $type)
    {
        $page = Post::orderBy('created_at', 'DESC')->first();
        $args = [
            ['type', '=', $type],
            ['status', '=', 1],
            ['id', '!=', $page->id]
        ];
        $pages = Post::where($args)
            ->orderBy('created_at', 'DESC')
            -> limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages
            ],
            200
        );
    }
    public function page_all($limit, $page = 1)
    {
        $offset = ($page - 1) * $limit;
        $pages = Post::where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages
            ],
            200
        );
    }
    public function page_topic($limit, $topic_id)
    {
        $listid = array();
        array_push($listid, $topic_id + 0);
        $args_topic1 = [
            ['parent_id', '=', $topic_id + 0],
            ['status', '=', 1]
        ];
        $list_topic1 = Topic::where($args_topic1)->get();
        if (count($list_topic1) > 0) {
            foreach ($list_topic1 as $row1) {
                array_push($listid, $row1->id);
                $args_topic2 = [
                    ['parent_id', '=', $row1->id],
                    ['status', '=', 1]
                ];
                $list_topic2 = Topic::where($args_topic2)->get();
                if (count($list_topic2) > 0) {
                    foreach ($list_topic2 as $row2) {
                        array_push($listid, $row2->id);
                    }
                }
            }
        }
        $pages = Post::where('status', 1)
            ->whereIn('topic_id', $listid)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages
            ],
            200
        );
    }
    public function page_new()
    {
        $page = Post::orderBy('created_at', 'DESC')->first();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'page' => $page
            ],
            200
        );
    }
    public function page_detail($slug)
    {
        $args = [
            ['slug', '=', $slug],
            ['status', '=', 1],
            ['type', '=', 'page']
        ];
        $page = Post::where($args)->first();
            return response()->json(
                ['status' => true, 
                 'message' => 'Tải dữ liệu thành công', 
                 'page' => $page,
                ],
                200
            );
    }

    function page_order($id, $limit)
    {
        $args = [
            ['id', '=', $id],
            ['status', '=', 1]
        ];
        $pages = Post::where($args)
            ->orderBy('created_at', 'DESC')
            > limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $page = Post::find($id);
        if($page == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'page' => null
                ],
                404
            );    
        }
        $page->updated_at = date('Y-m-d H:i:s');
        $page->updated_by = 1;
        $page->status = ($page->status == 1) ? 2 : 1; //form
        if($page->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'page' => $page
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
                    'page' => null
                ],
                422
            );
        }
    }
    public function trash()
    {
        $pages = Post::where([['status', '=', 0], ['type', '=', 'page']])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'topic_id', 'image', 'status' )
            ->paginate(5);
        $total = Post::where([['status', '!=', 0], ['type', '=', 'page']])->count();
        $publish = Post::where([['status', '=', 1], ['type', '=', 'page']])->count();
        $trash = Post::where([['status', '=', 0], ['type', '=', 'page']])->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }

    public function index()
    {
        $pages = Post::where([['status', '!=', 0], ['type', '=', 'page']])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'topic_id', 'image', 'status' )
            ->paginate(5);
        $pagesAll = Post::where([['status', '!=', 0], ['type', '=', 'page']])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'topic_id', 'image', 'status' )
            ->get();
        $total = Post::where([['status', '!=', 0], ['type', '=', 'page']])->count();
        $publish = Post::where([['status', '=', 1], ['type', '=', 'page']])->count();
        $trash = Post::where([['status', '=', 0], ['type', '=', 'page']])->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'pages' => $pages,
                'pagesAll' => $pagesAll,
                'total' => $total,
                'publish' => $publish,
                'trash' => $trash,
            ],
            200
        );
    }
    public function show($id)
    {
        if(is_numeric($id)){
            $page = Post::find($id);
        }
        else{
            $page = Post::where('slug', $id)->first();
        }
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'page' => $page],
            200
        );
    }
    public function store(Request $request)
    {
        $page = new Post();
        $page->topic_id = $request->topic_id; //form
        $page->title = $request->title; //form
        $page->slug = Str::of($request->title)->slug('-');
        $page->detail = $request->detail; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $page->image = $filename;
                $files->move(public_path('images/page'), $filename);
            }
        }
        //
        $page->type = $request->type; //form
        $page->metakey = $request->metakey; //form
        $page->metadesc = $request->metadesc; //form
        $page->created_at = date('Y-m-d H:i:s');
        $page->created_by = 1;
        $page->status = $request->status; //form
        $page->save(); //Luuu vao CSDL
        if($page->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'page' => $page
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
                    'page' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $page = Post::find($id);
        if($page == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'page' => null
                ],
                404
            );    
        }
        $page->topic_id = $request->topic_id; //form
        $page->title = $request->title; //form
        $page->slug = Str::of($request->title)->slug('-');
        $page->detail = $request->detail; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $page->image = $filename;
                $files->move(public_path('images/page'), $filename);
            }
        }
        //
        $page->type = $request->type; //form
        $page->metakey = $request->metakey; //form
        $page->metadesc = $request->metadesc; //form
        $page->updated_at = date('Y-m-d H:i:s');
        $page->updated_by = 1;
        $page->status = $request->status; //form
        if($page->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'page' => $page
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
                    'page' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $page = Post::find($id);
        if($page == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'page' => null
                ],
                404
            );    
        }
        $page->updated_at = date('Y-m-d H:i:s');
        $page->updated_by = 1;
        $page->status = 0; 
        if($page->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'page' => $page
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $page = Post::find($id);
        if($page == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'page' => null
                ],
                404
            );    
        }
        $page->updated_at = date('Y-m-d H:i:s');
        $page->updated_by = 1;
        $page->status = 2; 
        if($page->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'page' => $page
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $page = Post::findOrFail($id);
        if($page == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'page' => null
                ],
               404 
            );    
        }
        if($page->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'page' => $page
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
                    'page' => null
                ],
                422
            );    
        }
    }

}
