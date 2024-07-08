<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductStoreController;
use App\Http\Controllers\Api\ProductSaleController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ImportInvoiceController;
use App\Http\Controllers\Api\PromotionController;

//address
Route::get('address_userId/{id?}', [AddressController::class, 'address_userId']);
Route::get('count_address_userId/{id?}', [AddressController::class, 'count_address_userId']);
Route::prefix('address')->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::get('index?page={page}', [AddressController::class, 'index']);
    Route::get('show/{id}', [AddressController::class, 'show']);
    Route::post('store', [AddressController::class, 'store']);
    Route::post('update/{id}', [AddressController::class, 'update']);
    Route::delete('destroy/{id}', [AddressController::class, 'destroy']);
    Route::get('change_status/{id}', [AddressController::class, 'changeStatus']);
    Route::get('delete/{id}', [AddressController::class, 'delete']);
    Route::get('restore/{id}', [AddressController::class, 'restore']);
    Route::get('trash?page={page}', [AddressController::class, 'trash']);
});

Route::post('login', [AuthController::class,'login']);
Route::post('loginFacebook', [AuthController::class,'loginFacebook']);
Route::post('loginGoogle', [AuthController::class,'loginGoogle']);
Route::post('register', [AuthController::class,'register']);


Route::get('brand_home/{limit}', [BrandController::class, 'brand_home']);

Route::get('menu_list/{position}/{parent_id?}', [MenuController::class, 'menu_list']);
Route::get('banner_list/{position}', [BannerController::class, 'banner_list']);
Route::get('category_list/{parent_id?}', [CategoryController::class, 'category_list']);
Route::get('topic_list/{parent_id?}', [TopicController::class, 'topic_list']);

Route::post('updateAccount/{id}', [UserController::class, 'update_account']);

Route::get('product_new/{limit}', [ProductController::class, 'product_new']);
Route::get('product_sale/{limit}', [ProductController::class, 'product_sale']);
Route::get('product_bestSeller/{limit}', [ProductController::class, 'product_bestSeller']);
Route::get('product_home/{limit}/{category_id?}', [ProductController::class, 'product_home']);
Route::get('product_stores', [ProductController::class, 'product_stores']);
Route::get('product_allAction', [ProductController::class, 'product_allAction']);
Route::get('product_category/{category_id}', [ProductController::class, 'product_category']);
Route::get('product_brand/{brand_id}', [ProductController::class, 'product_brand']);
Route::get('product_detail/{id}', [ProductController::class, 'product_detail']);
Route::get('product_other/{id}/{limit}', [ProductController::class, 'product_other']);
Route::get('search', [ProductController::class, 'search']);

Route::get('post_list/{limit}/{type}', [PostController::class, 'post_list']);
Route::get('post_all', [PostController::class, 'post_all']);
Route::get('post_topic/{topic_id}', [PostController::class, 'post_topic']);
Route::get('post_detail/{slug}', [PostController::class, 'post_detail']);
Route::get('post_other/{id}/{limit}', [PostController::class, 'post_other']);
Route::get('post_new', [PostController::class, 'post_new']);

Route::get('page_detail/{slug}', [PageController::class, 'page_detail']);

Route::get('order_userId/{id}', [OrderController::class, 'order_userId']);
Route::post('doCheckout', [OrderController::class, 'doCheckout']);
Route::get('getUSDRate', [OrderController::class, 'getUSDRate']);



Route::get('reviewProduct/{product_id}', [ReviewController::class, 'review_product']);
Route::get('reviewProductUser/{product_id}/{user_id}', [ReviewController::class, 'review_product_user']);
Route::post('review/store', [ReviewController::class, 'store']);
Route::delete('review/destroy/{id}', [ReviewController::class, 'destroy']);

