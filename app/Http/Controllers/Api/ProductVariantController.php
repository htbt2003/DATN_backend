<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductVariantController extends Controller
{
    public function store(Request $request)
    {
        try {
            $product_id = $request['product_id'];
            $variants = $request['variants'];

            foreach ($variants as $variant) {
                $product = new ProductVariant();
                $product->product_id = $product_id;
                $product->SKU = $variant['SKU'] ?? null;
                $product->name = $variant['name'];
                $product->price = $variant['price'];

                if ($product->save()) {
                    foreach ($variant['values'] as $value) {
                        DB::table('db_product_variant_value')->insert([
                            'product_variant_id' => $product->id,
                            'product_attribute_value_id' => $value['product_attribute_value_id'],
                        ]);
                    }
                }
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Thành công',
                    'product' => null,
                ],
                200 // Changed status code to 200 for success
            );
        } catch (Exception $e) {
            // Handle any errors
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                ],
                500 // Internal Server Error status code
            );
        }
    }
    public function update(Request $request)
    {
        try {
            $product_id = $request['product_id'];
            //tạo mới
            if($request->has('variantNews')){
                foreach ($request['variantNews'] as $variant) {
                    $product = new ProductVariant();
                    $product->product_id = $product_id;
                    $product->SKU = $variant['SKU'] ?? null;
                    $product->name = $variant['name'];
                    $product->price = $variant['price'];
    
                    if ($product->save()) {
                        foreach ($variant['values'] as $value) {
                            DB::table('db_product_variant_value')->insert([
                                'product_variant_id' => $product->id,
                                'product_attribute_value_id' => $value['product_attribute_value_id'],
                            ]);
                        }
                    }
                }    
            }
            //cập nhật
            if($request->has('variantUpdates')){
                foreach ($request['variantUpdates'] as $variant) {
                    $product = ProductVariant::find($variant['id']);
                    $product->product_id = $product_id;
                    $product->SKU = $variant['SKU'] ?? null;
                    $product->name = $variant['name'];
                    $product->price = $variant['price'];
                    $product->save();
                }
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Thành công',
                    'product' => null,
                ],
                200 // Changed status code to 200 for success
            );
        } catch (Exception $e) {
            // Handle any errors
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                ],
                500 // Internal Server Error status code
            );
        }
    }

}
