<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
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
            })->get()
        ]);
    }

    /**
     * Get the latest released version.
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function projectJson($slug): JsonResponse
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $releases = [];
        foreach($project->versions()->published()->get() as $version) {
            $releases[$version->revision] = [['url' => url($version->zip)]];
        }

        $version = $project->versions()->published()->get()->last();

        if (is_null($version)) {
            return response()->json(['message' => 'No releases found'], 404);
        }

        $package = new stdClass;
        $package->info = ['version' => (string)$version->revision];
        $package->description = $project->description;
        $package->releases = $releases;

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
        })->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the latest released versions.
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
            ->get(), 200, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }
}
