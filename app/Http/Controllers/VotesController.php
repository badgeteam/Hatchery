<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Project;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class VotesController.
 *
 * @author annejan@badge.team
 */
class VotesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'show']);
        $this->authorizeResource(Vote::class, null, ['except' => 'show']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VoteRequest $request
     *
     * @return RedirectResponse
     */
    public function store(VoteRequest $request): RedirectResponse
    {
        $user = Auth::guard()->user();

        if (Vote::where('user_id', $user->id)->where('project_id', $request->project_id)->exists()) {
            /** @var Project $project */
            $project = Project::find($request->project_id);
            return redirect()
                ->route('projects.show', ['project' => $project->slug])
                ->withInput()
                ->withErrors(['Already voted, no update method yet implemented']);
        }

        $vote = new Vote();

        try {
            $vote->project_id = $request->project_id;
            $vote->type = $request->type;
            $vote->comment = $request->comment;
            $vote->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.show', ['project' => $vote->project->slug])->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.show', ['project' => $vote->project->slug])->withSuccesses(['Vote saved']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Vote $vote
     *
     * @return RedirectResponse
     */
    public function destroy(Vote $vote): RedirectResponse
    {
        $project = $vote->project;

        try {
            $vote->delete();
        } catch (\Exception $e) {
            return redirect()->route('projects.show', ['project' => $project->slug])
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.show', ['project' => $project->slug])->withSuccesses(['Vote deleted']);
    }
}
