<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\Topic;

class PostController extends Controller
{
    function post_list($limit, $type)
    {
        $post = Post::orderBy('created_at', 'DESC')->first();
        $args = [
            ['type', '=', $type],
            ['status', '=', 1],
            ['id', '!=', $post->id]
        ];
        $posts = Post::where($args)
            ->orderBy('created_at', 'DESC')
            -> limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts
            ],
            200
        );
    }
    public function post_all()
    {
        $posts = Post::where([['status', '=', 1], ['type', '=', 'post']])
            ->orderBy('created_at', 'DESC')
            ->paginate(5);
        $total = $posts->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts,
                'total' => $total,
            ],
            200
        );
    

    }
    public function post_topic( $topic_id)
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
        $posts = Post::where('status', 1)
            ->whereIn('topic_id', $listid)
            ->orderBy('created_at', 'DESC')
            ->paginate(6);
        $total = $posts->count();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts,
                'total' => $total,

            ],
            200
        );
    }
    public function post_new()
    {
        $post = Post::orderBy('created_at', 'DESC')->first();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'post' => $post
            ],
            200
        );
    }
    public function post_detail($slug)
    {
        $args = [
            ['slug', '=', $slug],
            ['status', '=', 1],
            ['type', '=', 'post']
        ];
        $post = Post::where($args)->first();
        if($post == null){
            return response()->json(
                ['status' => false, 
                 'message' => 'Không timg thấy dữ liệu', 
                 'post' =>null
                ],
                400
            );
        }
        $listid = array();
        array_push($listid, $post->topic_id);
        $args_topic1 = [
            ['parent_id', '=', $post->topic_id],
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
        $post_other = Post::where([['id', '!=', $post->id],['status', '=', 1]])
            ->whereIn('topic_id', $listid)
            ->orderBy("created_at", 'DESC')
            ->limit(8)
            ->get();
            return response()->json(
                ['status' => true, 
                 'message' => 'Tải dữ liệu thành công', 
                 'post' => $post,
                 'post_other'=>$post_other
                ],
                200
            );
    }

    function post_order($id, $limit)
    {
        $args = [
            ['id', '=', $id],
            ['status', '=', 1]
        ];
        $posts = Post::where($args)
            ->orderBy('created_at', 'DESC')
            > limit($limit)
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $post = Post::find($id);
        if($post == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'post' => null
                ],
                404
            );    
        }
        $post->updated_at = date('Y-m-d H:i:s');
        $post->updated_by = 1;
        $post->status = ($post->status == 1) ? 2 : 1; //form
        if($post->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'post' => $post
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
                    'post' => null
                ],
                422
            );
        }
    }
    public function trash()
    {
        $posts = Post::where([['status', '=', 0], ['type', '=', 'post']])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'topic_id', 'image', 'status' )
            ->paginate(5);
        $total = Post::where([['status', '=', 0], ['type', '=', 'post']])->count();
        $publish = Post::where('status', '=', 1)->count();
        $trash = Post::where('status', '=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }

    public function index()
    {
        $posts = Post::where([['status', '!=', 0], ['type', '=', 'post']])
            ->orderBy('created_at', 'DESC')
            ->select('id', 'title', 'slug', 'topic_id', 'image', 'status' )
            ->paginate(5);
        $total = Post::where([['status', '!=', 0], ['type', '=', 'post']])->count();
        $publish = Post::where([['status', '=', 1], ['type', '=', 'post']])->count();
        $trash = Post::where([['status', '=', 0], ['type', '=', 'post']])->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'posts' => $posts,
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
            $post = Post::find($id);
        }
        else{
            $post = Post::where('slug', $id)->first();
        }
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'post' => $post],
            200
        );
    }
    public function store(Request $request)
    {
        $post = new Post();
        $post->topic_id = $request->topic_id; //form
        $post->title = $request->title; //form
        $post->slug = Str::of($request->title)->slug('-');
        $post->detail = $request->detail; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $post->image = $filename;
                $files->move(public_path('images/post'), $filename);
            }
        }
        //
        $post->type = $request->type; //form
        $post->metakey = $request->metakey; //form
        $post->metadesc = $request->metadesc; //form
        $post->created_at = date('Y-m-d H:i:s');
        $post->created_by = 1;
        $post->status = $request->status; //form
        $post->save(); //Luuu vao CSDL
        if($post->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'post' => $post
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
                    'post' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if($post == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'post' => null
                ],
                404
            );    
        }
        $post->topic_id = $request->topic_id; //form
        $post->title = $request->title; //form
        $post->slug = Str::of($request->title)->slug('-');
        $post->detail = $request->detail; //form
        //upload image
        $files = $request->image;
        if ($files != null) {
            $extension = $files->getClientOriginalExtension();
            if (in_array($extension, ['jpg', 'png', 'gif', 'webp', 'jpeg'])) {
                $filename = date('YmdHis') . '.' . $extension;
                $post->image = $filename;
                $files->move(public_path('images/post'), $filename);
            }
        }
        //
        $post->type = $request->type; //form
        $post->metakey = $request->metakey; //form
        $post->metadesc = $request->metadesc; //form
        $post->updated_at = date('Y-m-d H:i:s');
        $post->updated_by = 1;
        $post->status = $request->status; //form
        if($post->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'post' => $post
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
                    'post' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $post = Post::find($id);
        if($post == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'post' => null
                ],
                404
            );    
        }
        $post->updated_at = date('Y-m-d H:i:s');
        $post->updated_by = 1;
        $post->status = 0; 
        if($post->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'post' => $post
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $post = Post::find($id);
        if($post == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'post' => null
                ],
                404
            );    
        }
        $post->updated_at = date('Y-m-d H:i:s');
        $post->updated_by = 1;
        $post->status = 2; 
        if($post->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'post' => $post
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        if($post == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'post' => null
                ],
               404 
            );    
        }
        if($post->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'post' => $post
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
                    'post' => null
                ],
                422
            );    
        }
    }

}
