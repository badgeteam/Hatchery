<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(User::class);
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
            ->with('user', $user);
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
            return redirect()->route('users.edit', ['user' => $user->id])
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        $this->guard()->logout();

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
}
