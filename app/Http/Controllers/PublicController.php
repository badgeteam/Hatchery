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
        return view('welcome')->with(['users' => User::count(), 'projects' => Project::count()]);
    }

    /**
     * Get the latest released version.
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function json($slug): JsonResponse
    {
        $project = Project::where('slug', $slug)->firstOrFail();

        $releases = [];
        foreach($project->versions()->published()->get() as $version) {
            $releases[$version->revision] = ['url' => url($version->zip)];
        }

        $version = $project->versions()->published()->get()->last();

        if (is_null($version)) {
            return response()->json(['message' => 'No releases found'], 404);
        }

        $package = new stdClass;
        $package->info = ['version' => (string)$version->revision];
        $package->description = $project->description;
        $package->releases = $releases;

        return response()->json($package, 200, [], JSON_UNESCAPED_SLASHES);
    }
}
