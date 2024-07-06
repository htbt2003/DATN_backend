Route::prefix('brand')->group(function () {
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
