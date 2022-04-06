<?php

namespace Multiple\Frontend;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Escaper;

class Module implements ModuleDefinitionInterface
{
    /**
     * Register a specific autoloader for the module
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            [
                'Multiple\Frontend\Controllers' => APP_PATH . 'frontend/controllers/',
                'Multiple\Frontend\Models' => APP_PATH . 'frontend/models/',
                'Multiple\Frontend\Validators' => APP_PATH . 'frontend/validators/',
                'Multiple\Backend\Helpers'  => APP_PATH.'backend/helpers',

            ]
        );

        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(DiInterface $di)
    {
        // Registering a dispatcher
        $di->set(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();

                $dispatcher->setDefaultNamespace('Multiple\Frontend\Controllers');

                return $dispatcher;
            }
        );


        $di->set(
            'escaper',
            function () {
                $dispatcher = new Escaper();

                return $dispatcher;
            }
        );

        // Registering the view component
        $di->set(
            'view',
            function () {
                $view = new View();

                $view->setViewsDir(APP_PATH . 'frontend/views/');

                return $view;
            }
        );

        // Registering a module specific website helper
        $di->setShared(
            'websiteHelper',
            function () {
                return new \Multiple\Backend\Helpers\WebsiteHelper();
            }
        );
    }
}
