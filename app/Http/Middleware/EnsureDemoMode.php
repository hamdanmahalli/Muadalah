<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Pembatas akses khusus "mode demo".
 *
 * Berfungsi bila config app.demo = true (instance demo).
 * Di luar itu, seluruh rute/sumber daya yang memakai middleware ini
 * diperlakukan tidak ada (404) supaya produksi tidak terpengaruh.
 */
class EnsureDemoMode
{
    public function handle(Request $request, Closure $next)
    {
        if (config('app.demo') !== true) {
            abort(404);
        }

        return $next($request);
    }
}