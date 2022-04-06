<?php

namespace Multiple\Backend\Controllers;

use Multiple\Core\Models\Language;
use Multiple\Core\Models\WebsiteOrg;
use Phalcon\Mvc\Controller;
use Exception;

class BaseController extends Controller
{

    protected function failAndRedirect($redirectUrl)
    {
        $this->response->redirect($redirectUrl);
        $this->view->disable();
    }

    protected function hasUserGotPermissionToAccessWebsite($website)
    {
        if($this->session->get("session") == null) return false;

        $orgIds = array();
        foreach ($website->getWebsiteOrg() as $val) {
            $orgIds[] = $val->org_id;
        }
        return $this->authenticationService->checkOrgPermissions($this->constants->getWebsitePermission($website->type_id), $orgIds, $this->session->get("session"),$website);

    }
}