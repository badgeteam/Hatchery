<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class UsersController.
 *
 * @author annejan@badge.team
 */
class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(User::class, null, ['except' => ['show', 'index']]);
    }

    /**
     * Show public user profiles.
     *
     * @return View
     */
    public function index(): View
    {
        return view('users.index')
            ->with('users', User::where('public', true)->paginate());
    }

    /**
     * Show user profile, public method ツ.
     *
     * @param User $user
     *
     * @return View
     */
    public function show(User $user): View
    {
        return view('users.show')
            ->with('user', $user)
            ->with('projects', $this->getProjects($user));
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function redirect(Request $request): RedirectResponse
    {
        return redirect()->route('users.show', ['user' => $request->user()->getAuthIdentifier()]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     *
     * @return View
     */
    public function edit(User $user): View
    {
        return view('users.edit')
            ->with('user', $user)
            ->with('projects', $this->getProjects($user));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param User              $user
     *
     * @return RedirectResponse
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->editor = $request->editor;
            $user->public = (bool) $request->public;
            $user->show_projects = (bool) $request->show_projects;
            $user->save();
        } catch (\Exception $e) {
            return redirect()->route('users.edit', ['user' => $user->id])->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()
            ->route('projects.index')
            ->withSuccesses([$user->name.' saved']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $user->delete();
        } catch (\Exception $e) {
            return redirect(URL()->previous())
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        /** @var Auth $guard */
        $guard = $this->guard();
        $guard->logout();

        return redirect()->guest('/')->withSuccesses([$user->name.' deleted']);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return Guard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * @param User $user
     *
     * @return LengthAwarePaginator
     */
    private function getProjects(User $user): LengthAwarePaginator
    {
        return Project::where(function ($query) use ($user) {
            $query->where('user_id', $user->id);
            $query->orWhereHas('collaborators', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        })->orderByDesc('updated_at')->paginate();
    }
}
