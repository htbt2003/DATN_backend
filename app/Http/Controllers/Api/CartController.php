<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartController extends Controller
{
    public function list($deviceId)
    {
        // Lấy giỏ hàng dựa trên deviceId
        $cart = Cart::where('deviceId', '=', $deviceId)->first();

        // Kiểm tra nếu giỏ hàng không tồn tại
        if (!$cart) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Không tìm thấy giỏ hàng',
                ],
                404
            );
        }

        // Lấy danh sách các mục trong giỏ hàng
        $ListCart = CartItem::where('cart_id', '=', $cart->id)
                    ->join('db_product as p', 'db_cart_item.product_id', '=', 'p.id')
                    ->leftJoin('db_productsale', function ($join) {
                        $join->on('p.id', '=', 'db_productsale.product_id')
                            ->orOn('db_cart_item.variant_id', '=', 'db_productsale.variant_id')
                            ->where('db_productsale.date_begin', '<=', Carbon::now())
                            ->where('db_productsale.date_end', '>=', Carbon::now());
                    })
                    ->select([
                        'db_cart_item.id', 
                        'db_cart_item.variant_id', 
                        'db_cart_item.status', 
                        'p.price', 
                        'db_productsale.price_sale', 
                        'db_cart_item.quantity', 
                        'p.cost', 
                        'p.name', 
                        'p.image', 
                        'db_cart_item.product_id'
                    ])
                    ->get();

        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'ListCart' => $ListCart,
            ],
            200
        );
    }

    private function ckeck_inventory($productId, $variantId, $quantityToAdd)
    {
        // Truy vấn tổng số lượng tồn kho của sản phẩm và biến thể (nếu có)
        $productstoreQuery = ProductStore::where('product_id', $productId)
            ->select(DB::raw('SUM(qty) as sum_qty_store'));

        // Truy vấn tổng số lượng sản phẩm đã bán
        $orderdetailQuery = OrderDetail::where('product_id', $productId)
            ->select(DB::raw('SUM(db_orderdetail.qty) as sum_qty_selled'))
            ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
            ->whereNotIn('db_order.status', [0, 5]); // Loại trừ các trạng thái 0 và 5

        if ($variantId) {
            $productstoreQuery->where('variant_id', $variantId)
                ->groupBy('product_id', 'variant_id');
            $orderdetailQuery->where('variant_id', $variantId)
                ->groupBy('product_id', 'variant_id');
        } else {
            $productstoreQuery->groupBy('product_id');
            $orderdetailQuery->groupBy('product_id');
        }

        $productstore = $productstoreQuery->first();
        $orderdetail = $orderdetailQuery->first();

        $sum_qty_store = $productstore ? $productstore->sum_qty_store : 0;
        $sum_qty_selled = $orderdetail ? $orderdetail->sum_qty_selled : 0;

        $inventory = $sum_qty_store - $sum_qty_selled;

        if ($quantityToAdd > $inventory) {
            return response()->json(['status' => false, 'message' => 'Số lượng tồn kho không đủ', 'inventory' => $inventory]);
        }

        return null;
    }

    // private function ckeck_inventory($productId, $variantId, $quantityToAdd)
    // {
    //     $productstore = ProductStore::where('product_id', '=', $productId)
    //     ->select(DB::raw('SUM(qty) as sum_qty_store'));
    //     $orderdetail = OrderDetail::where('product_id', '=', $productId)
    //             ->select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
    //             ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
    //             ->whereNotIn('db_order.status', [0, 5]); 
    //     if ($variantId) {
    //         $productstore->where('variant_id', '=', $variantId)
    //             ->groupBy('product_id', 'variant_id')
    //             ->first();
    //         $orderdetail = OrderDetail::where('variant_id', '=', $variantId)
    //             ->groupBy('product_id', 'variant_id')
    //             ->first();

    //     } else {
    //         $productstore->groupBy('product_id')->first();
    //         $orderdetail->groupBy('product_id', 'variant_id')->first();
    //     }

    //     $inventory = $productstore->sum_qty_store - $orderdetail->sum_qty_selled;

    //     if ($quantityToAdd > $inventory) {
    //         return response()->json(['message' => 'Số lượng tồn kho không đủ'], 400);
    //     }
    //     return null;
    // }
    public function add(Request $request)
    {
        $deviceId = $request->input('deviceId');
        $variantId = $request->input('variant_id');
        $productId = $request->input('product_id');
        $quantityToAdd = $request->input('quantity');
        // $product = Product::findOrFail($productId);
        // $price = $product->price;
        // $cost = $product->cost;
        //Kiểm tra sô lượng tồn kho----------------
        $inventoryCheck = $this->ckeck_inventory($productId, $variantId, $quantityToAdd);
        if ($inventoryCheck) {
            return $inventoryCheck;
        }
        //lấy ra id giỏ hàng nếu chưa tồn tại giỏ hàng thì tạo mới
        $cart = Cart::firstOrCreate(
            ['deviceId' => $deviceId]
        );
        //thêm vào chi tiết giỏ hàng nếu đủ số lượng trong kho----------------
        ///kiểm tra là sản phẩm biến thể
        if ($variantId) {
            $cartItem = CartItem::where([['cart_id', $cart->id], ['product_id', $productId], ['variant_id', $variantId]])->first();
            //nếu tồn tại thì tăng số lượng
            if ($cartItem) {
                $cartItem->quantity += $quantityToAdd;
                $cartItem->save();
            } 
            //ngược lại thêm mới
            else {
                // $variant = ProductVariant::findOrFail($variantId);
                // $price = $variant->price;
                // $cost = $variant->cost;
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantityToAdd,
                    // 'price' => $price,
                    // 'cost' => $cost,
                ]);
            }
        } 
        //ngược lại kiểm tra là sản phẩm đơn
        else {
            $cartItem = CartItem::where([['cart_id', $cart->id], ['product_id', $productId]])->first();   
            if ($cartItem) {
                $cartItem->quantity += $quantityToAdd;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantityToAdd,
                    // 'price' => $price,
                    // 'cost' => $cost,
                ]);
            }
        }
    
        return response()->json(['status' => true, 'message' => 'Thêm vào giỏ hàng thành công']);
    }
    public function selected($id)
    {   
        $cartItem = CartItem::find($id);        
        if ($cartItem) {
            $cartItem->status = ($cartItem->status == 0) ? 1 : 0;
            $cartItem->save();
            return response()->json(['status' => true, 'message' => 'Cập nhật số lượng sản phẩm thành công']);
        } 
        else {
            return response()->json(['status' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    }
    public function list_selected($deviceId)
    {   
        $cart = Cart::where('deviceId', '=', $deviceId)->first();
        $ListCart = CartItem::where([['db_cart_item.status','=', 1],['db_cart_item.cart_id','=', $cart->id]])
        ->join('db_product as p', 'db_cart_item.product_id', '=', 'p.id')
        ->leftJoin('db_productsale', function ($join) {
            $join->on('p.id', '=', 'db_productsale.product_id')
                ->orOn('db_cart_item.variant_id', '=', 'db_productsale.variant_id')
                ->where('db_productsale.date_begin', '<=', Carbon::now())
                ->where('db_productsale.date_end', '>=', Carbon::now());
        })
        ->select('db_cart_item.id', 'db_cart_item.variant_id', 'db_cart_item.product_id', 'p.price', 'db_productsale.price_sale','db_cart_item.quantity', 'p.cost', 'p.name', 'p.image', 'db_cart_item.status',)
        ->get();


        if ($ListCart) {
            return response()->json(['status' => true, 'message' => 'Cập nhật số lượng sản phẩm thành công', 'ListCart' => $ListCart,]);
        } 
        else {
            return response()->json(['status' => false, 'message' => 'Không có sản phẩm']);
        }
    }

    public function update_qty($id, $quantity)
    {   
        $cartItem = CartItem::find($id);
        $inventoryCheck = $this->ckeck_inventory($cartItem->product_id, $cartItem->variant_id, $quantity);
        if ($inventoryCheck) {
            return $inventoryCheck;
        }
        
        if ($cartItem) {
            $cartItem->quantity = $quantity;
            $cartItem->save();
            return response()->json(['status' => true, 'message' => 'Cập nhật số lượng sản phẩm thành công']);
        } 
        else {
            return response()->json(['status' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    }
    public function increase($id)
    {    
        $cartItem = CartItem::find($id);

        $inventoryCheck = $this->ckeck_inventory($cartItem->product_id, $cartItem->variant_id, $cartItem->quantity+1);
        if ($inventoryCheck) {
            return $inventoryCheck;
        }
        
        if ($cartItem) {
            $cartItem->quantity += 1;
            $cartItem->save();
            return response()->json(['message' => 'Cập nhật số lượng sản phẩm thành công']);
        } 
        else {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    }
    public function decrease($id)
    {    
        $cartItem = CartItem::find($id);
    
        if ($cartItem) {
            $cartItem->quantity -= 1;
            $cartItem->save();
            if ($cartItem->quantity > 0) {
                $cartItem->save();
                return response()->json(['message' => 'Cập nhật số lượng sản phẩm thành công']);
            } 
            else {
                $cartItem->delete();
                return response()->json(['message' => 'Sản phẩm đã được xóa khỏi giỏ hàng']);
            }
        } 
        else {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    }
    public function delete($id)
    {    
        $cartItem = CartItem::where('id', '=', $id)->delete();
    
        if ($cartItem == 1) {
            return response()->json(['message' => 'Cập nhật số lượng sản phẩm thành công']);
        } 
        else {
            return response()->json(['message' => 'Sản phẩm không tồn tại trong giỏ hàng']);
        }
    }

}
