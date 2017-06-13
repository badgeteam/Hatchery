<?php

namespace App\Http\Controllers;


use App\Http\Requests\FileStoreRequest;
use App\Models\File;
use App\Models\Version;
use Illuminate\Http\Request;
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
     * @param Version $version
     * @param FileStoreRequest $request
     */
    public function upload(Version $version, FileStoreRequest $request)
    {
        $upload = $request->file('file');

        $file = new File;
        $file->version()->associate($version);
        $file->name = $upload->getClientOriginalName();
        $file->content = file_get_contents($upload->path());
        $file->save();
    }

}