Route::prefix('order')->group(function () {
    Route::get('index', [OrderController::class, 'index']);
    Route::get('show/{id}', [OrderController::class, 'show']);
    Route::post('store', [OrderController::class, 'store']);
    Route::post('update/{id}', [OrderController::class, 'update']);
    Route::delete('destroy/{id}', [OrderController::class, 'destroy']);
    Route::get('change_status/{key}', [OrderController::class, 'changeStatus']);
    Route::get('delete/{key}', [OrderController::class, 'delete']);
    Route::get('restore/{key}', [OrderController::class, 'restore']);
    Route::get('trash', [OrderController::class, 'trash']);
    Route::post('action_trash', [OrderController::class, 'action_trash']);
    Route::post('action_destroy', [OrderController::class, 'action_destroy']);

});


Route::prefix('cart')->group(function () {
    Route::get('list/{deviceId}', [CartController::class, 'list']);
    Route::post('add', [CartController::class, 'add']);
    Route::post('update_qty/{id}/{qty}', [CartController::class, 'update_qty']);
    Route::post('selected/{id}', [CartController::class, 'selected']);
    Route::get('list_selected/{deviceId}', [CartController::class, 'list_selected']);
    Route::get('increase/{id}', [CartController::class, 'increase']);
    Route::get('decrease/{id}', [CartController::class, 'decrease']);
    Route::delete('delete/{id}', [CartController::class, 'delete']);
});




Route::prefix('variant')->group(function () {
    Route::post('store', [ProductVariantController::class, 'store']);
});


