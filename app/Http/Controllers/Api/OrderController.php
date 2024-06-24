<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyEmail;

class OrderController extends Controller
{
    public function doCheckout(Request $request)
    {  
        $tam = $request->order;
        $order = new Order();
        $order->user_id = $tam['user_id']; //form
        $order->name = $tam['name']; //form
        $order->phone = $tam['phone']; //form
        $order->email = $tam['email']; //form
        $order->address = $tam['address']; //form
        $order->note = $tam['note']; //form
        $order->created_at = date('Y-m-d H:i:s');
        $order->created_by = 1;
        $order->status = 2; //form
        $order->save();

       foreach($request->input('ListCart')  as  $row){
            $orderDetail= new OrderDetail();
            $orderDetail->order_id= $order->id;
            $orderDetail->product_id=$row['id'];
            $orderDetail->price=$row['price'];
            $orderDetail->qty=$row['quantity'];
            $orderDetail->save();
       }

       Mail::mailer('smtp')->to($order->email)->send(new MyEmail($order));

       return response()->json(
        [
            'status' => true,
            'message' => 'Đặt hàng thành công',
            'order' => null,
        ],
        200
    );
    }

    public function order_userId($user_id)
    {
        $args = [
            ['user_id', '=', $user_id],
            // ['status', '=', 1]
        ];
        $order = Order::where($args)->orderBy('created_at', 'DESC')->first();
        return response()->json(
            [
                'success' => true,
                'message' => 'Tải dữ liệu thành công',
                'order' => $order
            ],
            200
        );
    }
    public function changeStatus($id)
    {
        $order = Order::find($id);
        if($order == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'order' => null
                ],
                404
            );    
        }
        $order->updated_at = date('Y-m-d H:i:s');
        $order->updated_by = 1;
        $order->status = ($order->status == 1) ? 2 : 1; //form
        if($order->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'order' => $order
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
                    'order' => null
                ],
                422
            );
        }
    }
    public function trash()
    {
        $orders = Order::where('status', '=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'user_id', 'phone', 'email', 'created_at', 'status' )
        ->paginate(5);
        $total = Order::where('status', '!=', 0)->count();
        $publish = Order::where('status', '=', 1)->count();
        $trash = Order::where('status', '=', 0)->count();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'orders' => $orders,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }

    public function index()
    {
        $orders = Order::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'user_id', 'phone', 'email', 'created_at', 'status' )
        ->paginate(5);
        $total = Order::where('status', '!=', 0)->count();
        $publish = Order::where('status', '=', 1)->count();
        $trash = Order::where('status', '=', 0)->count();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'orders' => $orders,
                'total' => $total,
                'publish' => $publish,
            'trash' => $trash,
            ],
            200
        );
    }
    public function show($id)
    {
        $orders = array();
        $orderDetail=OrderDetail::where('order_id', $id)->get();
        foreach($orderDetail as $row)
        {
            $order = order::find($row->order_id);
            if($order != null)
                $order["quantity"] = $row->qty;
                array_push($orders, $order);
        }
        $order = Order::find($id);
        return response()->json(
            ['success' => true, 
             'message' => 'Tải dữ liệu thành công', 
             'order' => $order,
             'orders' => $orders
            ],
            200
        );
    }
    public function store(Request $request)
    {
        $order = new Order();
        $order->user_id = $request->user_id; //form
        $order->name = $request->name; //form
        $order->phone = $request->phone; //form
        $order->email = $request->email; //form
        $order->address = $request->address; //form
        $order->note = $request->note; //form
        $order->created_at = date('Y-m-d H:i:s');
        $order->created_by = 1;
        $order->status = $request->status; //form
        $order->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'order' => $order
            ],
            201
        );
    }
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $order->user_id = $request->user_id; //form
        $order->name = $request->name; //form
        $order->phone = $request->phone; //form
        $order->email = $request->email; //form
        $order->address = $request->address; //form
        $order->note = $request->note; //form
        $order->updated_at = date('Y-m-d H:i:s');
        $order->updated_by = 1;
        $order->status = $request->status; //form
        $order->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'order' => $order
            ],
            200
        );
    }
    public function delete($id)
    {
        $order = Order::find($id);
        if($order == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'order' => null
                ],
                404
            );    
        }
        $order->updated_at = date('Y-m-d H:i:s');
        $order->updated_by = 1;
        $order->status = 0; 
        if($order->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'order' => $order
                ],
                201
            );    
        }
    }
    public function restore($id)
    {
        $order = Order::find($id);
        if($order == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'order' => null
                ],
                404
            );    
        }
        $order->updated_at = date('Y-m-d H:i:s');
        $order->updated_by = 1;
        $order->status = 2; 
        if($order->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Khôi phục thành công', 
                    'order' => $order
                ],
                201
            );    
        }
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Xóa thành công',
                'order' => null
            ],
            200
        );
    }

}
