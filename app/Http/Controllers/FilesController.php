<?php

namespace App\Http\Controllers;


use App\Models\File;
use App\Models\Version;
use Illuminate\Http\Request;
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
    public function upload(Version $version, Request $request): View
    {
        $user = Auth::guard()->user();

        $file = new File;
        $file->version()->associate($version);


    }

}
