<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use App\Events\DownloadCounter;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use stdClass;

class PublicController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        return view('welcome')->with([
            'users' => User::count(),
            'projects' => Project::count(),
            'published' => Project::whereHas('versions', function ($query) {
                $query->published();
            })->orderBy('id', 'DESC')->get()
        ]);
    }

    /**
     * Get the latest released version.
     *
     * @param  Project  $project
     * @return JsonResponse
     */
    public function projectJson(Project $project): JsonResponse
    {
        $releases = [];
        foreach($project->versions()->published()->orderBy('revision', 'desc')->limit(5)->get() as $version) {
            $releases[$version->revision] = [['url' => url($version->zip)]];
        }

        $version = $project->versions()->published()->get()->last();

        if (empty($version)) {
            return response()->json(['message' => 'No releases found'], 404);
        }

        $package = new stdClass;
        $package->info = ['version' => (string)$version->revision];
        $package->description = $project->description;
        $package->name = $project->name;
        $package->category = $project->category;
        $package->releases = $releases;

	event(new DownloadCounter($project));

        return response()->json($package, 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions.
     *
     * @return JsonResponse
     */
    public function listJson(): JsonResponse
    {
        return response()->json(Project::whereHas('versions', function ($query) {
            $query->published();
        })->orderBy('id', 'DESC')->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Find the latest released versions.
     *
     * @param string $search
     * @return JsonResponse
     */
    public function searchJson($search): JsonResponse
    {
        $what = '%'.$search.'%';
        return response()->json(Project::whereHas('versions', function ($query) {
            $query->published();
        })->where('name', 'like', $what)
            ->orWhere('description', 'like', $what)
            ->orderBy('id', 'DESC')
            ->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions.
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function categoryJson(Category $category): JsonResponse
    {
        return response()->json($category->projects()->whereHas('versions', function ($query) {
            $query->published();
        })->orderBy('id', 'DESC')->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the categories.
     *
     * @return JsonResponse
     */
    public function categoriesJson(): JsonResponse
    {
        return response()->json(Category::where('hidden', false)->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

}
