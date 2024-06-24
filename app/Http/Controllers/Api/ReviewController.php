<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function review_product($product_id)
    {
        $reviews = Review::where([['product_id', '=', $product_id]])
                ->join('db_user', 'db_user.id', '=', 'db_review.user_id')
                ->select('db_review.id', 'db_review.product_id', 'db_review.user_id','db_review.rating', 'db_review.comment','db_user.name as user_name', 'db_review.created_at')
                ->get();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'reviews' => $reviews,
            ],
            200
        );
    }
    public function review_product_user($product_id, $user_id)
    {
        $review = Review::where([['product_id', '=', $product_id], ['user_id', '=', $user_id]])->first();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'review' => $review,
            ],
            200
        );
    }
    public function index()
    {
        $reviews = Review::orderBy('created_at', 'DESC')->get();
        return response()->json(
            [
                'success' => true, 
                'message' => 'Tải dữ liệu thành công',
                'reviews' => $reviews,
            ],
            200
        );
    }
    public function show($id)
    {
        if(is_numeric($id)){
            $review = Review::find($id);
        }
        else{
            $review = Review::where('slug', $id)->first();
        }
        return response()->json(
            ['success' => true, 'message' => 'Tải dữ liệu thành công', 'review' => $review],
            200
        );
    }
    public function store(Request $request)
    {
        $review = new Review();
        $review->user_id = $request->user_id; //form
        $review->product_id = $request->product_id; //form
        $review->rating = $request->rating; //form
        $review->comment = $request->comment; //form
        $review->created_at = date('Y-m-d H:i:s');
        $review->created_by = 1;
        $review->save(); //Luuu vao CSDL
        return response()->json(
            [
                'success' => true, 
                'message' => 'Thành công', 
                'review' => $review,
            ],
            201
        );
    }
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Xóa thành công',
                'review' => null
            ],
            200
        );
    }

}
