<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectNotificationRequest;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Mail\ProjectNotificationMail;
use App\Models\Badge;
use App\Models\BadgeProject;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use App\Models\Warning;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Phar;
use PharData;

/**
 * Class ProjectsController.
 *
 * @author annejan@badge.team
 */
class ProjectsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->authorizeResource(Project::class, null, ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $badge = $category = $search = '';
        if ($request->has('badge')) {
            $badge = Badge::where('slug', $request->get('badge'))->first();
        }
        if ($badge === '' || !$badge) {
            $projects = Project::orderBy('id', 'DESC');
        } else {
            $projects = $badge->projects()->orderBy('id', 'DESC');
            $badge = $badge->slug;
        }
        if ($request->has('category') && $request->get('category')) {
            $category = Category::where('slug', $request->get('category'))->first();
            $projects = $projects->where('category_id', $category->id);
            $category = $category->slug;
        }
        if ($request->has('search')) {
            $search = $request->get('search');
            $projects = $projects->where(
                function (Builder $query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%');
                    // @todo perhaps search in README ?
                }
            );
        }

        return view('projects.index')
            ->with(['projects' => $projects->paginate()])
            ->with('badge', $badge)
            ->with('category', $category)
            ->with('search', $search);
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
     *
     * @return RedirectResponse
     */
    public function store(ProjectStoreRequest $request): RedirectResponse
    {
        if (Project::where('slug', Str::slug($request->name, '_'))->exists()) {
            return redirect()->route('projects.create')->withInput()->withErrors(['slug already exists :(']);
        }

        if (Project::isForbidden(Str::slug($request->name, '_'))) {
            return redirect()->route('projects.create')->withInput()->withErrors(['reserved name']);
        }

        $project = new Project();

        try {
            $project->name = $request->name;
            $project->category_id = $request->category_id;
            $project->save();

            if ($request->badge_ids) {
                $badges = Badge::find($request->badge_ids);
                $project->badges()->attach($badges);
            }
            if ($request->description) {
                $version = $project->versions->last();
                $file = new File();
                $file->name = 'README.md';
                $file->content = $request->description;
                $file->version()->associate($version);
                $file->save();
            }
        } catch (\Exception $e) {
            return redirect()->route('projects.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' saved']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Project $project
     *
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
     * @param Project              $project
     *
     * @return RedirectResponse
     */
    public function update(ProjectUpdateRequest $request, Project $project): RedirectResponse
    {
        try {
            $project->category_id = $request->category_id;
            if ($request->has('dependencies')) {
                $dependencies = $request->get('dependencies');
                foreach ($project->dependencies as $dependency) {
                    if (!in_array($dependency->id, $dependencies)) {
                        $dependency->pivot->delete();
                    }
                }
                foreach ($dependencies as $dependency) {
                    if (!$project->dependencies->contains($dependency)) {
                        /** @var Project $dep */
                        $dep = Project::find($dependency);
                        $project->dependencies()->save($dep);
                    }
                }
            } else {
                foreach ($project->dependencies as $dependency) {
                    $dependency->pivot->delete();
                }
            }

            if ($request->badge_ids) {
                $project->badges()->detach();
                $badges = Badge::find($request->badge_ids);
                $project->badges()->attach($badges);

                foreach ($request->badge_ids as $badge_id) {
                    if (array_key_exists($badge_id, $request->badge_status)) {
                        $state = BadgeProject::where('badge_id', $badge_id)->where('project_id', $project->id)->first();
                        $state->status = $request->badge_status[$badge_id];
                        $state->save();
                    }
                }
            }
            $project->save();

            if (isset($request->publish)) {
                return $this->publish($project);
            }
        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $project->slug])->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.index')->withSuccesses([$project->name.' saved']);
    }

    /**
     * Publish the latest version.
     *
     * @param Project $project
     *
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

        $data = [
            'name'        => $project->name,
            'description' => $project->description,
            'category'    => $project->category,
            'author'      => $project->user->name,
            'revision'    => $version->revision,
        ];

        if ($project->hasValidIcon()) {
            $data['icon'] = 'icon.png';
        }

        $zip[$project->slug.'/metadata.json'] = strval(json_encode($data));

        if (!$project->dependencies->isEmpty()) {
            $dep = '';
            foreach ($project->dependencies as $dependency) {
                $dep .= $dependency->slug."\n";
            }
            $zip[$project->slug.'/'.$project->slug.'.egg-info/requires.txt'] = $dep;
        }

        if (empty(exec('which minigzip'))) {
            $zip->compress(Phar::GZ);
        } else {
            system('minigzip < '.public_path($filename).' > '.public_path($filename.'.gz'));
        }
        unlink(public_path($filename));

        $version->zip = $filename.'.gz';
        $version->size_of_zip = filesize(public_path($version->zip));
        $version->save();

        $newVersion = new Version();
        $newVersion->revision = $version->revision + 1;
        $newVersion->project()->associate($project);
        $newVersion->save();
        foreach ($version->files as $file) {
            $newFile = new File();
            $newFile->name = $file->name;
            $newFile->content = $file->content;
            $newFile->version()->associate($newVersion);
            $newFile->save();
        }

        $project->published_at = now();
        $project->save();

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' published']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Project $project
     *
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
     * Show project content, public method ツ.
     *
     * @param Project $project
     *
     * @return View
     */
    public function show(Project $project): View
    {
        return view('projects.show')
            ->with('project', $project);
    }

    /**
     * Notify badge.team of broken or dangerous app.
     *
     * @param Project                    $project
     * @param ProjectNotificationRequest $request
     *
     * @return RedirectResponse
     */
    public function notify(Project $project, ProjectNotificationRequest $request): RedirectResponse
    {
        $warning = Warning::create(['project_id' => $project->id, 'description' => $request->description]);
        $mail = new ProjectNotificationMail($warning);
        Mail::to('bugs@badge.team')->send($mail);

        return redirect()->route('projects.show', ['project' => $project])->withSuccesses(['Notification sent to badge.team']);
    }
}
