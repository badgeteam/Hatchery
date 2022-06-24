<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

class MchController extends Controller
{
    /**
     * List the available devices.
     *
     * @OA\Get(
     *   path="/devices",
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @return JsonResponse
     */
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
     * @OA\Get(
     *   path="/{device}/types",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $device
     * @return JsonResponse
     */
    public function types(string $device): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        return response()->json($badge->types, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the types of apps a device supports.
     *
     * @OA\Get(
     *   path="/{device}/{type}/categories",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
     * @OA\Parameter(
     *     name="type",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="esp32")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $device
     * @param string $type
     * @return JsonResponse
     */
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
     * @OA\Get(
     *   path="/{device}/{type}/{category}",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
     * @OA\Parameter(
     *     name="type",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="esp32")
     *   ),
     * @OA\Parameter(
     *     name="category",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="fun")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @return JsonResponse
     */
    public function apps(string $device, string $type, string $category): JsonResponse
    {
        /** @var Badge $badge */
        $badge = Badge::whereSlug($device)->firstOrFail();
        /** @var Category $category */
        $categoryId = Category::whereSlug($category)->firstOrFail()->id;
        $apps = [];
        /** @var Project $project */
        foreach ($badge->projects()->whereProjectType($type)->whereCategoryId($categoryId)->get() as $project) {
            $apps[] = [
                'slug' => $project->slug,
                'name' => $project->name,
                'author' => $project->author,
                'license' => $project->license,
                'description' => $project->description,
            ];
        }
        return response()->json($apps, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }


    /**
     * Get the apps from a device / type / category
     *
     * @OA\Get(
     *   path="/{device}/{type}/{category}/{app}",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
     * @OA\Parameter(
     *     name="type",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="esp32")
     *   ),
     * @OA\Parameter(
     *     name="category",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="fun")
     *   ),
     * @OA\Parameter(
     *     name="app",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="game_of_life")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @param string $app
     * @return JsonResponse
     */
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
            $fileData->size = $file->size_of_content;

            $files[] = $fileData;
        }

        return response()->json(
            [
                'slug' => $project->slug,
                'name' => $project->name,
                'author' => $project->author,
                'license' => $project->license,
                'description' => $project->description,
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
     * @OA\Get(
     *   path="/{device}/{type}/{category}/{app}/{file}",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
     * @OA\Parameter(
     *     name="type",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="esp32")
     *   ),
     * @OA\Parameter(
     *     name="category",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="fun")
     *   ),
     * @OA\Parameter(
     *     name="app",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="game_of_life")
     *   ),
     * @OA\Parameter(
     *     name="file",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="file.py")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $device
     * @param string $type
     * @param string $category
     * @param string $app
     * @param string $name
     * @return Response|JsonResponse
     */
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

        return response(
            $file->content,
            200,
            ['Content-Type' => $file->mime]
        );
    }
}
