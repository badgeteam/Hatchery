<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Support\Facades\App;

/**
 * Class VerifyCsrfToken.
 *
 * @author annejan@badge.team
 */
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<string>
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
        // @codeCoverageIgnoreStart
        return parent::handle($request, $next);
        // @codeCoverageIgnoreEnd
    }
}
