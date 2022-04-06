<?php

namespace Multiple\Backend\Controllers;

use Multiple\Backend\Services\Service\ImageCleanupService;
use Multiple\Core\Misc\SettingMappingService;
use Multiple\Core\Models\CommonLibrary;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentWidgets;
use Multiple\Core\Models\PageContentWidgetSettings;
use Multiple\Core\Validators\Custom\ImageDimensionValidator;
use Phalcon\Exception;
use Phalcon\Http\Response;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;

class PagecontentwidgetController extends BackendBaseController
{

    public function addAjaxAction($pageContentId = 0)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');
        if ($this->request->isAjax() && $pageContentId > 0 && $this->request->isPost()) {
            $pageContent = PageContent::findFirst($pageContentId);
            if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
                $this->failAndRedirect('backend/error/permissionDenied');
                return;
            }

            $associatedWidgets = $pageContent->getWidgets(
                [
                    "order" => "widget_order DESC"
                ]
            );

            if (count($associatedWidgets) > 0) {
                $order = $associatedWidgets->getFirst()->getWidgetOrder() + 1;
            } else {
                $order = 0;
            }

            $pageContentWidget = new PageContentWidgets();
            $pageContentWidget->setPageContentId($pageContentId);
            $pageContentWidget->setWidgetId($this->filter->sanitize($this->request->getPost('newWebsiteWidget'), 'int'));
            $pageContentWidget->setWidgetOrder($order);

            $response = new Response();
            $response->setHeader('Content-Type', 'application/json');

            if ($pageContentWidget->save() === false) {
                $errorArray = [];
                $this->flash->error('Page Content Widget cannot be added to page:');
                foreach ($pageContentWidget->getMessages() as $message) {
                    $errorArray[] = $message;
                }

                return $response
                    ->setStatusCode(400)
                    ->setContent(json_encode($errorArray));

            }

            return $response
                ->setStatusCode(200)
                ->setContent(json_encode(array(
                            'pageContentWidget' => array(
                                'id' => $pageContentWidget->getId(),
                                'pageContentId' => $pageContentWidget->getPageContentId(),
                                'widgetId' => $pageContentWidget->getWidgetId(),
                                'widgetOrder' => $pageContentWidget->getWidgetOrder()))
                    )
                );


        }
        $this->view->setTemplateAfter('ajax-layout');

    }

    public function editAction($pageContentWidgetId)
    {
        $pageContentWidget = PageContentWidgets::findFirstById($pageContentWidgetId);

        $pageContent = $pageContentWidget->getPageContent();

        $website = $pageContent->getPage()->getWebsite();

        if (!$this->hasUserGotPermissionToAccessWebsite($website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $redirectToUrl = $this->tag->linkTo(
            array(
                "backend/page/editPage/" . $pageContent->id,
                '<i class="fa fa-arrow-left fa-lg pull-right"></i>',
                "class" => "newPageModal btn btn-default",
                "title" => $this->translator->_('cms.v3.admin.pages.backpage')
            )
        );

        $this->view->redirectToUrl = $redirectToUrl;

        $validator = $this->settingsValidatorFactory->getValidator($pageContentWidget->getWidget()->getName());

        if ($this->request->isPost()) {
            if ($pageContentWidget->getWidget()->getName() == "Slider") {
                // validation for slider only, because the slider validation is complex it can't be done inside
                // the actual class
                $this->validateIncomingData($this->request->getPost(), $validator);
            }

            $messages = $validator->validate($this->request->getPost());

            if (count($messages)) {
                $this->view->isError = true;
                foreach ($messages as $message) {
                    $this->flashSession->error($message);
                }
            } else {
                $isError = false;

                $pageContentWidgetSettings = $pageContentWidget->getPageContentWidgetSettings();

                // Update existing page page widget settings
                if (count($pageContentWidgetSettings) > 0) {
                    foreach ($this->request->getPost() as $key => $postSettingVal) {
                        if (strpos($key, 'edit_') !== false) {
                            $keyExploded = explode("_", $key);

                            $key = $this->filter->sanitize($keyExploded[1], 'int');

                            // Only get the setting if the id's match
                            $matchingPageContentWidgetSetting = $pageContentWidgetSettings->filter(
                                function ($pageContentWidgetSetting) use ($key) {
                                    if ($pageContentWidgetSetting->setting_id === $key) {
                                        return $pageContentWidgetSetting;
                                    }
                                }
                            );

                            if (!empty($matchingPageContentWidgetSetting)) {
                                $pageContentWidgetSetting = $matchingPageContentWidgetSetting[0];

                                $value = $postSettingVal;
                                $pageContentWidgetSetting->value = $this->filter->sanitize($value, 'string');
                                $pageContentWidget->PageContent->Page->last_modified = date('Y-m-d H:i:s');

                                if ($pageContentWidgetSetting->save() === false) {
                                    $this->flash->error(
                                        'Page Widget Setting ' . $pageContentWidgetSetting->Setting->name
                                        . ' cannot be edited:'
                                    );
                                    $isError = true;

                                    foreach ($pageContentWidgetSetting->getMessages() as $message) {
                                        $this->flash->error($message);
                                    }
                                }
                            } else {
                                // A new setting has been assigned to the widget since the user settings were created
                                $pageContentWidgetSettings = new PageContentWidgetSettings();
                                $pageContentWidgetSettings->setPageContentWidgetId($pageContentWidget->id);
                                $pageContentWidgetSettings->setSettingId($key);
                                $pageContentWidgetSettings->setValue($this->filter->sanitize($postSettingVal, 'string'));
                                $pageContentWidgetSettings->PageContentWidgets->PageContent->Page->last_modified =
                                    date('Y-m-d H:i:s');

                                if ($pageContentWidgetSettings->save() === false) {
                                    $this->flash->error(
                                        'Page Widget Setting cannot be edited:'
                                    );
                                    $isError = true;

                                    foreach ($pageContentWidgetSettings->getMessages() as $message) {
                                        $this->flash->error($message);
                                    }
                                }
                            }
                        }
                    }
                } else {
                    //No user defined page-widget settings exist at all
                    // Insert new settings
                    foreach ($pageContentWidget->widget->getWidgetSettings() as $pageContentWidgetSetting) {
                        $pageContentWidgetSettings = new PageContentWidgetSettings();
                        $pageContentWidgetSettings->setPageContentWidgetId($pageContentWidget->id);
                        $pageContentWidgetSettings->setSettingId($pageContentWidgetSetting->setting_id);

                        $pageContentWidgetSettings->setValue($this->filter->sanitize($this->request->getPost("edit_" . $pageContentWidgetSetting->setting_id), 'string'));

                        $pageContentWidgetSettings->PageContentWidgets->PageContent->Page->last_modified =
                            date('Y-m-d H:i:s');

                        if ($pageContentWidgetSettings->save() === false) {
                            $this->flash->error(
                                'Page Widget Setting ' . $pageContentWidgetSetting->Setting->name . ' cannot be edited:'
                            );
                            $isError = true;

                            foreach ($pageContentWidgetSettings->getMessages() as $message) {
                                $this->flash->error($message);
                            }
                        }
                    }
                }

                if (!$isError) {
                    $matchingPageContent = PageContent::findFirst($pageContentWidget->getPageContentId());

                    if ($matchingPageContent != false) {
                        $associatedPage = $matchingPageContent->getPage();

                        if ($associatedPage != false) {
                            $dateTimeNow = new \DateTime();
                            $associatedPage->setLastModified($dateTimeNow->format('Y-m-d H:i:s'));
                            $associatedPage->save();
                        }

                    }

                    $this->flash->success($this->translator->_('cms.v3.admin.editpage.saved'));
                }
            }
        }


        $mappedWidgetSettings = SettingMappingService::mapWidgetSettings($pageContentWidget);
        $mappedWidgetSettings->sortByDisplayOrder();


        $this->view->websiteId = $pageContentWidget->PageContent->Page->websiteId;
        $this->view->pageWidget = $pageContentWidget;
        $this->view->mappedWidgetSettings = $mappedWidgetSettings;
        $this->view->contentTitle =
            $this->translator->_($pageContentWidget->getWidget()->getTranslateToken()) . " " . $this->translator->_('cms.v3.admin.editpage.settings');
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.editpage.subheading');
    }



    public function deleteAction($pageContentWidgetId)
    {
        $pageContentWidgetId = $this->filter->sanitize($pageContentWidgetId, 'int');

        if ($this->request->isAjax()) {

            $errors = [];
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json');

            $pageContentWidget = PageContentWidgets::findFirst($pageContentWidgetId);

            if ($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)) {
                $this->failAndRedirect('backend/error/permissionDenied');
                return;
            }

            if ($pageContentWidget) {
                // Delete all images from the db when deleting the album
                if ($pageContentWidget->getWidgetId() == 8) {
                    $cleanupService = new ImageCleanupService();
                    $cleanupService->deleteImagesFromAlbum($pageContentWidgetId);
                }
                if ($pageContentWidget->delete() === false) {
                    $this->flash->error('Page Content Widget cannot be deleted:');
                    foreach ($pageContentWidget->getMessages() as $message) {
                        $errors = [$message];
                    }
                } else {
                    return $response->setContent(json_encode(array('success' => 'Page Content Widget has been deleted.')));
                }
            } else {
                $errors[] = 'Page Content Widget cannot be deleted as it does not exist';
            }

            return $response
                ->setStatusCode(400)
                ->setContent(json_encode(array('error' => $errors)));
        }
    }


    public function manageWidgetOrderAjaxAction()
    {

        if ($this->request->isAjax() && $this->request->isPost()) {

            foreach ($this->request->getPost("items") as $index => $item) {

                $item = $this->filter->sanitize($item, 'int');

                $pageContentWidget = PageContentWidgets::findFirst($item);

                // Break and return if we find anything not quite right
                if ($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)) {
                    $this->failAndRedirect('backend/error/permissionDenied');
                    return;
                }

                if ($pageContentWidget != false) {
                    $pageContentWidget->setWidgetOrder($index);
                    if ($pageContentWidget->save() == false) {
                        $this->flash->error('Page Content Widget ordering failed');
                    }
                }

            }
            $this->view->setTemplateAfter('ajax-layout');

        }
    }

    /**
     * Private mathod to validate slider input data. The slider form is generated dynamically. This hard code method take advantage
     * of hard coded array keYs to perform a check. The rules arE to validate a group of 3 fields. Those fields are the input text,link and image.
     * If any of those fields has data then check if all three of the group have data. If the validator return the error message back to the user.
     * @param $postData
     * @param $validation
     */
    private function validateIncomingData($postData, $validation)
    {
        $editInitPos = 25;
        $sliderNumber = 0;
        for ($editNumber = $editInitPos; $editNumber < $editInitPos + count($postData) - 3; $editNumber += 3) {
            $sliderNumber++;
            if (
            $postData["edit_" . ($editNumber + 2)]) {

                $validation->add("edit_" . ($editNumber + 2), new PresenceOf(
                    [
                        'message' => $this->translator->_('cms.v3.admin.slidersettings.asliderprefixtxt') . ' '.$sliderNumber .' '. $this->translator->_('cms.v3.admin.slidersettings.imgrequiredvalidationmsg'),
                    ]
                ));
            }

        }

        //Some additional validation if a Transition value has been provided, we can use the setting id as a reference as this is unique
        if ($postData["edit_40"]) {

            $validation->add("edit_40",
                new Numericality(
                    [
                        "message" => $this->translator->_('cms.v3.admin.slidersettings.transitionvaluenotnumericvalidationmsg'),
                    ]
                )
            );

        }

        //Validate those dimensions allow only 1800px wide by 863 height
        $imgDimensionValidator = new ImageDimensionValidator(863, 1800);
	
        if ($postData["edit_27"]) {
                $validation->add(
                    $postData["edit_27"],
                    new Callback(
                        [
                            'callback' => function () use (&$imgDimensionValidator, &$postData) {

                                // Check if the image is smaller or larger than we need for slider
                                if (!$imgDimensionValidator->validateImageDimensions($postData["edit_27"])) { 
								
									$imgdimensionsvalidationmsgnew=$this->translator->_('cms.v3.admin.slidersettings.imgdimensionsvalidationmsgnew' );
									$imgmsg=str_replace("{1}","863",$imgdimensionsvalidationmsgnew);									
									$imgmsg=str_replace("{0}","1800",$imgmsg);		
								
                                    return new PresenceOf(
                                        [
                                            'message' =>
                                                $this->translator->_(
                                                    'cms.v3.admin.slidersettings.asliderprefixtxt'
                                                ) .
                                                ' '.
                                                1 .
                                                ' '.
                                                $imgmsg
                                        ]
                                    );
                                }

                                return true;
                            }
                        ]
                    )
                );
        }

        if ($postData["edit_30"]) {
            $validation->add(
                $postData["edit_30"],
                new Callback(
                    [
                        'callback' => function () use (&$imgDimensionValidator, &$postData) {

                            // Check if the image is smaller or larger than we need for slider
                            if (!$imgDimensionValidator->validateImageDimensions($postData["edit_30"])) {
								
								$imgdimensionsvalidationmsgnew=$this->translator->_('cms.v3.admin.slidersettings.imgdimensionsvalidationmsgnew' );
								$imgmsg=str_replace("{1}","863",$imgdimensionsvalidationmsgnew);									
								$imgmsg=str_replace("{0}","1800",$imgmsg);		
								
                                return new PresenceOf(
                                    [
                                        'message' =>
                                            $this->translator->_(
                                                'cms.v3.admin.slidersettings.asliderprefixtxt'
                                            ) .
                                            ' '.
                                            2 .
                                            ' '.
                                            $imgmsg
                                    ]
                                );
                            }

                            return true;
                        }
                    ]
                )
            );
        }

        if ($postData["edit_33"]) {
            $validation->add(
                $postData["edit_33"],
                new Callback(
                    [
                        'callback' => function () use (&$imgDimensionValidator, &$postData) {

                            // Check if the image is smaller or larger than we need for slider
                            if (!$imgDimensionValidator->validateImageDimensions($postData["edit_33"])) {
								
								$imgdimensionsvalidationmsgnew=$this->translator->_('cms.v3.admin.slidersettings.imgdimensionsvalidationmsgnew' );
								$imgmsg=str_replace("{1}","863",$imgdimensionsvalidationmsgnew);									
								$imgmsg=str_replace("{0}","1800",$imgmsg);	


                                return new PresenceOf(
                                    [
                                        'message' =>
                                            $this->translator->_(
                                                'cms.v3.admin.slidersettings.asliderprefixtxt'
                                            ) .
                                            ' '.
                                            3 .
                                            ' '.
                                            $imgmsg
                                    ]
                                );
                            }

                            return true;
                        }
                    ]
                )
            );
        }

        if ($postData["edit_36"]) {
            $validation->add(
                $postData["edit_36"],
                new Callback(
                    [
                        'callback' => function () use (&$imgDimensionValidator, &$postData) {

                            // Check if the image is smaller or larger than we need for slider
                            if (!$imgDimensionValidator->validateImageDimensions($postData["edit_36"])) {
								
								$imgdimensionsvalidationmsgnew=$this->translator->_('cms.v3.admin.slidersettings.imgdimensionsvalidationmsgnew' );
								$imgmsg=str_replace("{1}","863",$imgdimensionsvalidationmsgnew);									
								$imgmsg=str_replace("{0}","1800",$imgmsg);	

								
                                return new PresenceOf(
                                    [
                                        'message' =>
                                            $this->translator->_(
                                                'cms.v3.admin.slidersettings.asliderprefixtxt'
                                            ) .
                                            ' '.
                                            4 .
                                            ' '.
                                            $imgmsg
                                    ]
                                );
                            }

                            return true;
                        }
                    ]
                )
            );
        }

        if ($postData["edit_39"]) {
            $validation->add(
                $postData["edit_39"],
                new Callback(
                    [
                        'callback' => function () use (&$imgDimensionValidator, &$postData) {

                            // Check if the image is smaller or larger than we need for slider
                            if (!$imgDimensionValidator->validateImageDimensions($postData["edit_39"])) {
								
								$imgdimensionsvalidationmsgnew=$this->translator->_('cms.v3.admin.slidersettings.imgdimensionsvalidationmsgnew' );
								$imgmsg=str_replace("{1}","863",$imgdimensionsvalidationmsgnew);									
								$imgmsg=str_replace("{0}","1800",$imgmsg);

								
                                return new PresenceOf(
                                    [
                                        'message' =>
                                            $this->translator->_(
                                                'cms.v3.admin.slidersettings.asliderprefixtxt'
                                            ) .
                                            ' '.
                                            5 .
                                            ' '.
                                            $imgmsg
                                    ]
                                );
                            }

                            return true;
                        }
                    ]
                )
            );
        }
    }


    function getImg($url)
    {
        // Mongo ID is the last part of the url path. Use the mysql to get the width and height of the image
        try {
            $urlArray = explode("/", parse_url($url, PHP_URL_PATH));
            $id = end($urlArray);
            $image = CommonLibrary::findFirst("object_id = '" . $id . "'");

            return $image;
        } catch (Exception $exception) {
            $this->logger->warning(
                'No image found in db for the following url: ' . $url
            );
        }

        return null;
    }

}