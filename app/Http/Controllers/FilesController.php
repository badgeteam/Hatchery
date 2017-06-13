<?php

namespace App\Http\Controllers;


use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FilesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function upload($version, $request): View
    {
        dd($version, $request);

        return view('projects.index')->with(['projects' => Project::paginate()]);
    }

}
