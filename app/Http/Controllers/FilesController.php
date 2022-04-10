<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Requests\FileUploadRequest;
use App\Jobs\LintContent;
use App\Jobs\ProcessFile;
use App\Models\File;
use App\Models\Version;
use App\Support\Linters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

/**
 * Class FilesController.
 *
 * @author annejan@badge.team
 */
class FilesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show', 'download']]);
        $this->authorizeResource(File::class, null, ['except' => ['show', 'download']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Version           $version
     * @param FileUploadRequest $request
     */
    public function upload(Version $version, FileUploadRequest $request): void
    {
        /** @var UploadedFile $upload */
        $upload = $request->file('file');
        /** @var File $file */
        $file = $version->files()->firstOrNew(['name' => $upload->getClientOriginalName()]);
        $file->content = (string) file_get_contents($upload->path());
        $file->save();
        LintContent::dispatch($file);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param File $file
     *
     * @return View
     */
    public function edit(File $file): View
    {
        return view('files.edit')
            ->with('file', $file);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param FileUpdateRequest $request
     * @param File              $file
     *
     * @return RedirectResponse
     */
    public function update(FileUpdateRequest $request, File $file): RedirectResponse
    {
        try {
            $file->content = $request->file_content;
            $file->save();
        } catch (\Exception $e) {
            return redirect()->route('files.edit', ['file' => $file->id])
                ->withInput()->withErrors([$e->getMessage()]);
        }

        if ($file->lintable) {
            $data = Linters::lintFile($file);
            if ($data['return_value'] === 0) {
                return redirect()
                    ->route('projects.edit', ['project' => $file->version->project])
                    ->withSuccesses([$file->name . ' saved']);
            }

            if (!empty($data[0])) {
                return redirect()->route('files.edit', ['file' => $file->id])
                    ->withInput()
                    ->withSuccesses([$file->name . ' saved'])
                    ->withWarnings(explode("\n", (string) $data[0]));
            }

            return redirect()->route('files.edit', ['file' => $file->id])
                ->withInput()
                ->withSuccesses([$file->name . ' saved'])
                ->withErrors(explode("\n", (string) $data[1]));
        }

        return redirect()
            ->route('projects.edit', ['project' => $file->version->project])
            ->withSuccesses([$file->name . ' saved']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        $version = Version::where('id', $request->get('version'))->firstOrFail();

        return view('files.create')
            ->with('version', $version)
            ->with('name', $request->name);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createIcon(Request $request): RedirectResponse
    {
        /** @var Version $version */
        $version = Version::where('id', $request->get('version'))->firstOrFail();
        $file = new File();
        $pixels = [];
        for ($p = 0; $p < 64; $p++) {
            $pixels[] = '0x00000000';
        }

        try {
            $file->version_id = $version->id;
            $file->name = $request->has('name') ? $request->get('name') : 'icon.py';
            $file->content = 'icon = ([' . implode(', ', $pixels) . '], 1)';
            $file->save();
        } catch (\Exception $e) {
            return redirect()->route(
                'projects.edit',
                [
                'project' => $version->project]
            )->withInput()->withErrors([$e->getMessage()
                    ]);
        }

        return redirect()->route('files.edit', ['file' => $file->id])->withSuccesses([$file->name . ' created']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FileStoreRequest $request
     *
     * @return RedirectResponse
     */
    public function store(FileStoreRequest $request): RedirectResponse
    {
        $file = new File();

        try {
            $file->version_id = $request->version_id;
            $file->name = $request->name;
            $file->content = $request->file_content;
            $file->save();
            LintContent::dispatch($file);
        } catch (\Exception $e) {
            return redirect()->route('files.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route(
            'projects.edit',
            [
            'project' => $file->version->project]
        )->withSuccesses([$file->name . ' saved'
                ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param File $file
     *
     * @return RedirectResponse
     */
    public function destroy(File $file): RedirectResponse
    {
        $project = $file->version->project;

        try {
            $file->delete();
        } catch (\Exception $e) {
            return redirect(URL()->previous())
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project])->withSuccesses([$file->name . ' deleted']);
    }

    /**
     * Show file content, public method ツ.
     *
     * @param File $file
     *
     * @return View
     */
    public function show(File $file): View
    {
        return view('files.show')
            ->with('file', $file);
    }

    /**
     * Download file content, public method ツ what could go wrong..
     *
     * @param File $file
     *
     * @return Response
     */
    public function download(File $file): Response
    {
        return response($file->content)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $file->mime)
            ->header('Content-length', (string) strlen($file->content))
            ->header('Content-Disposition', 'attachment; filename=' . $file->name)
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * @param FileUpdateRequest $request
     * @param File              $file
     *
     * @return JsonResponse
     */
    public function lint(FileUpdateRequest $request, File $file): JsonResponse
    {
        LintContent::dispatch($file, $request->file_content);

        return response()->json(['linting' => 'started']);
    }

    /**
     * @param File $file
     *
     * @return JsonResponse
     */
    public function process(File $file): JsonResponse
    {
        ProcessFile::dispatch($file);

        return response()->json(['processing' => 'started']);
    }
}
