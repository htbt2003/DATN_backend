<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportInvoice;
use App\Models\OrderDetail;
use App\Models\ProductStore;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class ImportInvoiceController extends Controller
{
    public function importInvoice(Request $request)
{
    // Extract the main import invoice details
    $invoiceData = $request->importInvoice;
    
    // Create a new ImportInvoice instance
    $importInvoice = new ImportInvoice();
    $order->name = $orderData['name']; 
    $order->phone = $orderData['phone']; 
    $order->email = $orderData['email']; 
    $order->address = $orderData['address']; 
    $importInvoice->note = $invoiceData['note'];
    $importInvoice->created_at = now();
    $importInvoice->created_by = $invoiceData['user_id'];
    $importInvoice->save();

    // Loop through the list of invoices and save each one
    foreach ($request->Listinvoices as $invoice) {
        DB::table('import_invoice_details')->insert([
            'import_invoice_id' => $importInvoice->id,
            'invoice_id' => $invoice['invoice_id'],
            'variant_id' => $invoice['variant_id'],
            'quantity' => $invoice['quantity'],
            'price_root' => $invoice['price_root'],
            'created_at' => now(),
        ]);
    }

    // Return a response
    return response()->json([
        'status' => true,
        'message' => 'Import invoice created successfully',
        'importInvoice' => $importInvoice,
    ], 200);
}

    public function action_trash(Request $request)
    {
        $listId = $request->input('listId');

        $result = ImportInvoice::whereIn('id', $listId)->update(['status' => 0]);

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Không có dòng nào được cập nhật'], 404);
        }
    }
    public function action_destroy(Request $request)
    {
        $listId = $request->input('listId');

        $result = ImportInvoice::whereIn('id', $listId)->delete();

        if ($result > 0) {
            return response()->json(['message' => 'Thành công'], 200);
        } else {
            return response()->json(['message' => 'Thất bại'], 404);
        }
    }

    public function trash(Request $condition)
    {
        $query = ImportInvoice::where('status', '=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'created_by', 'name', 'phone', 'email', 'created_at', 'status');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_invoice.name', 'like', '%' . $key . '%');
            });
        }
        if ($condition->input('date') != null) {
            $query->whereDate('created_at', $condition->input('date'));
        }

        $total = ImportInvoice::where('status', '!=', 0)->count();
        $importInvoices = $query->paginate(5);
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'importInvoices' => $importInvoices,
            'total' => $total,
        ];
        return response()->json($result,200);
    }

    public function index(Request $condition)
    {
        $query = ImportInvoice::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'created_by', 'name', 'phone', 'email', 'created_at', 'status');
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_invoice.name', 'like', '%' . $key . '%');
            });
        }
        if ($condition->input('date') != null) {
            $query->whereDate('created_at', $condition->input('date'));
        }

        $invoicesAll = $query->get(); 
        $total = $query->count();
        $importInvoices = $query->paginate(5);
        $trash = ImportInvoice::where('status', '=', 0)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'importInvoices' => $importInvoices,
            'total' => $total,
            'trash' => $trash,
            'invoicesAll' => $invoicesAll,
        ];
        return response()->json($result,200);

    }
    public function show($id)
    {
        // $orders = array();
        $orderDetail=ProductStore::where([['import_invoice_id','=', $id],['db_productstore.status','=', 1]])
            ->join('db_product as p', 'db_productstore.product_id', '=', 'p.id')
            ->select(
                'db_productstore.id',
                'db_productstore.price_root',
                'db_productstore.qty',
                'db_productstore.created_at',
                'db_productstore.variant_id',
                'p.name',
                'p.image', 
                'p.price as price_sell')
            ->get();
        $order = ImportInvoice::find($id);
        return response()->json(
            ['success' => true, 
             'message' => 'Tải dữ liệu thành công', 
             'order' => $order,
             'orderDetails' => $orderDetail,
            ],
            200
        );
    }

    // public function show($id)
    // {
    //     $invoice = ImportInvoice::find($id);
    //     if($invoice == null)//Luuu vao CSDL
    //     {
    //         return response()->json(
    //             [
    //                 'status' => false, 
    //                 'message' => 'Không tìm thấy dữ liệu', 
    //                 'invoice' => null
    //             ],
    //             404
    //         );    
    //     }
    //     else{
    //         return response()->json(
    //             [   
    //                 'status' => true, 
    //                 'message' => 'Tải dữ liệu thành công', 
    //                 'invoice' => $invoice
    //             ],
    //             200
    //         );    
    //     }
    // }
    public function store(Request $request)
    {
        $invoiceData = $request['order'];
    
        // Create a new ImportInvoice instance
        $importInvoice = new ImportInvoice();
        $importInvoice->name = $invoiceData['name'];
        $importInvoice->phone = $invoiceData['phone'];
        $importInvoice->email = $invoiceData['email'] ?? null;
        $importInvoice->address = $invoiceData['address'];
        $importInvoice->note = $invoiceData['note'] ?? null;
        $importInvoice->created_at = now();
        $importInvoice->created_by = $invoiceData['user_id'];
        $importInvoice->save();
    
        if ($importInvoice->save()) {
            foreach ($request->Listproducts as $invoice) {
                DB::table('db_productstore')->insert([
                    'import_invoice_id' => $importInvoice->id,
                    'variant_id' => $invoice['variant_id'] ?? null,
                    'product_id' => $invoice['product_id'],
                    'qty' => $invoice['qty'],
                    'price_root' => $invoice['price_root'],
                    'created_at' => now(),
                ]);
    
                if ($invoice['variant_id'] != null) {
                    $product = ProductVariant::select('cost')
                        ->where('id', '=', $invoice['variant_id'])
                        ->first();
    
                     $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                        ->where('status', '=', 1)
                        ->where('product_id', '=', $invoice['product_id'])
                        ->where('variant_id', $invoice['variant_id'])
                        ->groupBy('product_id', 'variant_id')
                        ->first();
    
                    $orderdetail = OrderDetail::select('product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                        ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                        ->whereNotIn('db_order.status', [0, 5, 6])
                        ->where('db_orderdetail.product_id', '=', $invoice['product_id'])
                        ->where('db_orderdetail.variant_id', $invoice['variant_id'])
                        ->groupBy('product_id', 'variant_id')
                        ->first();
    
                    $cost = $product->cost ?? 0;
                    $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);
    
                    $newCost = (($cost * $qty_inventory) + ($invoice['price_root'] * $invoice['qty'])) / ($qty_inventory + $invoice['qty']);
                    ProductVariant::where('id', $invoice['variant_id'])->update(['cost' => $newCost]);
                } else {
                    $product = Product::select('cost')
                        ->where('db_product.id', '=', $invoice['product_id'])
                        ->first();
    
                     $productstore = ProductStore::select('product_id', DB::raw('SUM(qty) as sum_qty_store'))
                        ->where('status', '=', 1)
                        ->where('product_id', '=', $invoice['product_id'])
                        ->groupBy('product_id')
                        ->first();
    
                    $orderdetail = OrderDetail::select('db_orderdetail.product_id', DB::raw('SUM(qty) as sum_qty_selled'))
                        ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
                        ->whereNotIn('db_order.status', [0, 5, 6])
                        ->where('db_orderdetail.product_id', '=', $invoice['product_id'])
                        ->groupBy('db_orderdetail.product_id')
                        ->first();
    
                    $cost = $product->cost ?? 0;
                    $qty_inventory = ($productstore->sum_qty_store ?? 0)-($orderdetail->sum_qty_selled ?? 0);
    
                    $newCost = (($cost * $qty_inventory) + ($invoice['price_root'] * $invoice['qty'])) / ($qty_inventory + $invoice['qty']);
                    Product::where('id', $invoice['product_id'])->update(['cost' => $newCost]);
                }
            }
    
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Thành công',
                    'importInvoice' => $importInvoice
                ],
                201
            );
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Thêm không thành công',
                    'invoice' => null
                ],
                422
            );
        }
    }
    
    public function update(Request $request, $id)
    {
        $invoice = ImportInvoice::find($id);
        if($invoice == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Không tìm thấy dữ liệu', 
                    'invoice' => null
                ],
                404
            );    
        }
        $invoice->name = $request['name']; 
        $invoice->phone = $request['phone']; 
        $invoice->email = $request['email']; 
        $invoice->address = $request['address']; 
        $invoice->note = $request['note'];
        $invoice->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $invoice->updated_by = $request['user_id'];
        // $invoice->status = $request->status; //form
        if($invoice->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Cập nhật dữ liệu thành công', 
                    'invoice' => $invoice
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
                    'invoice' => null
                ],
                422
            );
        }
    }
    public function delete($id)
    {
        $invoice = ImportInvoice::find($id);
        if($invoice == null)//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => false, 
                    'message' => 'Đã chuyển vào thùng rác', 
                    'invoice' => null
                ],
                404
            );    
        }
        $invoice->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
        $invoice->updated_by = 1;
        $invoice->status = 0; 
        if($invoice->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Xoá thành công', 
                    'invoice' => $invoice
                ],
                201
            );    
        }
    }

}
