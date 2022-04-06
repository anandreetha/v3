<?php

use Phalcon\Mvc\Router;

$router = new Router(false);

$router->setDefaultModule("backend");

$router->removeExtraSlashes(true);

$router->add(
    "/:module/:controller/:action/:params",
    [
        "module"     => 1,
        "controller" => 2,
        "action"     => 3,
        "params"     => 4,
    ]
);


//$router->add(
//    '/:controller/:action/([a-zA-Z0-9\-\.]+)/{language:[a-z]{2}}/([a-z\-\.]+)',
//    [
//        "module"     => "backend",
//        'controller' => 'render',
//        'action' => 'preview',
//        'domain' => 3,
//        'slug' => 5,
//
//    ]
//);


$router->add(
    "/backend/render/preview/([a-zA-Z0-9\-\.]+)/([^\/]+)/([a-zA-Z\-]{2,10})/(.*)",
    [
        "module" => "backend",
        "controller" => "render",
        "action" => "previewChapter",
        'domain' => 1,
        'chapter' => 2,
        'language' => 3,
        'slug' => 4,
    ]
);


//$router->add(
//    'backend/page/view/{language:[a-z]{2}}/([0-9])',
//    [
//        "module"     => "backend",
//        'controller' => 'page',
//        'action' => 'view',
//
//    ]
//);

//Route for previewing country & region sites
$router->add(
    "/preview/([a-zA-Z0-9\-\.]+)/([a-zA-Z\-]{2,10})/(.*)",
    [
        "module"     => "backend",
        "controller"     => "render",
        "action"     => "preview",
        'domain' => 1,
        'language' => 2,
        'slug' => 3,
    ]
);

//Route for previewing chapter sites
$router->add(
    "/preview/([a-zA-Z0-9\-\.]+)/([^\/]+)/([a-zA-Z\-]{2,10})/(.*)",
    [
        "module"     => "backend",
        "controller"     => "render",
        "action"     => "previewChapter",
        'domain' => 1,
        'chapter' => 2,
        'language' => 3,
        'slug' => 4,
    ]
);



$router->add(
    "/consume/:int",
    [
        "module"     => "frontend",
        "controller"     => "consume",
        "action"     => "chapterInfo",
        "chapterID"     => 1,
    ]
);


$router->notFound(
    array(
        "module"     => "backend",
        "controller" => "error",
        "action" => "handle404"
    )
);

return $router;
