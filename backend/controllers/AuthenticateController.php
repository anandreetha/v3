<?php
/**
 * Created by PhpStorm.
 * User: shabnam.sidhik
 * Date: 25/09/2017
 * Time: 16:35
 */

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use \Zend\Config\Config;
use \Zend\Config\Factory;
use \Zend\Http\PhpEnvironment\Request;


class AuthenticateController extends Controller
{

    public function authAction()
    {
        if ($this->request->isPost()) {

            $sessionOrgs = $this->authenticationService->getAccessToken_UserSpecificOrgs($this->request->getPost("response"));

            $this->session->set('session-token', $sessionOrgs[0]);
            $this->session->set('countries', $sessionOrgs[1]);
            $this->session->set('regions', $sessionOrgs[2]);
            $this->session->set('chapters', $sessionOrgs[3]);
            $this->session->set('userId', $sessionOrgs[4]);
            $this->session->set('username',$sessionOrgs[5]);
            $this->session->set('session',$sessionOrgs[6]);
			$this->session->set('orgsession',$sessionOrgs[7]);
            $this->response->redirect('backend/website/list');
            $this->view->disable();
            return;
        }


    }

    public function logoutAction() {
        // For 'safety', clear each variable before destroying the whole session
        $this->session->remove('session-token');
        $this->session->remove('countries');
        $this->session->remove('regions');
        $this->session->remove('chapters');
        $this->session->remove('userId');
        $this->session->remove('username');
        $this->session->remove('session');
        $this->session->destroy();

        $this->response->redirect($this->config->general->baseUrl . '/web/secure/home');
        $this->view->disable();
    }
}