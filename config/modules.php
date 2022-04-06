<?php
// Register the installed modules
$application->registerModules(
    [
        'core' => [
            'className' => 'Multiple\Core\Module',
            'path' => '../core/Module.php'
        ],
        'backend' => [
            'className' => 'Multiple\Backend\Module',
            'path' => '../backend/Module.php'
        ],
        'frontend' => [
            'className' => 'Multiple\Frontend\Module',
            'path' => '../frontend/Module.php'
        ]

    ]
);