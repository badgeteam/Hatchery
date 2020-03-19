<?php

namespace App\Http\Controllers;

use App\Models\Badge;
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
}
