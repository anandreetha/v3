<?php
/**
 * Created by PhpStorm.
 * User: shabnam.sidhik
 * Date: 29/09/2017
 * Time: 14:36
 */

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class ErrorController extends Controller
{

    public function initialize()
    {
        $this->assets->addCss('css/AdminLTE.css');
        $this->assets->addCss('css/skin-red.css');
        $this->assets->addCss('css/ionicons.css');
        $this->assets->addCss('css/theme.css');
        $this->assets->addJs('js/adminlte.min.js');

        $this->view->contentTitle = "Error";
        $this->view->fullName = 'Session Error';
        $this->view->profileImage = $this->config->general->baseUri . 'img/default_profile.gif';
        $this->view->showNavItem = ["country" => false, "region" => false, "chapter" => false, "all" => false];
        $this->view->htmlTags = "dir='ltr' lang='en'"; // we have no session, so we can't check the user's locale for dir/lang. We could use the accept-language header if really needed, though?!
    }

    public function authAction()
    {
        $this->view->contentSubTitle = "Invalid Session";
        $this->view->errorData = "Your session is invalid. Please <a href=\"". $this->config->general->baseUrl ."/web/secure/cmsv3auth\">login again</a>.";
    }

    public function permissionDeniedAction()
    {
        $this->view->contentSubTitle = "Permission Denied";
        $this->view->errorData = 'You do not have permission to access the requested page. If you believe this is in error, please <a href="javascript:history.back();">go back</a> and try again.';
    }

    public function handle404Action()
    {
        $this->response->setStatusCode(404, 'Not Found');
    }

    public function handle500Action()
    {
        $this->response->setStatusCode(500, 'Internal Error');
    }

}
