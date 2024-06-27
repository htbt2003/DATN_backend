Route::prefix('brand')->group(function () {
    Route::get('index', [ProductVariantController::class, 'index']);
    Route::get('show/{id}', [ProductVariantController::class, 'show']);
    Route::post('store', [ProductVariantController::class, 'store']);
    Route::post('update/{id}', [ProductVariantController::class, 'update']);
    Route::delete('destroy/{id}', [ProductVariantController::class, 'destroy']);
    Route::get('change_status/{key}', [ProductVariantController::class, 'changeStatus']);
    Route::get('delete/{key}', [ProductVariantController::class, 'delete']);
    Route::get('restore/{key}', [ProductVariantController::class, 'restore']);
    Route::get('trash', [ProductVariantController::class, 'trash']);
    Route::post('action_trash', [ProductVariantController::class, 'action_trash']);
    Route::post('action_destroy', [ProductVariantController::class, 'action_destroy']);

});
