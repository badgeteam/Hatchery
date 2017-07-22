<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Phar;
use PharData;

class ProjectsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
	$this->authorizeResource(Project::class, null,['except' => 'show']);

    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('projects.index')->with(['projects' => Project::orderBy('id', 'DESC')->paginate()]);
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
            $project->category_id = $request->category_id;
            $project->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' saved']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Project  $project
     * @return View
     */
    public function edit(Project $project): View
    {
        return view('projects.edit')
            ->with('project', $project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProjectUpdateRequest $request
     * @param Project $project
     * @return RedirectResponse
     */
    public function update(ProjectUpdateRequest $request, Project $project): RedirectResponse
    {
        try {
            $project->description = $request->description;
            $project->category_id = $request->category_id;
            if ($request->has('dependencies')) {
                $dependencies = $request->get('dependencies');
                foreach($project->dependencies as $dependency) {
                    if (!in_array($dependency->id, $dependencies)) {
                        $dependency->pivot->delete();
                    }
                }
                foreach($dependencies as $dependency) {
                    if (!$project->dependencies->contains($dependency)) {
                        $project->dependencies()->save(Project::find($dependency));
                    }
                }
            } else {
                foreach($project->dependencies as $dependency) {
                    $dependency->pivot->delete();
                }
            }

            $project->save();

            if (isset($request->publish)) {
                return($this->publish($project));
            }

        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $project->slug])->withInput()->withErrors([$e->getMessage()]);
        }
        return redirect()->route('projects.index')->withSuccesses([$project->name.' saved']);
    }

    /**
     * Publish the latest version.
     *
     * @param  Project $project
     * @return RedirectResponse
     */
    public function publish(Project $project): RedirectResponse
    {
        $version = $project->versions()->unPublished()->first();

        $filename = 'eggs/'.uniqid($project->slug.'_').'.tar';

        $zip = new PharData(public_path($filename));

        foreach ($version->files as $file) {
            $zip[$project->slug.'/'.$file->name] = $file->content;
        }

        if (!$project->dependencies->isEmpty()) {
            $dep = "";
            foreach ($project->dependencies as $dependency) {
                $dep .= $dependency->slug . "\n";
            }
            $zip[$project->slug.'/'.$project->slug.'.egg-info/requires.txt'] = $dep;
        }

//        $zip->compress(Phar::GZ);

        system('minigzip < '.public_path($filename).' > '. public_path($filename.'.gz'));

        $version->zip = $filename.'.gz';
        $version->size_of_zip = filesize(public_path($version->zip));
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

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' published']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  Project $project
     * @return RedirectResponse
     */
    public function destroy(Project $project): RedirectResponse
    {
        try {
            $project->delete();
        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $project->slug])
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.index')->withSuccesses([$project->name.' deleted']);
    }

    /**
     * Show project content, public method ツ
     *
     * @param Project $project
     * @return View
     */
    public function show(Project $project): View
    {
        return view('projects.show')
            ->with('project', $project);
    }
}
