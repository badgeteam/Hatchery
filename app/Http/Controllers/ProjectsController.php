<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectNotificationRequest;
use App\Http\Requests\ProjectRenameRequest;
use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Jobs\PublishProject;
use App\Jobs\UpdateProject;
use App\Mail\ProjectNotificationMail;
use App\Models\Badge;
use App\Models\BadgeProject;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\Version;
use App\Models\Warning;
use App\Support\Helpers;
use Cz\Git\GitException;
use App\Support\GitRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
            $category = Category::where('slug', $request->get('category'))->firstOrFail();
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
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        return view('projects.create')
            ->with('type', $request->routeIs('projects.import') ? 'import' : 'create');
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

        try {
            $project = $this->storeProjectInfo($request);
        } catch (\Exception $e) {
            return redirect()->route('projects.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' created!']);
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
                        /** @var BadgeProject $state */
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
        PublishProject::dispatch($project, Auth::user());

        return redirect()->route('projects.index')->withSuccesses([$project->name.' is being published.']);
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
        $name = $project->name;

        try {
            $project->name = 'Deleted '.rand().' '.$name;
            $project->slug = Str::slug($project->name);
            $project->save();
            $project->delete();
        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $project->slug])
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.index')->withSuccesses([$name.' deleted']);
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

    /**
     * Show project rename form, public method ツ.
     *
     * @param Project $project
     *
     * @return View
     */
    public function renameForm(Project $project): View
    {
        return view('projects.rename')
            ->with('project', $project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProjectRenameRequest $request
     * @param Project              $project
     *
     * @return RedirectResponse
     */
    public function rename(ProjectRenameRequest $request, Project $project): RedirectResponse
    {
        $slug = Str::slug($request->name);

        if (Project::whereSlug($slug)->exists()) {
            return redirect()->route('projects.rename',
                ['project' => $project->slug])->withInput()->withErrors(['Name not unique']);
        }

        $project->name = $request->name;
        $project->slug = $slug;
        $project->save();

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$project->name.' renamed']);
    }

    /**
     * Update the specified resource from git when applicable.
     *
     * @param Project $project
     *
     * @return RedirectResponse
     */
    public function pull(Project $project): RedirectResponse
    {
        if ($project->git === null) {
            return redirect()->route('projects.edit', ['project' => $project->slug])->withInput()->withErrors(['No git repo for project.']);
        }

        UpdateProject::dispatch($project, Auth::user());

        return redirect()->route('projects.index')->withSuccesses([$project->name.' is being updated.']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectStoreRequest $request
     * @param GitRepository       $repo
     *
     * @return RedirectResponse
     */
    public function import(ProjectStoreRequest $request, GitRepository $repo): RedirectResponse
    {
        if (Project::where('slug', Str::slug($request->name, '_'))->exists()) {
            return redirect()->route('projects.create')->withInput()->withErrors(['slug already exists :(']);
        }
        if (Project::isForbidden(Str::slug($request->name, '_'))) {
            return redirect()->route('projects.create')->withInput()->withErrors(['reserved name']);
        }

        $tempFolder = sys_get_temp_dir().'/'.Str::slug($request->name);

        try {
            $repo->cloneRepository($request->git, $tempFolder, ['-q', '--single-branch', '--depth', 1]);
        } catch (GitException $e) {
            return redirect()->route('projects.import')->withInput()->withErrors([$e->getMessage()]);
        }

        try {
            $project = $this->storeProjectInfo($request);
            $project->git = $request->git;
            $project->save();
            UpdateProject::dispatch($project, Auth::user());
        } catch (\Exception $e) {
            Helpers::delTree($tempFolder);

            return redirect()->route('projects.import')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.index')->withSuccesses([$project->name.' being imported!']);
    }

    /**
     * @param Request $request
     *
     * @return Project
     */
    private function storeProjectInfo(Request $request): Project
    {
        $project = Project::create([
            'name' => $request->name,
            'category_id' => $request->category_id
        ]);

        if ($request->badge_ids) {
            $badges = Badge::find($request->badge_ids);
            $project->badges()->attach($badges);
        }
        if ($request->description) {
            /** @var Version $version */
            $version = $project->versions->last();
            $file = new File();
            $file->name = 'README.md';
            $file->content = $request->description;
            $file->version()->associate($version);
            $file->save();
        }
        return $project;
    }
}
