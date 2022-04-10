<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Authenticator;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

/**
 * Class AuthenticatorMiddleware.
 *
 * @author annejan@badge.team
 */
class AuthenticatorMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return JsonResponse|Response
     */
    public function handle($request, Closure $next)
    {
        /** @var Authenticator $authenticator */
        $authenticator = app(Authenticator::class)->boot($request);

        if ($authenticator->isAuthenticated()) {
            return $next($request);
        }

        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
