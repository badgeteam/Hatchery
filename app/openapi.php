<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="Hatchery by badge.team",
 *   version="0.2",
 *   description="Simple micropython software repository for Badges."
 * )
 */

/**
 * @OA\Server(
 *   url=L5_SWAGGER_BASE_URL
 * )
 * @OA\Server(
 *   description="Staging",
 *   url="https://badge.soononline.nl"
 * )
 * @OA\Server(
 *   description="Production",
 *   url="https://badge.team"
 * )
 */

/**
 * @OA\Response(
 *   response="html",
 *   description="Undocumented HTML response",
 *   @OA\XmlContent()
 * )
 */
