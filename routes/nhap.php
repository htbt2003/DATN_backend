Route::prefix('brand')->group(function () {
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
