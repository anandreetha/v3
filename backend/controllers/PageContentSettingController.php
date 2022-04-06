<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 19/09/2017
 * Time: 15:36
 */


namespace Multiple\Backend\Controllers;

use Multiple\Backend\Validators\AddWebsiteSettingValidator;
//use Multiple\Core\Models\PageSettings;
use Multiple\Core\Models\PageContentSettings as ContentSettings;
use Phalcon\Http\Response;

use Phalcon\Mvc\Controller;

class PageContentSettingController extends BaseController
{
    public function editAction($pageContentSettingId){

        // To mark if we've successfully updated a page setting
        $pageSettingSaved = false;

        $pageContentSettingId = $this->filter->sanitize($pageContentSettingId, 'int');
        $pagesetting = ContentSettings::findfirst(
            [
                'id = :id:',
                'bind' => [
                    'id' => $pageContentSettingId
                ],

            ]

        );

        if(!$this->hasUserGotPermissionToAccessWebsite($pagesetting->PageContent->Page->Website)){
            $pageSettingSaved = false;
        } else {

            if ($this->request->isPost()) {

                $validation = new AddWebsiteSettingValidator();

                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->flash->error($message);
                    }
                } else {
                    $pagesetting->value = $this->request->getPost("settingValue", 'string');
                    $pageSettingSaved = $pagesetting->save();
                    if ($pageSettingSaved === false) {
                        $this->flash->error('Setting cannot be added');
                    } else {
                        $this->flash->success("Setting updated");
                    }
                }
            }
        }

        // If we have an xhr request then we need to respond differently to the request
        if ($this->request->isAjax()) {

            $response = new Response();
            $response->setHeader('Content-Type', 'application/json');

            if ($pageSettingSaved === false) {
                $response->setStatusCode(400, "Bad Request");
                $response->setContent(json_encode(array('error' => 'Setting cannot be added')));
            }
            else {
                $response->setContent(json_encode(array('complete' => true)));
            }

            return $response;

        }

        // Fall through to here and load the ajax layout (which is a modal) if it's not an xhr request
        $this->view->pagesetting = $pagesetting;
        $this->view->setTemplateAfter('ajax-layout');
    }

}