Route::prefix('brand')->group(function () {
    Route::get('index', [BrandController::class, 'index']);
    Route::get('show/{id}', [BrandController::class, 'show']);
    Route::post('store', [BrandController::class, 'store']);
    Route::post('update/{id}', [BrandController::class, 'update']);
    Route::delete('destroy/{id}', [BrandController::class, 'destroy']);
    Route::get('change_status/{key}', [BrandController::class, 'changeStatus']);
    Route::get('delete/{key}', [BrandController::class, 'delete']);
    Route::get('restore/{key}', [BrandController::class, 'restore']);
    Route::get('trash', [BrandController::class, 'trash']);
    Route::post('action_trash', [BrandController::class, 'action_trash']);
    Route::post('action_destroy', [BrandController::class, 'action_destroy']);

});
Route::prefix('category')->group(function () {
    Route::get('index', [CategoryController::class, 'index']);
    Route::get('show/{id}', [CategoryController::class, 'show']);
    Route::post('store', [CategoryController::class, 'store']);
    Route::post('update/{id}', [CategoryController::class, 'update']);
    Route::delete('destroy/{id}', [CategoryController::class, 'destroy']);
    Route::get('change_status/{key}', [CategoryController::class, 'changeStatus']);
    Route::get('delete/{key}', [CategoryController::class, 'delete']);
    Route::get('restore/{key}', [CategoryController::class, 'restore']);
    Route::get('trash', [CategoryController::class, 'trash']);
    Route::post('action_trash', [CategoryController::class, 'action_trash']);
    Route::post('action_destroy', [CategoryController::class, 'action_destroy']);

});
Route::prefix('contact')->group(function () {
    Route::get('index', [ContactController::class, 'index']);
    Route::get('show/{id}', [ContactController::class, 'show']);
    Route::post('store', [ContactController::class, 'store']);
    Route::post('update/{id}', [ContactController::class, 'update']);
    Route::delete('destroy/{id}', [ContactController::class, 'destroy']);
    Route::get('change_status/{key}', [ContactController::class, 'changeStatus']);
    Route::get('delete/{key}', [ContactController::class, 'delete']);
    Route::get('restore/{key}', [ContactController::class, 'restore']);
    Route::get('trash', [ContactController::class, 'trash']);
    Route::post('action_trash', [ContactController::class, 'action_trash']);
    Route::post('action_destroy', [ContactController::class, 'action_destroy']);

});
Route::prefix('menu')->group(function () {
    Route::get('index', [MenuController::class, 'index']);
    Route::get('show/{id}', [MenuController::class, 'show']);
    Route::post('store', [MenuController::class, 'store']);
    Route::get('tao/{position}/{type}/{listid}', [MenuController::class, 'tao']);
    Route::post('update/{id}', [MenuController::class, 'update']);
    Route::delete('destroy/{id}', [MenuController::class, 'destroy']);
    Route::get('change_status/{key}', [MenuController::class, 'changeStatus']);
    Route::get('search/{key}', [MenuController::class, 'search']);
    Route::get('delete/{key}', [MenuController::class, 'delete']);
    Route::get('restore/{key}', [MenuController::class, 'restore']);
    Route::get('trash', [MenuController::class, 'trash']);
    Route::post('action_trash', [MenuController::class, 'action_trash']);
    Route::post('action_destroy', [MenuController::class, 'action_destroy']);

});
Route::prefix('post')->group(function () {
    Route::get('index', [PostController::class, 'index']);
    Route::get('show/{id}', [PostController::class, 'show']);
    Route::post('store', [PostController::class, 'store']);
    Route::post('update/{id}', [PostController::class, 'update']);
    Route::delete('destroy/{id}', [PostController::class, 'destroy']);
    Route::get('change_status/{key}', [PostController::class, 'changeStatus']);
    Route::get('delete/{key}', [PostController::class, 'delete']);
    Route::get('restore/{key}', [PostController::class, 'restore']);
    Route::get('trash', [PostController::class, 'trash']);
    Route::post('action_trash', [PostController::class, 'action_trash']);
    Route::post('action_destroy', [PostController::class, 'action_destroy']);

});
Route::prefix('page')->group(function () {
    Route::get('index', [PageController::class, 'index']);
    Route::get('show/{id}', [PageController::class, 'show']);
    Route::post('store', [PageController::class, 'store']);
    Route::post('update/{id}', [PageController::class, 'update']);
    Route::delete('destroy/{id}', [PageController::class, 'destroy']);
    Route::get('change_status/{key}', [PageController::class, 'changeStatus']);
    Route::get('delete/{key}', [PageController::class, 'delete']);
    Route::get('restore/{key}', [PageController::class, 'restore']);
    Route::get('trash', [PageController::class, 'trash']);
    Route::post('action_trash', [PageController::class, 'action_trash']);
    Route::post('action_destroy', [PageController::class, 'action_destroy']);

});
Route::prefix('product')->group(function () {
    Route::get('index', [ProductController::class, 'index']);
    Route::get('show/{id}', [ProductController::class, 'show']);
    Route::post('store', [ProductController::class, 'store']);
    Route::post('update/{id}', [ProductController::class, 'update']);
    Route::delete('destroy/{id}', [ProductController::class, 'destroy']);
    Route::get('change_status/{key}', [ProductController::class, 'changeStatus']);
    Route::get('filter/{category_id}/{brand_id}', [ProductController::class, 'filter']);
    Route::get('delete/{key}', [ProductController::class, 'delete']);
    Route::get('restore/{key}', [ProductController::class, 'restore']);
    Route::get('trash', [ProductController::class, 'trash']);
    Route::post('action_trash', [ProductController::class, 'action_trash']);
    Route::post('action_destroy', [ProductController::class, 'action_destroy']);
});
Route::prefix('productstore')->group(function () {
    Route::get('index', [ProductStoreController::class, 'index']);
    Route::get('show_history/{product_id}/{variant_id?}', [ProductStoreController::class, 'show_history']);
    Route::get('show/{id}', [ProductSaleController::class, 'show']);
    Route::post('store', [ProductStoreController::class, 'store']);
    Route::post('update/{id}', [ProductStoreController::class, 'update']);
    Route::get('delete/{key}', [ProductStoreController::class, 'delete']);
    Route::delete('destroy/{id}', [ProductStoreController::class, 'destroy']);
    Route::get('change_status/{key}', [ProductStoreController::class, 'changeStatus']);
    Route::post('action_trash', [ProductStoreController::class, 'action_trash']);
    Route::post('action_destroy', [ProductStoreController::class, 'action_destroy']);

});
Route::prefix('productsale')->group(function () {
    Route::get('index', [ProductSaleController::class, 'index']);
    Route::get('show/{id}', [ProductSaleController::class, 'show']);
    Route::post('store', [ProductSaleController::class, 'store']);
    Route::post('update/{id}', [ProductSaleController::class, 'update']);
    Route::delete('destroy/{id}', [ProductSaleController::class, 'destroy']);
    Route::get('change_status/{key}', [ProductSaleController::class, 'changeStatus']);
    Route::post('action_trash', [ProductSaleController::class, 'action_trash']);
    Route::post('action_destroy', [ProductSaleController::class, 'action_destroy']);
});

