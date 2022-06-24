<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class MchController extends Controller
{
    /**
     * List the available devices.
     *
     * @OA\Get(
     *   path="/mch2022/devices",
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
     *   path="/mch2022/{device}/types",
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
        return response()->json($badge->types, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Get the types of apps a device supports.
     *
     * @OA\Get(
     *   path="/mch2022/{device}/{type}/categories",
     * @OA\Parameter(
     *     name="device",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="mch2022")
     *   ),
 *     @OA\Parameter(
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

        $count = $categories =  [];

        // @todo Filtering on type
        foreach ($badge->projects as $project) {
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

        return response()->json($categories, 200, ['Content-Type' => 'application/json']);
    }



    /**
     * Get the latest released files from a project.
     *
     * @OA\Get(
     *   path="/eggs/files/{project}/json",
     * @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="game_of_life")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $slug
     *
     * @return JsonResponse
     */
    public function filesJson(string $slug): JsonResponse
    {
        /** @var Project|null $project */
        $project = Project::where('slug', $slug)->first();
        if ($project === null) {
            return response()->json(
                ['message' => 'Project not found'],
                404,
                ['Content-Type' => 'application/json'],
                JSON_UNESCAPED_SLASHES
            );
        }

        $version = $project->versions()->published()->get()->last();

        if ($version === null || empty($version->files)) {
            return response()->json(['message' => 'No files found'], 404);
        }

        $files = [];

        foreach ($version->files as $file) {
            $fileData = new \stdClass();
            $fileData->name = $file->name;
            $fileData->extension = $file->extension;
            $fileData->size = $file->size_of_content;

            $files[] = $fileData;
        }

        return response()->json(
            $files,
            200,
            ['Content-Type' => 'application/json'],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Get the content from a released file from a project.
     *
     * @OA\Get(
     *   path="/eggs/file/{project}/get/{file}",
     * @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="game_of_life")
     *   ),
     *  @OA\Parameter(
     *     name="file",
     *     in="path",
     *     required=true,
     * @OA\Schema(type="string", format="slug", example="file_name.py")
     *   ),
     *   tags={"MCH2022"},
     * @OA\Response(response="default",ref="#/components/responses/undocumented")
     * )
     *
     * @param string $slug
     * @param string $name
     *
     * @return JsonResponse
     */
    public function fileContent(string $slug, string $name): JsonResponse
    {
        /** @var Project|null $project */
        $project = Project::where('slug', $slug)->first();
        if ($project === null) {
            return response()->json(
                ['message' => 'Project not found'],
                404,
                ['Content-Type' => 'application/json'],
                JSON_UNESCAPED_SLASHES
            );
        }

        $version = $project->versions()->published()->get()->last();

        if ($version === null || empty($version->files)) {
            return response()->json(['message' => 'No files found'], 404);
        }

        /** @var File|null $file */
        $file = $version->files()->where('name', $name)->first();
        if ($file === null) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->json(
            $file->content,
            200,
            ['Content-Type' => $file->mime],
            JSON_UNESCAPED_SLASHES
        );
    }
}
