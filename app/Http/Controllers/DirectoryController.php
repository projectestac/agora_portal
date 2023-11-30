<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Helpers\Util;

class DirectoryController extends Controller
{
    /**
     * List the contents of the base directory. Takes an optional path parameter to be appended to the base directory.
     * If the path is empty, show the contents of the base directory. Otherwise, show the contents of the path. The list
     * of files and directories is sorted alphabetically and it is forbidden to navigate outside the base directory.
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

}
