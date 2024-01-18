<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Helpers\Util;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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
            return view('admin.files.index')->with('error', 'file.not_a_directory');
        }

        // Get the list of files and directories in the directory.
        $files = Util::getFiles($directory);

        $extensions = Util::getConfigParam('file_extensions_allowed');

        $uploadMaxFilesize = ini_get('upload_max_filesize');

        return view('admin.files.index')
            ->with('directory', $directory)
            ->with('parentDirectory', $parentDirectory)
            ->with('baseDirectory', $baseDirectory)
            ->with('files', $files)
            ->with('path', $path)
            ->with('ext', $extensions)
            ->with('maxFileSize', $uploadMaxFilesize);

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

        $extensions = Util::getConfigParam('file_extensions_allowed');

        try {
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            $maxFileSizeInBytes = Util::convertToBytes($uploadMaxFilesize);

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                // Check file's extension
                $extensions = str_replace(' ', '', $extensions);
                $allowedExtensions = explode(',', $extensions);
                $fileExtension = $file->getClientOriginalExtension();

                if (!in_array($fileExtension, $allowedExtensions)) {
                    return redirect()->back()->with('error', __('file.invalid_file_extension'));
                }

                // Verify file size
                $fileSize = $file->getSize(); // Size in kilobytes

                if ($fileSize > $maxFileSizeInBytes) {
                    return redirect()->back()->with('error', __('file.file_size_exceeded'));
                }

                // If everything went OK, move file.
                $filename = $file->getClientOriginalName();
                $file->move($currentPath, $filename);
                return redirect()->back()->with('success', __('file.uploaded_to_moodle', ['filename' => $filename]));
            }
            return redirect()->back()->with('error', __('file.upload_missing_file'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('file.upload_error', ['error' => $e->getMessage()]));
        }
    }

    public function destroy(Request $request, string $path, string $file){
        try{
            $path = base64_decode($path);
            $file = base64_decode($file);
            $wholeRoute = $path . $file;

            if (Storage::exists($path)) {
                if (File::exists($wholeRoute)) {
                    File::delete($wholeRoute);
                    return redirect()->back()->with('success', __('file.deleted_file', ['file' => $file, 'path' => $path]));
                } else {
                    return redirect()->back()->with('error', __('file.upload_error', ['error' => $file]));
                }
            } else {
                return redirect()->back()->with('error', __('file.upload_error', ['error' => $file]));
            }
        } catch (\Exception $e) {
            return redirect()->route('file.index')->with('error', 'Error al eliminar el archivo');
        }
    }
}
