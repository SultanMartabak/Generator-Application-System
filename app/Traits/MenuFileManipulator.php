<?php

namespace App\Traits;

trait MenuFileManipulator
{
    /**
    * Parses a URL path into its components.
     * e.g., "kendaraan/data-kendaraan" -> [moduleName => "Kendaraan", controllerName => "DataKendaraan", viewPath => "kendaraan/data_kendaraan"]
     */
    private function parseUrlPath(string $urlPath): array
    {
        $segments = explode('/', trim($urlPath, '/'));
        $moduleName = '';
        $controllerName = '';
        $viewPath = '';
        $controllerSegment = '';

        if (count($segments) > 1) {
            $moduleName = ucfirst($segments[0]);
            $controllerSegment = $segments[1];
        } else {
             $controllerSegment = $segments[0];
        }
        
        // Convert 'data-kendaraan' to 'DataKendaraan' for Controller/Model names
        $controllerName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controllerSegment)));

        // Convert 'data-kendaraan' to 'data_kendaraan' for view path
        $viewSegment = str_replace('-', '_', strtolower($controllerSegment));
        $viewPath = $moduleName ? strtolower($moduleName) . '/' . $viewSegment : $viewPath;

        return [
            'moduleName'     => $moduleName,
            'controllerName' => $controllerName,
            'viewPath'       => $viewPath,
        ];
    }

     /**
     * Mendapatkan path file dan direktori di lokasi aplikasi (app/).
     */
    protected function getAppPaths(string $urlPath): array
    {
        $parsed = $this->parseUrlPath($urlPath);
        $moduleSubPath = $parsed['moduleName'] ? $parsed['moduleName'] . '/' : '';

        return [
            'controller' => APPPATH . 'Controllers/' . $moduleSubPath . $parsed['controllerName'] . '.php',
            'model'      => APPPATH . 'Models/' . $moduleSubPath . $parsed['controllerName'] . 'Model.php',
            'view_dir'   => APPPATH . 'Views/' . $parsed['viewPath'],
        ];
    }

    /**
     * Mendapatkan path file dan direktori di folder sampah (writable/trash/).
     */
    protected function getTrashPaths(string $urlPath): array
    {
        $parsed = $this->parseUrlPath($urlPath);
        $moduleSubPath = $parsed['moduleName'] ? $parsed['moduleName'] . '/' : '';

        return [
           'controller' => WRITEPATH . 'trash/Controllers/' . $moduleSubPath . $parsed['controllerName'] . '.php',
            'model'      => WRITEPATH . 'trash/Models/' . $moduleSubPath . $parsed['controllerName'] . 'Model.php',
            'view_dir'   => WRITEPATH . 'trash/Views/' . $parsed['viewPath'],
        ];
    }

    /**
     * Helper untuk menghapus direktori secara rekursif.
     *
     * @param string $dir Path direktori yang akan dihapus.
     * @return bool True jika berhasil dihapus, false jika gagal atau direktori tidak ada.
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}