Route::post('config/update', [ConfigController::class, 'update']);
Route::get('config/show', [ConfigController::class, 'show']);

Route::prefix('banner')->group(function () {
    Route::get('index', [BannerController::class, 'index']);
    Route::get('show/{id}', [BannerController::class, 'show']);
    Route::post('store', [BannerController::class, 'store']);
    Route::post('update/{id}', [BannerController::class, 'update']);
    Route::delete('destroy/{id}', [BannerController::class, 'destroy']);
    Route::get('change_status/{key}', [BannerController::class, 'changeStatus']);
    Route::get('delete/{key}', [BannerController::class, 'delete']);
    Route::get('restore/{key}', [BannerController::class, 'restore']);
    Route::get('trash', [BannerController::class, 'trash']);
    Route::post('action_trash', [BannerController::class, 'action_trash']);
    Route::post('action_destroy', [BannerController::class, 'action_destroy']);

});
Route::prefix('topic')->group(function () {
    Route::get('index', [TopicController::class, 'index']);
    Route::get('show/{id}', [TopicController::class, 'show']);
    Route::post('store', [TopicController::class, 'store']);
    Route::post('update/{id}', [TopicController::class, 'update']);
    Route::delete('destroy/{id}', [TopicController::class, 'destroy']);
    Route::get('change_status/{key}', [TopicController::class, 'changeStatus']);
    Route::get('delete/{key}', [TopicController::class, 'delete']);
    Route::get('restore/{key}', [TopicController::class, 'restore']);
    Route::get('trash', [TopicController::class, 'trash']);
    Route::post('action_trash', [TopicController::class, 'action_trash']);
    Route::post('action_destroy', [TopicController::class, 'action_destroy']);

});

Route::prefix('user')->group(function () {
    Route::get('index', [UserController::class, 'index']);
    Route::get('show/{id}', [UserController::class, 'show']);
    Route::post('store', [UserController::class, 'store']);
    Route::post('update/{id}', [UserController::class, 'update']);
    Route::delete('destroy/{id}', [UserController::class, 'destroy']);
    Route::get('change_status/{key}', [UserController::class, 'changeStatus']);
    Route::get('delete/{key}', [UserController::class, 'delete']);
    Route::get('restore/{key}', [UserController::class, 'restore']);
    Route::get('trash', [UserController::class, 'trash']);
    Route::post('action_trash', [UserController::class, 'action_trash']);
    Route::post('action_destroy', [UserController::class, 'action_destroy']);

});
Route::prefix('customer')->group(function () {
    Route::get('index', [CustomerController::class, 'index']);
    Route::get('show/{id}', [CustomerController::class, 'show']);
    Route::post('store', [CustomerController::class, 'store']);
    Route::post('update/{id}', [CustomerController::class, 'update']);
    Route::delete('destroy/{id}', [CustomerController::class, 'destroy']);
    Route::get('change_status/{key}', [CustomerController::class, 'changeStatus']);
    Route::get('delete/{key}', [CustomerController::class, 'delete']);
    Route::get('restore/{key}', [CustomerController::class, 'restore']);
    Route::get('trash', [CustomerController::class, 'trash']);
    Route::post('action_trash', [CustomerController::class, 'action_trash']);
    Route::post('action_destroy', [CustomerController::class, 'action_destroy']);
});

