<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyEmail;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Models\CartItem;

class OrderController extends Controller
{
    public function getUSDRate()
    {
        $client = new Client();
        try {
            $response = $client->get('https://v6.exchangerate-api.com/v6/55281f5fd4dfce1a24054035/latest/USD');

            $rates = json_decode($response->getBody(), true);

            if (isset($rates['conversion_rates']['USD']) && isset($rates['conversion_rates']['VND'])) {
                $usdToVnd = $rates['conversion_rates']['VND'];
                $vndToUsd = 1 / $usdToVnd;
                return response()->json(['vnd_to_usd' => $vndToUsd, 'usd_to_vnd' => $usdToVnd]);
            }

            return response()->json(['error' => 'Unable to fetch exchange rates'], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function doCheckout(Request $request)
    {  
        $orderData = $request->order;
        $ListCart = $request->ListCart;

        $order = new Order();
        $order->user_id = $orderData['user_id']; 
        $order->name = $orderData['name']; 
        $order->phone = $orderData['phone']; 
        $order->email = $orderData['email']; 
        $order->address = $orderData['address']; 
        $order->note = $orderData['note']; 
        $order->created_at = now();
        $order->created_by = $orderData['user_id'];
        $order->save();
    
        foreach ($ListCart as $item) {
            DB::table('db_orderdetail')->insert([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'price' => $item['price'],
                'qty' => $item['quantity'],
                'price_root' => $item['cost'],
                'created_at' => now(),
            ]);
            CartItem::where('id', '=', $item['id'])->delete();
        }
        
        Mail::to($order->email)->send(new MyEmail($order));
    
        return response()->json([
            'status' => true,
            'message' => 'Đặt hàng thành công',
            'order' => $order,
        ], 200);
    }
    // public function getUSDRate()
    // {
    //     $client = new Client();
    //     $response = $client->get('https://v6.exchangerate-api.com/v6/55281f5fd4dfce1a24054035/latest/USD', [
    //         'query' => [
    //             'app_id' => '55281f5fd4dfce1a24054035',
    //             'symbols' => 'USD,VND'
    //         ]
    //     ]);

    //     $rates = json_decode($response->getBody(), true);

    //     if (isset($rates['rates']['USD']) && isset($rates['rates']['VND'])) {
    //         $usdToVnd = $rates['rates']['VND'];
    //         $vndToUsd = 1 / $usdToVnd;
    //         return response()->json(['vnd_to_usd' => $vndToUsd, 'usd_to_vnd' => $usdToVnd]);
    //     }

    //     return response()->json(['error' => 'Unable to fetch exchange rates'], 500);
    // }
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
    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = Order::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = Order::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = Order::where('status', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'user_id', 'phone', 'email', 'created_at', 'status' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_order.name', 'like', '%' . $key . '%');
            });
        }
        $total = Order::where('status', '!=', 0)->count();
        $orders = $query->paginate(5);
        $total = $orders->total();
        $trash = Order::where('status', '=', 0)->count();
        $publish = Order::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'orders' => $orders,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = Order::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'user_id', 'phone', 'email', 'created_at', 'status' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_order.name', 'like', '%' . $key . '%');
            });
        }
        $total = $query->count();
        $orders = $query->paginate(5);
        $trash = Order::where('status', '=', 0)->count();
        $publish = Order::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'orders' => $orders,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);

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
