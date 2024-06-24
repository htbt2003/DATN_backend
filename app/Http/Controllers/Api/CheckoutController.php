<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
class CheckoutController extends Controller
{
    public function checkout(Request $request, $listCart)
    {  
        // Cap nhat thong tin khach hang
       $user=User::find(6);
    //    $user->address=$request->address;
    //    $user->phone=$request->phone;
    //    $user->save();
       // Luu thong tin bang Orders
       $order=new Order;
       $order->user_id=$user->id;
       if($request->name == null)
       {
        $order->name=$user->name;
       }
       else{
        $order->name=$request->name;
       }
       if($request->email == null)
       {
        $order->email=$user->email;
       }
       else{
        $order->email=$request->email;
       }
       if($request->address == null)
       {
        $order->address=$user->address;
       }
       else{
        $order->address=$request->address;
       }
       if($request->phone == null)
       {
        $order->phone=$user->phone;
       }
       else{
        $order->phone=$request->phone;
       }
    //    $order->total=Cart::total();
       $order->note=$request->note;
       $order->status=1;
       $order->save();
       // Cap nhat thong tin chi tiet don hang
       foreach($listCart  as $row){
       $orderDetail=new OrderDetail;
       $orderDetail->order_id=$order->id;
       $orderDetail->product_id=$row->id;
       $orderDetail->price=$row->price;
       $orderDetail->pty=$row->qty;
       $orderDetail->save();
       }
    }


}
