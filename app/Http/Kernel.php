<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\AddContentLength;
use App\Http\Middleware\AuthenticatorMiddleware;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\ShareMessagesFromSession;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\VerifyCsrfToken;
use Bepsvpt\SecureHeaders\SecureHeadersMiddleware;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaravelWebauthn\Http\Middleware\WebauthnMiddleware;
use OpenApi\Annotations as OA;

/**
 * Class Kernel.
 *
 * @OA\Info(
 *   title="Hatchery by badge.team",
 *   version="0.2",
 *   description="Simple micropython software repository for Badges.",
 * @OA\Contact(
 *     name="Hatchery",
 *     url="https://docs.badge.team/hatchery",
 *     email="hatchery@badge.team"
 *   ),
 * @OA\License(
 *       name="MIT",
 *       url="https://opensource.org/licenses/MIT"
 *   )
 * )
 *
 * @OA\Parameter(
 *   parameter="badge",
 *   name="badge",
 *   in="path",
 *   required=true,
 * @OA\Schema(type="string", format="slug", example="sha2017")
 * )
 *
 * @OA\Response(
 *   response="html",
 *   description="Undocumented HTML response",
 * @OA\XmlContent()
 * ),
 * @OA\Response(
 *   response="undocumented",
 *   description="Undocumented JSON response",
 * @OA\JsonContent()
 * )
 *
 * @OA\Tag(
 *   name="Basket",
 *   description="Related to getting Projects for specific Badge models."
 * ),
 * @OA\Tag(
 *   name="Egg",
 *   description="Related to getting Eggs / Projects."
 * ),
 * @OA\Tag(
 *   name="External",
 *   description="External api proxies for convenience of apps."
 * )
 *
 * 　　　　　　 ＿＿
 * 　　　　　／＞　　フ
 * 　　　　　|  _　 _l  Not adding OA doc blocks makes kitty sad!!
 * 　 　　　／`ミ＿xノ
 * 　　 　 /　　　 　|
 * 　　　 /　 ヽ　　 ﾉ
 * 　 　 │　　|　|　|
 * 　／￣|　　 |　|　|
 * 　| (￣ヽ＿_ヽ_)__)
 * 　＼二つ
 *
 * @author annejan@badge.team
 */
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<string>
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SecureHeadersMiddleware::class,
        HandleCors::class,
        AddContentLength::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array>
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            ShareMessagesFromSession::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, string>
     */
    protected $routeMiddleware = [
        'auth'       => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings'   => SubstituteBindings::class,
        'can'        => Authorize::class,
        'guest'      => RedirectIfAuthenticated::class,
        'throttle'   => ThrottleRequests::class,
        '2fa'        => AuthenticatorMiddleware::class,
        'webauthn'   => WebauthnMiddleware::class,
    ];

    /**
     * Returns the version of the application by fetching and displaying the version.json file
     *
     * @return string URL
     * @throws \JsonException
     */
    public static function applicationVersion(): string
    {
        // Silence is ok here
        $versionJson = @file_get_contents(public_path("/version.json"));
        if (!$versionJson) {
            return 'Undefined';
        }

        $versionData = json_decode($versionJson, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($versionData) && array_key_exists('version', $versionData)) {
            return $versionData['version'];
        }

        return 'Unknown';
    }
}
