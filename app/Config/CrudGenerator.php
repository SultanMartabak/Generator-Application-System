<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CrudGenerator extends BaseConfig
{
    public $templates = [
        'basic' => [
            'name' => 'Basic CRUD',
            'description' => 'Simple CRUD with basic features',
            'fields' => ['name', 'description', 'is_active']
        ],
        'advanced' => [
            'name' => 'Advanced CRUD',
            'description' => 'CRUD with file upload and additional features',
            'fields' => ['name', 'description', 'image', 'files', 'is_active']
        ],
        'api' => [
            'name' => 'API CRUD',
            'description' => 'CRUD with RESTful API endpoints',
            'with_api' => true
        ]
    ];

    public $defaultTemplate = 'basic';

    // Default paths for file generation
    public $paths = [
        'controllers' => APPPATH . 'Controllers/',
        'models' => APPPATH . 'Models/',
        'views' => APPPATH . 'Views/',
        'trash' => WRITEPATH . 'trash/'
    ];
}