Route::prefix('attribute')->group(function () {
    Route::get('index', [AttributeController::class, 'index']);
    Route::get('show/{id}', [AttributeController::class, 'show']);
    Route::post('store', [AttributeController::class, 'store']);
    Route::post('update/{id}', [AttributeController::class, 'update']);
    Route::delete('destroy/{id}', [AttributeController::class, 'destroy']);
    Route::get('change_status/{key}', [AttributeController::class, 'changeStatus']);
    Route::get('delete/{key}', [AttributeController::class, 'delete']);
    Route::get('restore/{key}', [AttributeController::class, 'restore']);
    Route::get('trash', [AttributeController::class, 'trash']);
    Route::post('action_trash', [AttributeController::class, 'action_trash']);
    Route::post('action_destroy', [AttributeController::class, 'action_destroy']);
});

Route::prefix('attributeValue')->group(function () {
    Route::get('index', [AttributeValueController::class, 'index']);
    Route::get('show/{id}', [AttributeValueController::class, 'show']);
    Route::post('store', [AttributeValueController::class, 'store']);
    Route::post('update/{id}', [AttributeValueController::class, 'update']);
    Route::delete('destroy/{id}', [AttributeValueController::class, 'destroy']);
    Route::get('change_status/{key}', [AttributeValueController::class, 'changeStatus']);
    Route::get('delete/{key}', [AttributeValueController::class, 'delete']);
    Route::get('restore/{key}', [AttributeValueController::class, 'restore']);
    Route::get('trash', [AttributeValueController::class, 'trash']);
    Route::post('action_trash', [AttributeValueController::class, 'action_trash']);
    Route::post('action_destroy', [AttributeValueController::class, 'action_destroy']);
});

Route::prefix('importInvoice')->group(function () {
    Route::get('index', [ImportInvoiceController::class, 'index']);
    Route::get('show/{id}', [ImportInvoiceController::class, 'show']);
    Route::post('store', [ImportInvoiceController::class, 'store']);
    Route::post('update/{id}', [ImportInvoiceController::class, 'update']);
    Route::delete('destroy/{id}', [ImportInvoiceController::class, 'destroy']);
    Route::get('change_status/{key}', [ImportInvoiceController::class, 'changeStatus']);
    Route::get('delete/{key}', [ImportInvoiceController::class, 'delete']);
    Route::get('restore/{key}', [ImportInvoiceController::class, 'restore']);
    Route::get('trash', [ImportInvoiceController::class, 'trash']);
    Route::post('action_trash', [ImportInvoiceController::class, 'action_trash']);
    Route::post('action_destroy', [ImportInvoiceController::class, 'action_destroy']);
});
Route::prefix('promotion')->group(function () {
    Route::get('index', [PromotionController::class, 'index']);
    Route::get('show/{id}', [PromotionController::class, 'show']);
    Route::post('store', [PromotionController::class, 'store']);
    Route::post('update/{id}', [PromotionController::class, 'update']);
    Route::delete('destroy/{id}', [PromotionController::class, 'destroy']);
    Route::get('change_status/{key}', [PromotionController::class, 'changeStatus']);
    Route::get('delete/{key}', [PromotionController::class, 'delete']);
    Route::get('restore/{key}', [PromotionController::class, 'restore']);
    Route::get('trash', [PromotionController::class, 'trash']);
    Route::post('action_trash', [PromotionController::class, 'action_trash']);
    Route::post('action_destroy', [PromotionController::class, 'action_destroy']);

});


Route::group(['middleware'=>'api', 'prefix' => 'auth'],function(){
    Route::post('logout', [AuthController::class,'logout']);
    Route::post('refresh', [AuthController::class,'refresh']);
    Route::post('me', [AuthController::class,'me']);
});
    // Route::middleware(['auth:sanctum', 'isAPIAdmin'])->group(function() {
    //     Route::get('checkingAuthenticated', function(){
    //         return response()->json(['message'=>'You are in', 'status'=>200], 200);
    //     });
    // });
    
    // Route::middleware(['auth:sanctum'])->group(function() {
    //     Route::post('logout', [UserController::class, 'logout']);
    // });

    // Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });