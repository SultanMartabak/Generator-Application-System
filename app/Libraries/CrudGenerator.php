<?php

namespace App\Libraries;

class CrudGenerator
{
    protected $stubsPath;
    protected $messages = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->stubsPath = APPPATH . 'Commands/stubs/';
    }

    /**
     * Main function to generate all CRUD files.
     */
    public function generate(string $urlPath, string $menuName): array
    {
        $nameInfo = $this->parseName($urlPath);

        $this->createController($nameInfo, $menuName);
        $this->createModel($nameInfo, $menuName);
        $this->createViewIndex($nameInfo, $menuName);
        $this->createViewListPartial($nameInfo, $menuName);
        $this->createViewTrash($nameInfo, $menuName);

        return [
            'messages'         => $this->messages,
            'route_suggestion' => $this->getRouteSuggestion($nameInfo),
        ];
    }

    /**
     * Parses the provided URL path to derive all necessary names and paths.
     */
    protected function parseName(string $urlPath): array
    {
        $parts = explode('/', strtolower(trim($urlPath, '/')));
        $name = array_pop($parts); // Ini adalah segmen terakhir dari URL path, misal: "data-kendaraan"
        
        // Untuk nama Model dan Controller (PascalCase, tanpa tanda hubung/spasi)
        // Contoh: "data-kendaraan" -> "DataKendaraan"
        // Contoh: "user_roles" -> "UserRoles"
        $modelName = str_replace(['-', '_', ' '], '', ucwords($name, '-_ '));
        $controllerName = $modelName . 'Controller';

        // Untuk nama Tabel (snake_case, tanpa tanda hubung)
        // Contoh: "kendaraan/data-kendaraan" -> "kendaraan_data_kendaraan"
        $tableName = str_replace(['/', '-'], '_', strtolower(trim($urlPath, '/')));

        // Untuk nama variabel Model (camelCase)
        // Contoh: "DataKendaraan" -> "dataKendaraan"
        $modelVarName = lcfirst($modelName);
        
        // Untuk path View (kebab-case, sesuai struktur URL path)
        // Contoh: "kendaraan/data-kendaraan" -> "kendaraan/data-kendaraan"
        $viewPath = strtolower(trim($urlPath, '/')); // Sudah kebab-case dari urlPath

        $moduleNamespaceParts = array_map('ucfirst', $parts);
        $moduleNamespace = !empty($moduleNamespaceParts) ? '\\' . implode('\\', $moduleNamespaceParts) : '';
        $moduleDiskPath = !empty($moduleNamespaceParts) ? implode('/', $moduleNamespaceParts) . '/' : '';

        return compact('urlPath', 'name', 'modelName', 'controllerName', 'tableName', 'modelVarName', 'viewPath', 'moduleDiskPath', 'moduleNamespace');
    }

    protected function createController(array $nameInfo, string $menuName)
    {
        $path = APPPATH . 'Controllers/' . $nameInfo['moduleDiskPath'] . $nameInfo['controllerName'] . '.php';
        $template = $this->readStub('controller.stub');
        $template = $this->replacePlaceholders($template, $nameInfo, $menuName);
        $this->writeFile($path, $template, 'Controller');
    }

    protected function createModel(array $nameInfo, string $menuName)
    {
        $path = APPPATH . 'Models/' . $nameInfo['moduleDiskPath'] . $nameInfo['modelName'] . '.php';
        $template = $this->readStub('model.stub');
        $template = $this->replacePlaceholders($template, $nameInfo, $menuName);
        $this->writeFile($path, $template, 'Model');
    }

    protected function createViewIndex(array $nameInfo, string $menuName)
    {
        $path = APPPATH . 'Views/' . $nameInfo['viewPath'] . '/index.php';
        $template = $this->readStub('view_index.stub');
        $template = $this->replacePlaceholders($template, $nameInfo, $menuName);
        $this->writeFile($path, $template, 'View Index');
    }

    protected function createViewListPartial(array $nameInfo, string $menuName)
    {
        $path = APPPATH . 'Views/' . $nameInfo['viewPath'] . '/_list_partial.php';
        $template = $this->readStub('_list_partial.stub');
        $template = $this->replacePlaceholders($template, $nameInfo, $menuName);
        $this->writeFile($path, $template, 'View List Partial');
    }

    protected function createViewTrash(array $nameInfo, string $menuName)
    {
        $path = APPPATH . 'Views/' . $nameInfo['viewPath'] . '/trash.php';
        $template = $this->readStub('view_trash.stub');
        $template = $this->replacePlaceholders($template, $nameInfo, $menuName);
        $this->writeFile($path, $template, 'View Trash');
    }

    protected function getRouteSuggestion(array $nameInfo): string
    {
        $urlPath = $nameInfo['urlPath'];
        $shortControllerPath = $nameInfo['controllerName'];
        $pathParts = explode('/', $urlPath);

        if (count($pathParts) > 1) {
            // Case for URL with module, e.g., "kendaraan/data-kendaraan"
            $groupName = $pathParts[0];
            // Handle potentially deeper paths like master/data/barang
            $innerPath = implode('/', array_slice($pathParts, 1)); 
            
            // Build the namespace for the group option. e.g., App\Controllers\Kendaraan
            $groupNamespace = 'App\\Controllers' . $nameInfo['moduleNamespace'];

            return <<<EOT
// In app/Config/Routes.php:
// Grup ini menerapkan filter 'rbac' dan namespace untuk kerapian.
\$routes->group('{$groupName}', ['filter' => 'rbac', 'namespace' => '{$groupNamespace}'], static function (\$routes) {
    // Rute untuk {$nameInfo['controllerName']}
    \$routes->get('{$innerPath}', '{$shortControllerPath}::index');
    \$routes->post('{$innerPath}/create', '{$shortControllerPath}::create');
    \$routes->post('{$innerPath}/update/(:num)', '{$shortControllerPath}::update/$1');
    \$routes->post('{$innerPath}/delete/(:num)', '{$shortControllerPath}::delete/$1');
    \$routes->get('{$innerPath}/trash', '{$shortControllerPath}::trash');
    \$routes->get('{$innerPath}/restore/(:num)', '{$shortControllerPath}::restore/$1');
    \$routes->get('{$innerPath}/purge/(:num)', '{$shortControllerPath}::purge/$1');
});
EOT;
        } else {
            // Case for simple URL, e.g., "users"
            $groupName = $pathParts[0];
            $groupNamespace = 'App\\Controllers'; // No module namespace

            return <<<EOT
// In app/Config/Routes.php:
// Grup ini menerapkan filter 'rbac' dan namespace untuk kerapian.
\$routes->group('{$groupName}', ['filter' => 'rbac', 'namespace' => '{$groupNamespace}'], static function (\$routes) {
    // Rute untuk {$nameInfo['controllerName']}
    \$routes->get('/', '{$shortControllerPath}::index');
    \$routes->post('create', '{$shortControllerPath}::create');
    \$routes->post('update/(:num)', '{$shortControllerPath}::update/$1');
    \$routes->post('delete/(:num)', '{$shortControllerPath}::delete/$1');
    \$routes->get('trash', '{$shortControllerPath}::trash');
    \$routes->get('restore/(:num)', '{$shortControllerPath}::restore/$1');
    \$routes->get('purge/(:num)', '{$shortControllerPath}::purge/$1');
});
EOT;
        }
    }

    protected function readStub(string $filename): string
    {
        return file_get_contents($this->stubsPath . $filename);
    }

    protected function replacePlaceholders(string $template, array $nameInfo, string $menuName): string
    {
        $replacements = array_merge($nameInfo, ['menuName' => $menuName]);
        $placeholders = array_map(fn($key) => "{{{$key}}}", array_keys($replacements));
        return str_replace($placeholders, array_values($replacements), $template);
    }

    protected function writeFile(string $path, string $content, string $fileType)
    {
        $this->ensureDirectoryExists(dirname($path));

        if (file_exists($path)) {
            $this->messages[] = "Skipped: {$fileType} file already exists at " . str_replace(ROOTPATH, '', $path);
            return;
        }

        if (file_put_contents($path, $content) === false) {
            $this->messages[] = "Error: Could not write {$fileType} file to " . str_replace(ROOTPATH, '', $path);
        } else {
            $this->messages[] = "Created: {$fileType} file at " . str_replace(ROOTPATH, '', $path);
        }
    }

    protected function ensureDirectoryExists(string $path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Moves generated CRUD files (Controller, Model, Views) to writable/trash.
     * This is typically called when a menu item is soft-deleted.
     *
     * @param string $urlPath The URL path of the menu.
     */
    public function moveGeneratedFilesToTrash(string $urlPath): void
    {
        $nameInfo = $this->parseName($urlPath);
        $filePaths = $this->getCrudFilePaths($nameInfo, false); // Get original paths

        foreach ($filePaths as $type => $path) {
            if (!file_exists($path)) {
                continue; // File doesn't exist, nothing to move
            }

            $trashDir = WRITEPATH . 'trash/' . $type . '/';
            $this->ensureDirectoryExists($trashDir);

            // For views, we need to move the whole directory
            if ($type === 'views') {
                $destination = $trashDir . $nameInfo['viewPath']; // Move the entire view directory
                if (is_dir($path)) {
                    if (rename($path, $destination)) {
                        $this->messages[] = "Moved view directory to trash: " . str_replace(ROOTPATH, '', $destination);
                    } else {
                        $this->messages[] = "Error moving view directory to trash: " . str_replace(ROOTPATH, '', $path);
                    }
                }
            } else { // For controller and model files
                $destination = $trashDir . basename($path);
                if (rename($path, $destination)) {
                    $this->messages[] = "Moved {$type} file to trash: " . str_replace(ROOTPATH, '', $destination);
                } else {
                    $this->messages[] = "Error moving {$type} file to trash: " . str_replace(ROOTPATH, '', $path);
                }
            }
        }
    }

    /**
     * Restores generated CRUD files from writable/trash back to their original locations.
     * This is typically called when a menu item is restored from soft-delete.
     *
     * @param string $urlPath The URL path of the menu.
     */
    public function restoreGeneratedFilesFromTrash(string $urlPath): void
    {
        $nameInfo = $this->parseName($urlPath);
        $filePathsInTrash = $this->getCrudFilePaths($nameInfo, true); // Get paths in trash

        foreach ($filePathsInTrash as $type => $trashPath) {
            if (!file_exists($trashPath)) {
                continue; // File not in trash
            }
            $originalPath = $this->getOriginalCrudFilePath($nameInfo, $type);
            $this->ensureDirectoryExists(dirname($originalPath)); // Ensure parent directory exists

            if (rename($trashPath, $originalPath)) {
                $this->messages[] = "Restored {$type} file from trash: " . str_replace(ROOTPATH, '', $originalPath);
            } else {
                $this->messages[] = "Error restoring {$type} file from trash: " . str_replace(ROOTPATH, '', $trashPath);
            }
        }
    }

    /**
     * Permanently deletes generated CRUD files from writable/trash.
     * This is typically called when a menu item is purged.
     *
     * @param string $urlPath The URL path of the menu.
     */
    public function deleteGeneratedFilesPermanently(string $urlPath): void
    {
        $nameInfo = $this->parseName($urlPath);
        $filePathsInTrash = $this->getCrudFilePaths($nameInfo, true); // Get paths in trash

        foreach ($filePathsInTrash as $type => $trashPath) {
            if (!file_exists($trashPath)) {
                continue; // File not in trash
            }

            if ($type === 'views' && is_dir($trashPath)) {
                // Recursively delete the view directory
                $this->deleteDirectory($trashPath);
                $this->messages[] = "Permanently deleted view directory from trash: " . str_replace(ROOTPATH, '', $trashPath);
            } elseif (unlink($trashPath)) {
                $this->messages[] = "Permanently deleted {$type} file from trash: " . str_replace(ROOTPATH, '', $trashPath);
            } else {
                $this->messages[] = "Error permanently deleting {$type} file from trash: " . str_replace(ROOTPATH, '', $trashPath);
            }
        }
    }

    /**
     * Helper to recursively delete a directory.
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * Helper to get the full paths of generated CRUD files.
     * @param array $nameInfo Parsed name information.
     * @param bool $inTrash If true, returns paths in writable/trash.
     * @return array Associative array of file paths.
     */
    protected function getCrudFilePaths(array $nameInfo, bool $inTrash = false): array
    {
        $basePath = $inTrash ? WRITEPATH . 'trash/' : APPPATH;
        
        // For controllers and models, the path includes module subdirectories
        $controllerPath = $basePath . 'Controllers/' . $nameInfo['moduleDiskPath'] . $nameInfo['controllerName'] . '.php';
        $modelPath = $basePath . 'Models/' . $nameInfo['moduleDiskPath'] . $nameInfo['modelName'] . '.php';
        
        // For views, the path is the view directory itself
        $viewPath = $basePath . 'Views/' . $nameInfo['viewPath'];

        return [
            'controllers' => $controllerPath,
            'models'      => $modelPath,
            'views'       => $viewPath, // This is a directory
        ];
    }

    /**
     * Helper to get the original path of a CRUD file.
     * Used by restoreGeneratedFilesFromTrash.
     */
    protected function getOriginalCrudFilePath(array $nameInfo, string $type): string
    {
        switch ($type) {
            case 'controllers':
                return APPPATH . 'Controllers/' . $nameInfo['moduleDiskPath'] . $nameInfo['controllerName'] . '.php';
            case 'models':
                return APPPATH . 'Models/' . $nameInfo['moduleDiskPath'] . $nameInfo['modelName'] . '.php';
            case 'views':
                return APPPATH . 'Views/' . $nameInfo['viewPath'];
            default:
                return ''; // Should not happen
        }
    }
}