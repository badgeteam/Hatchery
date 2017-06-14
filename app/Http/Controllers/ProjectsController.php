<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Phar;
use PharData;
use stdClass;

class ProjectsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('projects.index')->with(['projects' => Project::paginate()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectStoreRequest $request
     * @return RedirectResponse
     */
    public function store(ProjectStoreRequest $request): RedirectResponse
    {
        $project = new Project;
        try {
            $project->name = $request->name;
            $project->description = $request->description;
            $project->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->id])->withSuccesses([$project->name.' saved']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $projectId
     * @return View
     */
    public function edit($projectId): View
    {
        $project = Project::where('id', $projectId)->firstOrFail();
        return view('projects.edit')
            ->with('project', $project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProjectUpdateRequest $request
     * @param  int  $projectId
     * @return RedirectResponse
     */
    public function update(ProjectUpdateRequest $request, $projectId): RedirectResponse
    {
        $project = Project::where('id', $projectId)->firstOrFail();
        try {
            $project->description = $request->description;
            $project->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $project->id])->withInput()->withErrors([$e->getMessage()]);
        }
        return redirect()->route('projects.index')->withSuccesses([$project->name.' saved']);
    }

    /**
     * Publish the latest version.
     *
     * @param  int  $projectId
     * @return RedirectResponse
     */
    public function publish($projectId): RedirectResponse
    {
        $project = Project::where('id', $projectId)->firstOrFail();
        $version = $project->versions()->unPublished()->first();

        $filename = 'eggs/'.uniqid($project->slug).'.tar';

        $zip = new PharData(public_path($filename));

        foreach ($version->files as $file) {
            $zip[$file->name] = $file->content;
        }

        $zip->compress(Phar::GZ);

        $version->zip = $filename.'.gz';
        $version->save();

        $newVersion = new Version;
        $newVersion->revision = $version->revision + 1;
        $newVersion->project()->associate($project);
        $newVersion->save();
        foreach ($version->files as $file) {
            $newFile = new File;
            $newFile->name = $file->name;
            $newFile->content = $file->content;
            $newFile->version()->associate($newVersion);
            $newFile->save();
        }

        return redirect()->route('projects.edit', ['project' => $project->id])->withSuccesses([$project->name.' published']);
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
        $package->version = $version->revision;
        $package->description = $project->description;
        $package->releases = $releases;

        return response()->json($package);
    }
}
