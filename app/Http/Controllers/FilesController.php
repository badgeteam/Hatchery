<?php

namespace App\Http\Controllers;


use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Models\File;
use App\Models\Version;
use Illuminate\Http\RedirectResponse;
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
     * @param FileUploadRequest $request
     */
    public function upload(Version $version, FileUploadRequest $request)
    {
        $upload = $request->file('file');

        $file = new File;
        $file->version()->associate($version);
        $file->name = $upload->getClientOriginalName();
        $file->content = file_get_contents($upload->path());
        $file->save();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $fileId
     * @return View
     */
    public function edit($fileId): View
    {
        $file = File::where('id', $fileId)->firstOrFail();
        return view('files.edit')
            ->with('file', $file);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FileUpdateRequest $request
     * @param  int  $fileId
     * @return RedirectResponse
     */
    public function update(FileUpdateRequest $request, $fileId): RedirectResponse
    {
        $file = File::where('id', $fileId)->firstOrFail();
        try {
            $file->content = $request->file_content;
            $file->save();
        } catch (\Exception $e) {
            return redirect()->route('file.edit', ['file' => $file->id])->withInput()->withErrors([$e->getMessage()]);
        }
        return redirect()->route('projects.edit', ['project' => $file->project->id])->withSuccesses([$file->name.' saved']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('files.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FileStoreRequest $request
     * @return RedirectResponse
     */
    public function store(FileStoreRequest $request): RedirectResponse
    {
        $project = new File;
        try {
            $project->name = $request->name;
            $project->content = $request->file_content;
            $project->save();
        } catch (\Exception $e) {
            return redirect()->route('files.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->id])->withSuccesses([$project->name.' saved']);
    }
}
