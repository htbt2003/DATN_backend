<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            // Tạo phản hồi JSON với thông báo lỗi tùy chỉnh
            return response()->json([
                'message' => 'Data was invalid.', // Thông báo lỗi chung
                'errors' => $exception->errors(), // Các lỗi cụ thể cho từng trường
            ], 422);
        }

        // Xử lý các ngoại lệ khác
        return parent::render($request, $exception);
    }

}
