Route::prefix('customer')->group(function () {
    Route::get('index', [VariantController::class, 'index']);
    Route::get('show/{id}', [VariantController::class, 'show']);
    Route::post('store', [VariantController::class, 'store']);
    Route::post('update/{id}', [VariantController::class, 'update']);
    Route::delete('destroy/{id}', [VariantController::class, 'destroy']);
    Route::get('change_status/{key}', [VariantController::class, 'changeStatus']);
    Route::get('delete/{key}', [VariantController::class, 'delete']);
    Route::get('restore/{key}', [VariantController::class, 'restore']);
    Route::get('trash', [VariantController::class, 'trash']);
    Route::post('action_trash', [VariantController::class, 'action_trash']);
    Route::post('action_destroy', [VariantController::class, 'action_destroy']);
});
