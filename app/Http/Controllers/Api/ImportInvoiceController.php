<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImportInvoice;

class ImportInvoiceController extends Controller
{
    public function importInvoice(Request $request)
{
    // Extract the main import invoice details
    $invoiceData = $request->importInvoice;
    
    // Create a new ImportInvoice instance
    $importInvoice = new ImportInvoice();
    $importInvoice->user_id = $invoiceData['user_id'];
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
            ->select('id', 'name', 'slug', 'status', 'image' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_import_invoice.note', 'like', '%' . $key . '%');
            });
        }
        $total = ImportInvoice::where('status', '!=', 0)->count();
        $invoices = $query->paginate(5);
        $total = $invoices->total();
        $trash = ImportInvoice::where('status', '=', 0)->count();
        $publish = ImportInvoice::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'invoices' => $invoices,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
        ];
        return response()->json($result,200);
    }

    public function index()
    {
        $query = ImportInvoice::where('status', '!=', 0)
        ->orderBy('created_at', 'DESC')
        ->select('id', 'name', 'slug', 'status', 'image' );
        if ($condition->input('keySearch') != null ) {
            $key = $condition->input('keySearch');
            $query->where(function ($query) use ($key) {
                $query->where('db_invoice.name', 'like', '%' . $key . '%');
            });
        }
        $invoicesAll = $query->get(); 
        $total = $query->count();
        $invoices = $query->paginate(5);
        $total = $invoices->total();
        $trash = ImportInvoice::where('status', '=', 0)->count();
        $publish = ImportInvoice::where('status', '=', 1)->count();
        $result = [
            'status' => true, 
            'message' => 'Tải dữ liệu thành công',
            'invoices' => $invoices,
            'total' => $total,
            'publish' => $publish,
            'trash' => $trash,
            'invoicesAll' => $invoicesAll,
        ];
        return response()->json($result,200);

    }

    public function index()
    {
        $invoices = ImportInvoice::where('status', '!=', 0)
            ->orderBy('created_at', 'DESC')
            ->select('id', 'invoice_id', 'status')
            ->paginate(5);
        $total = ImportInvoice::where('status', '!=', 0)->count();
        return response()->json(
            [
                'status' => true, 
                'message' => 'Tải dữ liệu thành công',
                'invoices' => $invoices,
                'total' => $total,
            ],
            200
        );
    }
    public function show($id)
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
        else{
            return response()->json(
                [   
                    'status' => true, 
                    'message' => 'Tải dữ liệu thành công', 
                    'invoice' => $invoice
                ],
                200
            );    
        }
    }

    public function store(Request $request)
    {
        $invoice = new invoiceImport();
        $invoice->invoice_id = $request->invoice_id;
        $invoice->price_root = $request->price_root; //form
        $invoice->pty = $request->pty; //form
        $invoice->created_at = date('Y-m-d H:i:s');
        $invoice->created_by = 1;
        if($invoice->save())//Luuu vao CSDL
        {
            return response()->json(
                [
                    'status' => true, 
                    'message' => 'Thành công', 
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
        $invoice->pty = $request->pty; //form
        $invoice->price_root = $request->price_root; //form
        $invoice->updated_at = date('Y-m-d H:i:s');
        $invoice->updated_by = 1;
        $invoice->status = $request->status; //form
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
    
}
