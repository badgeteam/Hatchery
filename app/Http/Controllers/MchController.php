<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
// SPDX-License-Identifier: MIT

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class MchController extends Controller
{
    /**
     * List the available devices.
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/v2/devices',
        tags: ['MCH2022'],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function devices(): JsonResponse
    {
        $devices = [];
        foreach (Badge::pluck('name', 'slug') as $slug => $name) {
            $devices[] = [
                'slug' => $slug,
                'name' => $name
            ];
        }
        return response()->json(
            $devices,
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Get the types of apps a device supports.
     *
     * @param string $device
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/v2/{device}/types',
        tags: ['MCH2022'],
        parameters: [
            new OA\Parameter(
                name: 'device',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'mch2022'),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function types(string $device): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        return response()->json($badge->types, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the types of apps a device supports.
     *
     * @param string $device
     * @param string $type
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/v2/{device}/{type}/categories',
        tags: ['MCH2022'],
        parameters: [
            new OA\Parameter(
                name: 'device',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'mch2022'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'esp32'),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function categories(string $device, string $type): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();

        $count = $categories = [];
        /** @var Project $project */
        foreach ($badge->projects()->whereProjectType($type)->get() as $project) {
            $count[$project->category_id] =
                isset($count[$project->category_id]) ? $count[$project->category_id] + 1 : 1;
        }
        foreach ($count as $id => $apps) {
            /** @var Category $category */
            $category = Category::find($id);
            $categories[] = [
                'name' => $category->name,
                'slug' => $category->slug,
                'apps' => $apps,
            ];
        }

        return response()->json($categories, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the apps from a device / type / category
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/v2/{device}/{type}/{category}',
        tags: ['MCH2022'],
        parameters: [
            new OA\Parameter(
                name: 'device',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'mch2022'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'esp32'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'fun'),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function apps(string $device, string $type, string $category): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        $categoryId = Category::whereSlug($category)->firstOrFail()->id;
        $apps = [];
        /** @var Project $project */
        foreach ($badge->projects()->whereNotNull('published_at')->whereProjectType($type)->whereCategoryId($categoryId)->get() as $project) {
            $apps[] = [
                'slug' => $project->slug,
                'name' => $project->name,
                'author' => $project->author,
                'license' => $project->license,
                'description' => $project->description,
                'version' => $project->revision,
            ];
        }
        return response()->json($apps, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }


    /**
     * Get the apps from a device / type / category
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @param string $app
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/v2/{device}/{type}/{category}/{app}',
        tags: ['MCH2022'],
        parameters: [
            new OA\Parameter(
                name: 'device',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'mch2022'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'esp32'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'fun'),
            ),
            new OA\Parameter(
                name: 'app',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'game_of_life'),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function app(string $device, string $type, string $category, string $app): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        $categoryId = Category::whereSlug($category)->firstOrFail()->id;
        /** @var Project $project */
        $project = $badge->projects()
            ->whereProjectType($type)->whereCategoryId($categoryId)->whereSlug($app)->firstOrFail();

        /** @var Version $version */
        $version = $project->versions()->published()->get()->last();
        $files = [];
        /** @var File $file */
        foreach ($version->files as $file) {
            $fileData = new \stdClass();
            $fileData->name = $file->name;
            $fileData->url = route('mch.file', [
                'device' => $badge->slug,
                'type' => $project->project_type,
                'category' => $category,
                'app' => $project->slug,
                'file' => $file->name
            ]);
            $fileData->size = (int)$file->size_of_content;
            $fileData->crc32 = $file->crc32;

            $files[] = $fileData;
        }

        return response()->json(
            [
                'device' => $badge->slug,
                'type' => $project->project_type,
                'category' => $category,
                'slug' => $project->slug,
                'name' => $project->name,
                'author' => $project->author,
                'license' => $project->license,
                'description' => $project->description,
                'version' => (int)$project->revision,
                'files' => $files,
            ],
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Get app file content
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @param string $app
     * @param string $name
     * @return Response|JsonResponse
     */
    #[OA\Get(
        path: '/v2/{device}/{type}/{category}/{app}/{file}',
        tags: ['MCH2022'],
        parameters: [
            new OA\Parameter(
                name: 'device',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'mch2022'),
            ),
            new OA\Parameter(
                name: 'type',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'esp32'),
            ),
            new OA\Parameter(
                name: 'category',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'fun'),
            ),
            new OA\Parameter(
                name: 'app',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'game_of_life'),
            ),
            new OA\Parameter(
                name: 'file',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'slug', example: 'file.py'),
            ),
        ],
        responses: [
            new OA\Response(response: 'default', ref: '#/components/responses/undocumented'),
        ],
    )]
    public function file(
        string $device,
        string $type,
        string $category,
        string $app,
        string $name
    ): Response|JsonResponse {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        $categoryId = Category::whereSlug($category)->firstOrFail()->id;
        /** @var Project $project */
        $project = $badge->projects()
            ->whereProjectType($type)->whereCategoryId($categoryId)->whereSlug($app)->firstOrFail();

        /** @var Version|null $version */
        $version = $project->versions()->published()->get()->last();

        if ($version === null || empty($version->files)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        /** @var File|null $file */
        $file = $version->files()->where('name', $name)->first();
        if ($file === null) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response($file->content)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $file->mime)
            ->header('Content-length', (string) strlen($file->content))
            ->header('Content-Disposition', 'attachment; filename=' . $file->name)
            ->header('Content-Transfer-Encoding', 'binary');
    }
}
