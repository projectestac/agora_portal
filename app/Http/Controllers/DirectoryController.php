<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Helpers\Util;

class DirectoryController extends Controller {
    /**
     * List the contents of the base directory. Takes an optional path parameter to be appended to the base directory.
     * If the path is empty, show the contents of the base directory. Otherwise, show the contents of the path. The list
     * of files and directories is sorted alphabetically, and it is forbidden to navigate outside the base directory.
     *
     */
    public function index(string $path = ''): View {

        $path = base64_decode($path);
        $path = trim($path, '/');

        // Security check: Remove all occurrences of ".." and "." from the path
        $path = str_replace(['..', '.'], '', $path);

        $baseDirectory = Util::getAgoraVar('portaldata') . 'data/';

        if (empty($path)) {
            $directory = $baseDirectory;
        } else {
            $directory = $baseDirectory . $path . '/';
        }

        // Get the parent directory of $path if there is a path
        $parentDirectory = empty($path) ? '' : dirname($path);

        // Make sure the directory exists.
        if (!is_dir($directory)) {
            return view('admin.files.index')->with('error', 'files.not_a_directory');
        }

        // Get the list of files and directories in the directory.
        $files = Util::getFiles($directory);

        return view('admin.files.index')
            ->with('directory', $directory)
            ->with('parentDirectory', $parentDirectory)
            ->with('baseDirectory', $baseDirectory)
            ->with('files', $files)
            ->with('path', $path);

    }

    /**
     * Download file
     */
    public function download(Request $request, string $path) {
        $path = base64_decode($path);

        if (is_file($path)) {
            $filename = pathinfo($path, PATHINFO_BASENAME);
            return response()->file($path, ['Content-Disposition' => 'attachment; filename="' . $filename . '"']);
        }

        abort(404);
    }

    /**
     * Upload file
     */
    public function upload(Request $request, string $currentPath): RedirectResponse {
        $currentPath = base64_decode($currentPath);

        $request->validate([
            'file' => 'required|mimes:zip,sql,jpg,gif,png,txt|max:25000', // Size in kilobytes (25MB = 25000 KB)
        ]);

        try {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = $file->getClientOriginalName();
                $file->move($currentPath, $filename);

                return redirect()->back()->with('success', __('files.uploaded_to_moodle', ['filename' => $filename]));
            }
            return redirect()->back()->with('error', __('files.upload_missing_file'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('files.upload_error', ['error' => $e->getMessage()]));
        }
    }

}
