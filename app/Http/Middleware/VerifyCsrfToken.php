<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'http://huynhthibaotran_backend_cdtt.test/api/login',
        // 'http://huynhthibaotran_backend_cdtt.test/api/register',
        // 'http://huynhthibaotran_backend_cdtt.test/api/logout',
        // 'http://huynhthibaotran_backend_cdtt.test/api/doCheckout',
        // 'http://huynhthibaotran_backend_cdtt.test/api/review/store',
        // 'http://huynhthibaotran_backend_cdtt.test/api/updateAccount/{id}',
        // 'http://huynhthibaotran_backend_cdtt.test/api/user/update/{id}',
    ];
}
