<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

/**
 * Class HomeController.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        return view('home')->with(['users' => User::count(), 'projects' => Project::count()]);
    }
}
