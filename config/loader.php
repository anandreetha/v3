<?php

// Register composers autoloader


use Phalcon\Loader;

$loader = new Loader();

$loader->registerFiles(
    [
        BASE_PATH . '/vendor/autoload.php'
    ]
);

// Register shared common components
$loader->registerNamespaces(
    [
        'Multiple\Component' => '../components/',
        'Multiple\Locale' => '../locale/',
        'Multiple\Core\Utils' => '../core/utils/',
        'Multiple\Core\Factory' => '../core/factory/',
        'Multiple\Core\Misc' => '../core/misc/',
        'Multiple\Core\Models' => '../core/models/',
        'Multiple\Core\Services' => '../core/services/',
        'Multiple\Core\Validators' => '../core/validators/',
        'Multiple\Core\Validators\Settings' => '../core/validators/settings',
        'Multiple\Core\Validators\Custom' => '../core/validators/custom',
        'Multiple\Core\Services\Service' => '../core/services/service',
        'Multiple\Core\Services\Factory' => '../core/services/factory',
        'Multiple\Core\Widgets' => '../core/widgets/',
        'Multiple\Core\Widgets\Widget' => '../core/widgets/widget',
        'Multiple\Core\Widgets\Widget\Country' => '../core/widgets/widget/country',
        'Multiple\Core\Widgets\Widget\Country\Page' => '../core/widgets/widget/country/page',
        'Multiple\Core\Widgets\Widget\Region\Page' => '../core/widgets/widget/region/page',
        'Multiple\Core\Widgets\Widget\Chapter\Page' => '../core/widgets/widget/chapter/page',
        'Multiple\Core\Widgets\Factory' => '../core/widgets/factory',
    ]
);



$loader->register();
