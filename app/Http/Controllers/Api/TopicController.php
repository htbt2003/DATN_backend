<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use Illuminate\Support\Str;


class TopicController extends Controller
{
    public function topic_list($parent_id = 0)
    {
        $args = [
            ['parent_id', '=', $parent_id],
            ['status', '=', 1]
        ];
        $topics = Topic::where($args)->get();
        return response()->json(
            [
                'success' => true,
                'message' => 'Tải dữ liệu thành công',
                'topics' => $topics
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $topic = Topic::find($id);
        if($topic == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'topic' => null
                ],
                404
            );    
        }
        $topic->updated_at = date('Y-m-d H:i:s');
        $topic->updated_by = 1;
        $topic->status = ($topic->status == 1) ? 2 : 1; //form
        if($topic->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'topic' => $topic
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
                    'topic' => null
                ],
                422
            );
        }
    }
    public function trash()
    {
        $topics = Topic::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'status')
            ->paginate(5);
        $total = Topic::where('status', '!=', 0)->count();
        $publish = Topic::where('status', '=', 1)->count();
        $trash = Topic::where('status', '=', 0)->count();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'topics' => $topics,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }

    public function index()
    {
        $topics = Topic::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'status')
            ->paginate(5);
        $topicsAll = Topic::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'name', 'slug', 'status')
            ->get();
        $total = Topic::where('status', '!=', 0)->count();
        $publish = Topic::where('status', '=', 1)->count();
        $trash = Topic::where('status', '=', 0)->count();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'topics' => $topics,
                'topicsAll' => $topicsAll,
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
            $topic = Topic::find($id);
        }
        else{
            $topic = Topic::where('slug', $id)->first();
        }
        return response()->json(
            ['success' => true, 'message' => 'Tải dữ liệu thành công', 'topic' => $topic],
            200
        );
    }
    public function store(Request $request)
    {
        $topic = new Topic();
        $topic->name = $request->name; //form
        $topic->slug = Str::of($request->name)->slug('-');
        $topic->parent_id = $request->parent_id; //form
        $topic->metakey = $request->metakey; //form
        $topic->metadesc = $request->metadesc; //form
        $topic->created_at = date('Y-m-d H:i:s');
        $topic->created_by = 1;
        $topic->status = $request->status; //form
        $topic->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'topic' => $topic
            ],
            201
        );
    }
    public function update(Request $request, $id)
    {
        $topic = Topic::find($id);
        $topic->name = $request->name; //form
        $topic->slug = Str::of($request->name)->slug('-');
        $topic->parent_id = $request->parent_id; //form
        $topic->metakey = $request->metakey; //form
        $topic->metadesc = $request->metadesc; //form
        $topic->updated_at = date('Y-m-d H:i:s');
        $topic->updated_by = 1;
        $topic->status = $request->status; //form
        $topic->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'topic' => $topic
            ],
            200
        );
    }
    public function delete($id)
    {
        $topic = Topic::find($id);
        if($topic == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'topic' => null
                ],
                404
            );    
        }
        $topic->updated_at = date('Y-m-d H:i:s');
        $topic->updated_by = 1;
        $topic->status = 0; 
        if($topic->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'topic' => $topic
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $topic = Topic::find($id);
        if($topic == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'topic' => null
                ],
                404
            );    
        }
        $topic->updated_at = date('Y-m-d H:i:s');
        $topic->updated_by = 1;
        $topic->status = 2; 
        if($topic->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'topic' => $topic
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $topic = Topic::findOrFail($id);
        $topic->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Xóa thành công',
                'topic' => null
            ],
            200
        );
    }

}
