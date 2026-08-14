<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

/**
 * Class Controller.
 *
 * 　　　　　　 ＿＿
 * 　　　　　／＞　　フ
 * 　　　　　|  _　 _l  Not adding OA attributes makes kitty sad!!
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
#[OA\Info(
    version: '0.2',
    description: 'Simple micropython software repository for Badges.',
    title: 'Hatchery by badge.team',
    contact: new OA\Contact(
        name: 'Hatchery',
        url: 'https://docs.badge.team/hatchery',
        email: 'hatchery@badge.team',
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT',
    ),
)]
#[OA\Parameter(
    parameter: 'badge',
    name: 'badge',
    in: 'path',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'slug', example: 'sha2017'),
)]
#[OA\Response(
    response: 'html',
    description: 'Undocumented HTML response',
    content: new OA\XmlContent(),
)]
#[OA\Response(
    response: 'undocumented',
    description: 'Undocumented JSON response',
    content: new OA\JsonContent(),
)]
#[OA\Tag(
    name: 'Basket',
    description: 'Related to getting Projects for specific Badge models.',
)]
#[OA\Tag(
    name: 'Egg',
    description: 'Related to getting Eggs / Projects.',
)]
#[OA\Tag(
    name: 'External',
    description: 'External api proxies for convenience of apps.',
)]
class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
