<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Carbon\Carbon;

class ContactController extends Controller
{
    public function changeStatus($id)
    {
        $contact = Contact::find($id);
        if($contact == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'contact' => null
                ],
                404
            );    
        }
        $contact->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $contact->updated_by = 1;
        $contact->status = ($contact->status == 1) ? 2 : 1; //form
        if($contact->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'contact' => $contact
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
                    'contact' => null
                ],
                422
            );
        }
    }
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Contact::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Contact::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = Contact::where('db_contact.status', '=', 0)
            ->leftJoin('db_user', 'db_contact.user_id', '=', 'db_user.id')
            ->orderBy('created_at', 'DESC')
            ->select('db_contact.id', 'db_contact.name', 'db_contact.phone', 'db_contact.email', 'db_contact.title', 'db_contact.status', 'db_user.image as image');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_contact.name', 'like', '%' . $key . '%');
            });
        }
        $total = Contact::where('status', '!=', 0)->count();
        $contacts = $query->paginate(5);
        $total = $contacts->total();
        $trash = Contact::where('status', '=', 0)->count();
        $publish = Contact::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'contacts' => $contacts,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = Contact::where('db_contact.status', '!=', 0)
        ->leftJoin('db_user', 'db_contact.user_id', '=', 'db_user.id')
        ->orderBy('db_contact.created_at', 'DESC')
        ->select('db_contact.id', 'db_contact.name', 'db_contact.phone', 'db_contact.email', 'db_contact.title','db_contact.content', 'db_contact.status', 'db_user.image as image');
        $total = $query->count();
        $contacts = $query->paginate(5);
        $trash = Contact::where('status', '=', 0)->count();
        $publish = Contact::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'contacts' => $contacts,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);

    }

    public function show($id)
    {
        $contact = Contact::find($id);
        return response()->json(
            ['status' => true, 'message' => 'Tải dữ liệu thành công', 'contact' => $contact],
            200
        );
    }
    public function store(Request $request)
    {
        $contact = new Contact();
        $contact->user_id = $request->user_id; //form
        $contact->name = $request->name; //form
        $contact->email = $request->email; //form
        $contact->phone = $request->phone; //form
        $contact->title = $request->title; //form
        $contact->content = $request->content; //form
        $contact->replay_id = $request->replay_id; //form
        $contact->created_at = Carbon::now('Asia/Ho_Chi_Minh');
        $contact->status = $request->status; //form
        if($contact->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
                    'contact' => $contact
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
                    'contact' => null
                ],
                422
            );
        }
    }
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);
        if($contact == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'contact' => null
                ],
                404
            );    
        }
        $contact->user_id = $request->user_id; //form
        $contact->name = $request->name; //form
        $contact->email = $request->email; //form
        $contact->phone = $request->phone; //form
        $contact->title = $request->title; //form
        $contact->content = $request->content; //form
        $contact->replay_id = $request->replay_id; //form
        $contact->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $contact->updated_by = 1;
        $contact->status = $request->status; //form
        if($contact->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'contact' => $contact
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
                    'contact' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $contact = Contact::find($id);
        if($contact == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'contact' => null
                ],
                404
            );    
        }
        $contact->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $contact->updated_by = 1;
        $contact->status = 0; 
        if($contact->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'contact' => $contact
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $contact = Contact::find($id);
        if($contact == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'contact' => null
                ],
                404
            );    
        }
        $contact->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $contact->updated_by = 1;
        $contact->status = 2; 
        if($contact->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'contact' => $contact
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $contact = contact::findOrFail($id);
        if($contact == null)
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'contact' => null
                ],
               404 
            );    
        }
        if($contact->delete())
        {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Xóa thành công',
                    'contact' => $contact
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
                    'contact' => null
                ],
                422
            );    
        }
    }

}
