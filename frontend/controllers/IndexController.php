<?php
/**
 * Created by PhpStorm.
 * User: mseymour
 * Date: 21/08/2017
 * Time: 19:26
 */

namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        echo "frontend";
    }


    public function notFoundAction()
    {
        echo "404";
    }
}
