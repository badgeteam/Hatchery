<?php

declare(strict_types=1);

use App\Http\Middleware\AddContentLength;
use App\Http\Middleware\AuthenticatorMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\ShareMessagesFromSession;
use App\Http\Middleware\VerifyCsrfToken;
use Bepsvpt\SecureHeaders\SecureHeadersMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelWebauthn\Http\Middleware\WebauthnMiddleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/legacy.php'));

            Route::middleware('web')
                ->prefix('eggs')
                ->group(base_path('routes/eggs.php'));

            Route::middleware('web')
                ->prefix('basket')
                ->group(base_path('routes/basket.php'));

            Route::middleware('web')
                ->prefix('weather')
                ->group(base_path('routes/weather.php'));

            Route::middleware('web')
                ->prefix('v2')
                ->group(base_path('routes/mch2022.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trimStrings(except: [
            'password',
            'password_confirmation',
        ]);

        $middleware->append([
            SecureHeadersMiddleware::class,
            AddContentLength::class,
        ]);

        // Sessions are invalidated when the user's password changes elsewhere.
        $middleware->authenticateSessions();

        $middleware->web(
            append: [
                ShareMessagesFromSession::class,
            ],
            replace: [
                ValidateCsrfToken::class => VerifyCsrfToken::class,
            ],
        );

        $middleware->alias([
            'guest'    => RedirectIfAuthenticated::class,
            '2fa'      => AuthenticatorMiddleware::class,
            'webauthn' => WebauthnMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('sitemap:generate')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);

        $exceptions->dontReport([
            AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
        ]);

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });
    })->create();
