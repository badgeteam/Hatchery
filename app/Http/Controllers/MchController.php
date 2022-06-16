<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class MchController extends Controller
{
    /**
     * Get the latest released version of a project.
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
}
