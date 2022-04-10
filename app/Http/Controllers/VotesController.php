<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\VoteStoreRequest;
use App\Http\Requests\VoteUpdateRequest;
use App\Models\Project;
use App\Models\User;
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
        $this->authorizeResource(Vote::class);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VoteStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(VoteStoreRequest $request): RedirectResponse
    {
        /** @var User $user */
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
            return redirect()->route('projects.show', ['project' => $vote->project->slug])
                ->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.show', ['project' => $vote->project->slug])->withSuccesses(['Vote saved']);
    }

    /**
     * @param Vote              $vote
     * @param VoteUpdateRequest $request
     *
     * @return RedirectResponse
     */
    public function update(Vote $vote, VoteUpdateRequest $request): RedirectResponse
    {
        try {
            $vote->type = $request->type;
            $vote->comment = $request->comment;
            $vote->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.show', ['project' => $vote->project->slug])
                ->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.show', ['project' => $vote->project->slug])
            ->withSuccesses(['Vote updated']);
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
            return redirect(URL()->previous())
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.show', ['project' => $project->slug])->withSuccesses(['Vote deleted']);
    }
}
