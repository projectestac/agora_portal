<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Helpers\Util;

class DirectoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($path = null): View {
        // Genero el directorio raiz
        $baseDirectory = Util::getAgoraVar('portaldata') . 'data';

        // Combina la ruta base con la ruta adicional de la URL
        $directory = $baseDirectory . ($path ? '/' . $path : '');

         // Verifica si la ruta es un directorio
        if (!is_dir($directory)) {
            die('NO es un directorio');
        }

        // Obtén la lista de archivos y carpetas en el directorio
        $files = Util::getFiles($directory);

        // Obtén la ruta del directorio padre si hay un path
        $parentDirectory = $path ? rtrim(dirname($directory), '/') : null;

        // Reemplaza la barra inclinada al principio de la ruta para evitar duplicados en la URL
        $relativePath = ltrim(str_replace($baseDirectory, '', rtrim($directory, '/')), '/');

        // Asegúrate de que $relativePath sea siempre algo válido para evitar problemas con la URL
        $relativePath = $relativePath ?: null;

        return view('admin.files.index', compact('baseDirectory', 'directory', 'files', 'relativePath', 'parentDirectory'));
    }

}
