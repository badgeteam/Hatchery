<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Http\Requests\FileUploadRequest;
use App\Models\File;
use App\Models\Version;
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
        $this->middleware('auth', ['except' => 'show']);
        $this->authorizeResource(File::class, null, ['except' => 'show']);
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
        $file->content = file_get_contents($upload->path());
        $file->save();
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
            return redirect()->route('files.edit', ['file' => $file->id])->withInput()->withErrors([$e->getMessage()]);
        }

        if ($file->extension === 'py') {
            $pyflakes = $this->lintContent($request->file_content);
            if ($pyflakes['return_value'] == 0) {
                return redirect()
                    ->route('projects.edit', ['project' => $file->version->project->slug])
                    ->withSuccesses([$file->name.' saved']);
            } elseif (!empty($pyflakes[0])) {
                return redirect()->route('files.edit', ['file' => $file->id])
                    ->withInput()
                    ->withWarnings(explode("\n", strval($pyflakes[0])));
            }

            return redirect()->route('files.edit', ['file' => $file->id])
                ->withInput()
                ->withErrors(explode("\n", strval($pyflakes[1])));
        }

        return redirect()
            ->route('projects.edit', ['project' => $file->version->project->slug])
            ->withSuccesses([$file->name.' saved']);
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

        return view('files.create')->with('version', $version);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function createIcon(Request $request): RedirectResponse
    {
        $version = Version::where('id', $request->get('version'))->firstOrFail();
        $file = new File();
        $pixels = [];
        for ($p = 0; $p < 64; $p++) {
            $pixels[] = '0x00000000';
        }

        try {
            $file->version_id = $version->id;
            $file->name = 'icon.py';
            $file->content = 'icon = (['.implode(', ', $pixels).'], 1)';
            $file->save();
        } catch (\Exception $e) {
            return redirect()->route('projects.edit', ['project' => $version->project->slug])->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('files.edit', ['file' => $file->id])->withSuccesses([$file->name.' created']);
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
        } catch (\Exception $e) {
            return redirect()->route('files.create')->withInput()->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $file->version->project->slug])->withSuccesses([$file->name.' saved']);
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
            return redirect()->route('projects.edit', ['project' => $project->slug])
                ->withInput()
                ->withErrors([$e->getMessage()]);
        }

        return redirect()->route('projects.edit', ['project' => $project->slug])->withSuccesses([$file->name.' deleted']);
    }

    /**
     * @param string $content
     * @param string $command = "pyflakes"
     *
     * @return array<string|int, string|int|null>
     */
    public static function lintContent(string $content, string $command = 'pyflakes'): array
    {
        $stdOut = $stdErr = '';
        $returnValue = 255;
        $fds = [
            0 => ['pipe', 'r'], // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
        ];
        $process = proc_open($command, $fds, $pipes, null, null);
        if (is_resource($process)) {
            fwrite($pipes[0], $content);
            fclose($pipes[0]);
            $stdOut = (string) stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $stdErr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            $returnValue = proc_close($process);
        }

        return [
            'return_value' => $returnValue,
            0              => preg_replace('/<stdin>\:/', '', $stdOut),
            1              => preg_replace('/<stdin>\:/', '', $stdErr),
        ];
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
            ->header('Content-Disposition', 'attachment; filename='.$file->name)
            ->header('Content-Transfer-Encoding', 'binary');
    }
}
