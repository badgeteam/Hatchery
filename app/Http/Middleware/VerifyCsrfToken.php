<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Support\Facades\App;

/**
 * Class VerifyCsrfToken.
 *
 * @package App\Http\Middleware
 */
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @throws \Illuminate\Session\TokenMismatchException
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Don't validate CSRF when testing.
        if (App::environment(['local', 'testing'])) {
            return $this->addCookieToResponse($request, $next($request));
        }

        return parent::handle($request, $next);
    }
}
