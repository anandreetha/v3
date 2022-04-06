<?php

namespace Multiple\Backend;

use Multiple\Backend\Services;
use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View;
use Exception;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

class Module implements ModuleDefinitionInterface
{
    /**
     * Register an  autoloader specific for this module only
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        // Register required namespaces for this module.
        $loader->registerNamespaces(
            [
                'Multiple\Backend\Controllers'       => APP_PATH.'backend/controllers/',
                'Multiple\Backend\Models'            => APP_PATH.'backend/models/',
                'Multiple\Backend\Validators'        => APP_PATH.'backend/validators/',
                'Multiple\Backend\Exceptions' => APP_PATH . 'backend/exceptions/',
                'Multiple\Backend\Services'          => APP_PATH.'backend/services/',
                'Multiple\Backend\Services\Service'  => APP_PATH.'backend/services/service',
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

        // Registering a module specific dispatcher
        $di->set(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();

                $dispatcher->setDefaultNamespace('Multiple\Backend\Controllers');

                return $dispatcher;
            }
        );


        // Registering a module specific website helper
        $di->setShared(
            'websiteHelper',
            function () {
                return new \Multiple\Backend\Helpers\WebsiteHelper();
            }
        );

        // Registering a module specific view helper
        $di->setShared(
            'viewHelper',
            function () {
                return new \Multiple\Backend\Helpers\ViewHelper();
            }
        );

        // Registering a module specific authenticationService
        $di->setShared(
            'authenticationService',
            function () {
                return new Services\Service\AuthenticationService();
            }
        );

        // Registering a module specific websiteService
        $di->setShared(
            'websiteService',
            function () {
                return new Services\Service\WebsiteService();
            }
        );

        // Registering a module specific pageService
        $di->setShared(
            'pageService',
            function () {
                return new Services\Service\PageService();
            }
        );

        // Registering a module specific renderService
        $di->setShared(
            'renderService',
            function () {
                return new Services\Service\RenderService();
            }
        );

        // Registering a module specific renderService
        $di->setShared(
            'widgetRenderService',
            function () {
                return new Services\Service\WidgetRenderService();
            }
        );

        // Registering a module specific view component
        $di->set(
            'view',
            function () {
                $view = new View();

                $view->setViewsDir(APP_PATH.'backend/views/');

                // Make use of our core layouts and partials
                $view->setLayoutsDir(APP_PATH.'core/views/layouts/');
                $view->setPartialsDir(APP_PATH.'core/views/');
                $view->setTemplateAfter('admin');

                return $view;
            }
        );
    }
}