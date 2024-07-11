<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDetail;
use App\Models\Order;

class DashboardController extends Controller
{
    $currentDate = Carbon::now('Asia/Ho_Chi_Minh');
    public function filter_date(Request $request)
    {
        $date_begin = $request['date_begin'];
        $date_end = $request['date_end'];
        // $orderdetail = OrderDetail::select('order_id', DB::raw('SUM(price * qty) as total_amount'), DB::raw('SUM(qty*price) as total_qty'))
        //     ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
        //     ->whereNotIn('db_order.status', [5, 6, 7])
        $profits = OrderDetail::
            join('db_order', 'db_order.id', '=', 'db_orderdetail.order_id')
            ->whereNotIn('db_order.status', [5, 6, 7])
            ->select(
                DB::raw('DATE(db_order.created_at) as date')
                DB::raw('DATE(db_order.created_at) as sale_date'),
                DB::raw('SUM(db_orderdetail.qty * db_orderdetail.price) as total_revenue'),
                DB::raw('SUM(db_orderdetail.qty * db_orderdetail.price_root) as total_cost'),
                DB::raw('SUM(db_orderdetail.qty * db_orderdetail.price) - SUM(db_orderdetail.qty * db_orderdetail.price_root) as profit'),
            )
            ->whereBetween('db_order.created_at', [$date_begin, $date_end])
            ->groupBy(DB::raw('DATE(db_order.created_at)'))
            ->orderBy(DB::raw('DATE(db_order.created_at)'))
            ->get();
        return response()->json(
            [
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'profits' => $profits,
            ],
            200
        );
    }
    // private function toltal($type, $date, $total_revenue_profit, $total_expenditure_qty, $total_order, $total_user)
    // {
    //     //tổng doanh thu, lợi nhuận
    //     $total_revenue_profit = OrderDetail::select(
    //         DB::raw('SUM(db_orderdetail.qty*db_orderdetail.price) as total_profit'),
    //         DB::raw('SUM(db_orderdetail.qty*db_orderdetail.price_root) as total_revenue'),
    //         )
    //         ->join('db_order', 'db_orderdetail.order_id', '=', 'db_order.id')
    //         ->whereNotIn('db_order.status', [5, 6, 7])
    //         ->where(DB::raw('$type(o.created_at)'),'=', $date)
    //         ->first();
    //     //tổng chi và số lương nhập kho
    //     $total_expenditure_qty = ProductStore::select(
    //             DB::raw('SUM(db_productstore.qty*db_productstore.price_root) as total_expenditure'),
    //             DB::raw('SUM(db_productstore.qty) as total_qty'),
    //         )
    //         ->join('db_import_invoice', 'db_productstore.import_invoice_id', '=', 'db_import_invoice.id')
    //         ->where('db_import_invoice.status', 1)
    //         ->where(DB::raw('$type(db_import_invoice.created_at)'), '=', $date)
    //         ->first();
    //     //Tổng đơn hàng
    //     $total_order = Order::whereNotIn('status', [7])->count();
    //     //Tổng sản tv mới
    //     $total_user = User::where([
    //         ['status', '!=', 0],
    //         ['roles', '=', 'user'],
    //         [DB::raw('$type(created_at)'), '=', $date]])->count();
        
    // }
    private function total($startDate, $endDate)
    {
        $totals = [];

        // Total revenue and profit
        $total_revenue_profit = OrderDetail::select(
            DB::raw('SUM(od.qty * od.price) as total_profit'),
            DB::raw('SUM(od.qty * od.price_root) as total_revenue')
        )
        ->join('db_order as o', 'od.order_id', '=', 'o.id')
        ->whereNotIn('o.status', [5, 6, 7])
        ->whereBetween('o.created_at', [$startDate, $endDate])
        ->first();

        $totals['total_revenue_profit'] = $total_revenue_profit;

        // Total expenditure and quantity
        $total_expenditure_qty = ProductStore::select(
            DB::raw('SUM(ps.qty * ps.price_root) as total_expenditure'),
            DB::raw('SUM(ps.qty) as total_qty')
        )
        ->join('db_import_invoice as i', 'ps.import_invoice_id', '=', 'i.id')
        ->where('i.status', 1)
        ->whereBetween('i.created_at', [$startDate, $endDate])
        ->first();

        $totals['total_expenditure_qty'] = $total_expenditure_qty;

        // Total orders
        $total_order = Order::whereNotIn('status', [7])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $totals['total_order'] = $total_order;

        // Total new users
        $total_user = User::where([
            ['status', '!=', 0],
            ['roles', '=', 'user']
        ])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        $totals['total_user'] = $total_user;

        return $totals;
    }
    public function filter_option(Request $request)
    {
        $date = $request->input('date');
        $statType = $request->input('stat_type');

        switch ($statType) {
            case 'daily':
                //chart
                $startDate = Carbon::parse($date)->startOfDay();
                $endDate = Carbon::parse($date)->endOfDay();
                $groupBy = DB::raw('HOUR(o.created_at)');
                $selectDate = DB::raw("HOUR_FORMAT(o.created_at, '%h:%i') as stat_date")DB::raw('HOUR(o.created_at) as stat_date');
                break;

            case 'weekly':
                $startDate = Carbon::parse($date)->startOfWeek();
                $endDate = Carbon::parse($date)->endOfWeek();
                $groupBy = DB::raw('DAYNAME(o.created_at)');
                $selectDate = DB::raw("DATE_FORMAT(o.created_at,  '%d/%m/%Y') as stat_date");
                break;

            case 'monthly':
                $startDate = Carbon::parse($date)->startOfMonth();
                $endDate = Carbon::parse($date)->endOfMonth();
                $groupBy = DB::raw('DATE(o.created_at)');
                $selectDate = DB::raw("DATE_FORMAT(o.created_at,  '%d/%m/%Y') as stat_date");
                break;

            case 'yearly':
                $startDate = Carbon::parse($date)->startOfYear();
                $endDate = Carbon::parse($date)->endOfYear();
                $groupBy = DB::raw('MONTH(o.created_at)');
                $selectDate = DB::raw("DATE_FORMAT(o.created_at,  '%m/%Y') as stat_date");
                break;

            default:
                return response()->json(['error' => 'Invalid statistic type'], 400);
        }

        $profits = DB::table('db_orderdetail as od')
            ->join('db_order as o', 'o.id', '=', 'od.order_id')
            ->select(
                $selectDate,
                DB::raw('SUM(od.qty * od.price) as total_revenue'),
                DB::raw('SUM(od.qty * od.price_root) as total_cost'),
                DB::raw('SUM(od.qty * od.price) - SUM(od.qty * od.price_root) as profit')
            )
            ->whereBetween('o.created_at', [$startDate, $endDate])
            ->groupBy($groupBy)
            ->orderBy('stat_date')
            ->get();

        $totals = $this->total($startDate, $endDate);

        return response()->json([
                'status' => true,
                'message' => 'Tải dữ liệu thành công',
                'profits' => $profits,
                'totals' => $totals,
            ],
        );
    }

}
