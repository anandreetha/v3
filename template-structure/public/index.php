<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application as BaseApplication;
use Phalcon\Events\Manager;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/application');


class Application extends BaseApplication
{
    protected function registerAutoloaders()
    {
        $loader = new Loader();
        $loader->registerDirs(
            [
                APP_PATH."/controllers/",
            ]
        );
        $loader->register();
    }

    /**
     * This methods registers the services to be used by the application
     */
    protected function registerServices()
    {
        $di = new FactoryDefault();

        // Registering a router
        $di->set(
            "router",
            function () {

                $router = new Router(false);

                $router->removeExtraSlashes(true);

                $router->add(
                    "/:params",
                    [
                        "controller" => "index",
                        "action"     => "render",
                        "locale"     => 1,
                    ]
                );

                return $router;

            }
        );


        // Registering a dispatcher
        $di->set('dispatcher', function () {
            // Create an EventsManager
            $eventsManager = new Manager();

            // Attach a listener
            $eventsManager->attach("dispatch:beforeException", function ($event, $dispatcher, $exception) {
                // Handle exceptions
                if ($exception) {
                    $dispatcher->forward(
                        array(
                            'controller' => 'index',
                            'action' => 'maintenance'
                        )
                    );
                    return false;
                }


            });

            $dispatcher = new Dispatcher();
            // Bind the EventsManager to the dispatcher
            $dispatcher->setEventsManager($eventsManager);
            return $dispatcher;
        });

        // Registering a Http\Response
        $di->set(
            "response",
            function () {
                return new Response();
            }
        );
        // Registering a Http\Request
        $di->set(
            "request",
            function () {
                return new Request();
            }
        );
        // Registering the view component
        $di->set(
            "view",
            function () {
                $view = new View();
                $view->setViewsDir(APP_PATH."/views/");
                return $view;
            }
        );


        $this->setDI($di);
    }

    public function main()
    {
        $this->registerServices();
        $this->registerAutoloaders();
        echo $this->handle()->getContent();
    }
}

try {

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $application = new Application();
    $application->main();
} catch (\Exception $e) {

    echo $e->getMessage();
}