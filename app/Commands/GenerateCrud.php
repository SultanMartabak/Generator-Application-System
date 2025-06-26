<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\CrudGenerator;

class GenerateCrud extends BaseCommand
{
    protected $group       = 'Generators';
    protected $name        = 'generate:crud';
    protected $description = 'Generates a full CRUD module (Controller, Model, Views) based on a URL path.';
    protected $usage       = 'generate:crud <url_path> <menu_name>';
    protected $arguments   = [
        'url_path'  => 'The URL path for the module (e.g., "kendaraan/data" or "users").',
        'menu_name' => 'The display name for the menu (e.g., "Data Kendaraan").',
    ];

    public function run(array $params)
    {
        $urlPath = $params[0] ?? null;
        $menuName = $params[1] ?? null;

        if (!$urlPath || !$menuName) {
            CLI::error('Both <url_path> and <menu_name> arguments are required.');
            CLI::write('Usage: ' . $this->usage);
            return;
        }

        CLI::write('Starting CRUD generation for URL: ' . $urlPath . ' (Menu: ' . $menuName . ')', 'yellow');

        $generator = new CrudGenerator();
        $result = $generator->generate($urlPath, $menuName);

       foreach ($result['messages'] as $message) {
            if (str_starts_with($message, 'Error:')) {
                CLI::error(substr($message, 7));
            } elseif (str_starts_with($message, 'Skipped')) {
                CLI::write($message, 'yellow');
            } else {
                CLI::write($message, 'green');
            }
        }

        CLI::write("\n--- Suggested Routes ---", 'yellow');
        CLI::write($result['route_suggestion'], 'cyan');
        CLI::write("\nCRUD generation complete!", 'green');
    }
}