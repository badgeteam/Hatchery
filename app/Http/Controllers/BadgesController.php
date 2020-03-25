<?php

namespace App\Http\Controllers;

use App\Http\Requests\BadgeStoreRequest;
use App\Http\Requests\BadgeUpdateRequest;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Class BadgesController.
 *
 * @author annejan@badge.team
 */
class BadgesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->authorizeResource(Badge::class, null, ['except' => ['index', 'show']]);
    }

    /**
     * Show badges.
     *
     * @return View
     */
    public function index(): View
    {
        return view('badges.index')
            ->with('badges', Badge::paginate());
    }

    /**
     * Show badge, public method ツ.
     *
     * @param Badge $badge
     *
     * @return View
     */
    public function show(Badge $badge): View
    {
        return view('badges.show')
            ->with('badge', $badge)
            ->with('projects', $badge->projects()->paginate());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Badge $badge
     *
     * @return View
     */
    public function edit(Badge $badge): View
    {
        return view('badges.edit')
            ->with('badge', $badge);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('badges.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BadgeStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(BadgeStoreRequest $request): RedirectResponse
    {
        $badge = new Badge();

        try {
            $badge->name = $request->name;
            $badge->commands = $request->commands;
            $badge->constraints = $request->constraints;
            $badge->save();
        } catch (\Exception $e) {
            return redirect()->route('badges.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('badges.index')->withSuccesses([$badge->name.' saved']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BadgeUpdateRequest $request
     * @param Badge              $badge
     *
     * @return RedirectResponse
     */
    public function update(BadgeUpdateRequest $request, Badge $badge): RedirectResponse
    {
        $slug = $badge->slug;
        try {
            $badge->name = $request->name;
            $badge->commands = $request->commands;
            $badge->constraints = $request->constraints;
            $badge->save();
        } catch (\Exception $e) {
            return redirect()->route('badges.edit', ['badge' => $slug])
                ->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('badges.index')->withSuccesses([$badge->name.' updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Badge $badge
     *
     * @return RedirectResponse
     */
    public function destroy(Badge $badge): RedirectResponse
    {
        try {
            $badge->delete();
        } catch (\Exception $e) {
            return redirect(URL()->previous())
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('badges.index')->withSuccesses([$badge->name.' deleted']);
    }
}
