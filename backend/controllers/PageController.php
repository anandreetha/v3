<?php

namespace Multiple\Backend\Controllers;

use Multiple\Backend\Validators\EditPageValidator;
use Multiple\Core\Models\Language;
use Multiple\Core\Models\Page;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentWidgets;
use Multiple\Core\Models\PageContentWidgetSettings;
use Multiple\Core\Models\Website;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;


class PageController extends BackendBaseController
{


    const GALLERY_TEMPLATE_NAME = "gallery";

    public function addAction($websiteId)
    {
//        $languageId = $this->filter->sanitize($languageId, 'int');
        $websiteId = $this->filter->sanitize($websiteId, 'int');

        $this->pageService->createPage($websiteId);

        $this->view->website = Website::findFirstById($websiteId);
        // TODO: Check if this is still used.
//        $this->view->languages = Language::find($languageId);
        $this->view->setTemplateAfter('ajax-layout');
    }

    public function editAction($pageContentId, $pageContentWidgetId)
    {
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );

        // TODO: Eventually remove $pageContentId
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');
        $pageContentWidgetId = $this->filter->sanitize($pageContentWidgetId, 'int');

        $pageContent = PageContent::findFirstById($pageContentId);
        $pageContentWidget = PageContentWidgets::findFirstById($pageContentWidgetId);

        if($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)){
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        // We only expect to have one row for a specific widget setting
        $databaseContent = $pageContentWidget->getPageContentWidgetSettings();

        // Get the existing content from ckedit, if we don't have content inside then get the data from the DB
        $ckEditorContent = $this->purifier->purify($this->request->getPost("editPageEditor"));

        if ((bool) $ckEditorContent === false) {
            $ckEditorContent = count($databaseContent) > 0 ? $databaseContent[0]->value : "";
        }

        $result = null;
        if ($this->request->isPost()) {

            if (count($pageContentWidget->getPageContentWidgetSettings()) > 0) {
                foreach ($pageContentWidget->getPageContentWidgetSettings() as $pageContentWidgetRow) {
                    if ($pageContentWidgetRow->getPageContentWidgetId() === $pageContentWidgetId) {
                        $pageContentWidgetRow->setPageContentWidgetId($pageContentWidgetId);
                        $pageContentWidgetRow->setSettingId(54); // 54 is ID column in cms_v3.settings table
                        $pageContentWidgetRow->setValue($ckEditorContent);
                        $result = $pageContentWidgetRow->save();
                    }
                }
            } else {
                $pageContentWidgetSettings = new PageContentWidgetSettings();
                $pageContentWidgetSettings->setPageContentWidgetId($pageContentWidgetId);
                $pageContentWidgetSettings->setSettingId(54); // 54 is ID column in cms_v3.settings table
                $pageContentWidgetSettings->setValue($ckEditorContent);
                $result = $pageContentWidgetSettings->save();
            }

            if ($result === false) {
                $this->flashSession->error('Unable to save content');
            } else {
                $this->flashSession->success('Saved Content');
            }
        }


        $this->view->pageContent = $ckEditorContent;
        $this->view->websiteId = $pageContent->getPage()->website_id;
        $this->view->setTemplateAfter('ajax-layout');
    }


    public function editAjaxHandlerAction($pageContentId, $pageContentWidgetId){
        $this->view->disable();

        if($this->request->isPost() && $this->request->isAjax() ) {

            // TODO: Eventually remove $pageContentId
            $pageContentWidgetId = $this->filter->sanitize($pageContentWidgetId, 'int');

            $pageContent = PageContent::findFirstById($pageContentId);
            $pageContentWidget = PageContentWidgets::findFirstById($pageContentWidgetId);

            // Checking both $pageContent and $pageContentWidget before the above TODO is actioned - this should be modified appropriately
            if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
                $this->failAndRedirect('backend/error/permissionDenied');
                return;
            }

            if ($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)) {
                $this->failAndRedirect('backend/error/permissionDenied');
                return;
            }

            // We only expect to have one row for a specific widget setting
            $databaseContent = $pageContentWidget->getPageContentWidgetSettings();

            // Get the existing content from ckedit, if we don't have content inside then get the data from the DB
            $ckEditorContent = $this->purifier->purify($this->request->getPost("editPageEditor"));

            $errors = array();

            $result = null;

            if (count($pageContentWidget->getPageContentWidgetSettings()) > 0) {
                foreach ($pageContentWidget->getPageContentWidgetSettings() as $pageContentWidgetRow) {
                    if ($pageContentWidgetRow->getPageContentWidgetId() === $pageContentWidgetId) {
                        $pageContentWidgetRow->setPageContentWidgetId($pageContentWidgetId);
                        $pageContentWidgetRow->setSettingId(54); // 54 is ID column in cms_v3.settings table
                        $pageContentWidgetRow->setValue($ckEditorContent);

                        // Update page modified timestamp
                        $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                        $result = $pageContent->save();

                        // only save if timestamp successful (we should look at adding a transaction/rollback mechanism for this endpoint)
                        if ($result === true) {
                            $result = $pageContentWidgetRow->save();
                        }
                    }
                }
            } else {
                $pageContentWidgetSettings = new PageContentWidgetSettings();
                $pageContentWidgetSettings->setPageContentWidgetId($pageContentWidgetId);
                $pageContentWidgetSettings->setSettingId(54); // 54 is ID column in cms_v3.settings table
                $pageContentWidgetSettings->setValue($ckEditorContent);

                // Update page modified timestamp
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $result = $pageContent->save();

                // only save if timestamp successful (we should look at adding a transaction/rollback mechanism for this endpoint)
                if ($result === true) {
                    $result = $pageContentWidgetSettings->save();
                }
            }

            if ($result === false) {
                $data['success'] = false;
            } else {
                $data['success'] = true;
            }

            $data['errors'] = $errors;

            echo json_encode($data);
        }
    }

    public function editPageAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');
        $pageContent = PageContent::findFirstById($pageContentId);

        if ($pageContent == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        $website = $pageContent->Page->Website;

        if(!$this->hasUserGotPermissionToAccessWebsite($website)){
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $headerStrings = $this->websiteService->buildHeaderStrings($website);
        $pageTemplate = $pageContent->page->template;

        $contentSettings = array();

        foreach ($pageContent->getPageContentSettings() as $contentSetting) {
            $contentSettings[$contentSetting->Setting->name] = $contentSetting->value;
        }

        $backUrl = 'backend/website/view/' . $pageContent->getPage()->getWebsite()->id;
        $this->view->redirectToUrl = $this->tag->linkTo(array($backUrl, '<i class="fa fa-arrow-left fa-lg pull-right"></i>', "data-tooltip"=>"true" ,"title"=>$this->translator->_('cms.v3.admin.pages.backpage'), "class"=>"newPageModal btn btn-default"));

        $pageLevel = $this->pageService->calculatePageLevel($pageContent->getPage()->getId());


        // The page order can only be changed if this is a top level page that's not the homepage
        $this->view->isPageOrderEditable = ((($pageContent->getPage()->template !== "home")&&($pageContent->getPage()->template !== "find-a-chapter")) && $pageLevel->level === "top");
		if($pageContent->getPage()->template=="cookiebot-declaration"):
			$this->view->isPageOrderEditable ="";
		endif;
		
		$allow_access=$this->authenticationService->getCountryAuthCheck($pageContent->getPage()->getWebsite()->id);
		$this->view->allow_access = $allow_access;
		
        if ($this->request->isPost()) {

            $validation = new EditPageValidator();

            $validation->setFilters("inputPageTitle","trim");
            $validation->setFilters("inputPageLink","trim");
            $validation->setFilters("inputMetaTitle","trim");
            $validation->setFilters("inputMetaKeywords","trim");
            $validation->setFilters("inputMetaDescription","trim");
            $validation->setFilters("inputMetaAuthor","trim");
            $validation->setFilters("inputMetaRobots","trim");

            if($pageContent->getPage()->template !== "home" ){
                $validation->add(
                    'inputPageOrder',
                    new Callback(
                        [
                            'message' =>  $this->translator->_('cms.v3.admin.editpage.pageordernumberonlyvalidationmsg'),
                            "callback" => function($data) {
                                if ($data['inputPageOrder'] < 1 ) {
                                    return false;
                                }

                                return true;
                            }
                        ]
                    )
                );
            }

            $messages = $validation->validate($this->request->getPost());

            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->flashSession->error($message);
                }
            } else {

                $pageTitle = $this->request->getPost("inputPageTitle", "string");
                $pageLink = $this->request->getPost("inputPageLink", "string");
                $pageOrder = $this->request->getPost("inputPageOrder");

                $dateTimeNow = new \DateTime();
                $lastModified = $dateTimeNow->format('Y-m-d H:i:s');

                $pageContent->title = $pageTitle;
                $pageContent->nav_name = $pageLink;
                $pageContent->page->nav_order = $pageOrder;
                $pageContent->page->lastModified = $lastModified;
                $pageContent->page->website->lastModified = $lastModified;

                if ($pageContent->save() === false) {
                    $this->flashSession->error($this->translator->_('cms.v3.admin.editpage.saveerrormsg'));

                    foreach ($pageContent->getMessages() as $message) {
                        $this->flashSession->error($message);
                    }

                } else {
                    $this->flashSession->success($this->translator->_('cms.v3.admin.editpage.savesuccessmsg'));
                }

                //Sanitize
                $pageMetaTitle = $this->filter->sanitize($this->request->getPost("inputMetaTitle", "string"), "trim");
                $pageMetaKeywords = $this->filter->sanitize($this->request->getPost("inputMetaKeywords", "string"), "trim");
                $pageMetaDescription = $this->filter->sanitize($this->request->getPost("inputMetaDescription", "string"), "trim");
                $pageMetaAuthor = $this->filter->sanitize($this->request->getPost("inputMetaAuthor", "string"), "trim");
                $pageMetaRobots = $this->filter->sanitize($this->request->getPost("inputMetaRobots", "string"), "trim");

                // Map to array
                $metaTagValues = array(
                    "pageMetaTitle" => $pageMetaTitle,
                    "pageMetaKeywords" => $pageMetaKeywords,
                    "pageMetaDescription" => $pageMetaDescription,
                    "pageMetaAuthor" => $pageMetaAuthor,
                    "pageMetaRobots" => $pageMetaRobots,
                );

                $this->saveMetaTags($pageContent, $metaTagValues);

                return $this->response->redirect('backend/page/editPage/' . $pageContentId);

            }
        }

        // Get all associated widgets sorted by order
        $widgets = $pageContent->getWidgets(
            [
                "order" => "widget_order ASC"
            ]
        );

        $albumTitleArray = [];
        if($pageTemplate == self::GALLERY_TEMPLATE_NAME){
            forEach ($widgets as $widget){
                if( $widget->widget->class_name == "AlbumWidget"){
                    $albumTitle = "";
                    $pageContentWidget = PageContentWidgets::findFirstById($widget->id);
                    if(count($pageContentWidget->getPageContentWidgetSettings())){
                        $albumPageContentWidgetSetting = $pageContentWidget->getPageContentWidgetSettings();
                        $albumTitle = $albumPageContentWidgetSetting[0]->value;
                    }

                    if(isset($albumTitle)){
                        $albumTitleArray[$pageContentWidget->id] = $albumTitle;
                    }

                }
            }
        }


        // If we are rendering the home page we need access to it's slider widget so store as a separate variable for quick access
        if ($pageTemplate == "home") {
            $this->view->sliderWidget = $pageContent->getWidgets([
                'widget_id = :widgetid:',
                'bind' => [
                    'widgetid' => 7
                ],
            ]);
        }

        // Set the relevant CSS file based on the currently selected language - aka not based on the logged in user's locale, since this CSS and action is for the 'inline edit' (website preview) functionality
        if ($pageContent->getLanguage()->getLanguageOrientation() == "rtl") {
            $this->assets->addCss($this->config->general->cdn . '/new_template/assets/styles/css/styles-rtl.css');
        } else {
            $this->assets->addCss($this->config->general->cdn . '/new_template/assets/styles/css/styles-ltr.css');
        }
		
        // Set vars and pass back to all views
        $this->view->setVars(
            [
                'headerStrings' => $headerStrings,
                'contentSettings' => $contentSettings,
                 // Create a heading such as 'Edit page for Spanish'
                'contentTitle' => $this->translator->_("cms.v3.admin.editpage.heading") . ' ' . $this->translator->_($pageContent->getLanguage()->getDescriptionKey()),
                'contentSubTitle' => $website->clean_domain,
                'pageContent' => $pageContent,
                'pageTemplate' => $pageTemplate,
                'allowedWidgets' => $pageContent->getAllowedWidgets(),
                'widgets' => $widgets,
                'enableRss' => $pageTemplate == "home", // We only want to be able to edit the rss feed on the home page
                'enableNewsletter' => $pageTemplate == "home", // We only want to be able to edit the newsletter on the home page
                'enableEditFooter' => $pageTemplate == "home", // We only want to be able to edit the footer widgets on the home page
                'albumTitleArray' => $albumTitleArray,
                'languages' => $this->getLanguagesForDropdown($website, $pageContent->getLanguage()),
                'pageOrientation' => $pageContent->getLanguage()->getLanguageOrientation(),
                'website' => $website,
                'websiteType' => $website->getTypeId(),
            ]
        );

    }

    /**
     * Builds an object that is used to power the languages drop down feature. This is a cut down version of the 'real' one in RenderService.
     * This does naturally mean there's some code duplication, but the usages in the frontend is different to in the 'inline edit' feature,
     * and we won't need this in any other class, so I'll leave this as (partial) duplication for now.
     *
     * @param $website
     * @param $activeLanguage
     * @return \stdClass object with currently active language and also available languages
     */
    private function getLanguagesForDropdown($website, $activeLanguage)
    {
        $languagesNav = array();

        foreach ($website->getWebsiteLanguage() as $webLang) {
            $languagesNav[] = $this->buildLanguageObject($webLang->getLanguage());
        }

        $dropdownLanguages = new \stdClass();
        $dropdownLanguages->activeLanguage = $this->buildLanguageObject($activeLanguage);
        $dropdownLanguages->availableLanguages = $this->translationUtils->sortLanguages($languagesNav, function ($a){return $a->descriptionKey;});
        return $dropdownLanguages;
    }

    private function buildLanguageObject($language) {
        $currentLanguage = new \stdClass();
        $currentLanguage->localeCode = $language->getLocaleCode();
        $currentLanguage->descriptionKey = $this->translator->_($language->getDescriptionKey());
        return $currentLanguage;
    }

    private function saveMetaTags($pageContent, $metaTagValues)
    {
        $currentSettings = $pageContent->getPageContentSettings();

        $metaTitlePageContentSetting = $this->settingsFactory->getPageContentSetting("META_TITLE", $currentSettings, $pageContent->id);
        $metaTitlePageContentSetting->setValue($metaTagValues['pageMetaTitle']);
        $metaTitlePageContentSetting->save();

        $metaKeywordPageContentSetting = $this->settingsFactory->getPageContentSetting("META_KEYWORDS", $currentSettings, $pageContent->id);
        $metaKeywordPageContentSetting->setValue($metaTagValues['pageMetaKeywords']);
        $metaKeywordPageContentSetting->save();


        $metaDescriptionPageContentSetting = $this->settingsFactory->getPageContentSetting("META_DESCRIPTION", $currentSettings, $pageContent->id);
        $metaDescriptionPageContentSetting->setValue($metaTagValues['pageMetaDescription']);
        $metaDescriptionPageContentSetting->save();

        $metaRobotsPageContentSetting = $this->settingsFactory->getPageContentSetting("META_ROBOTS", $currentSettings, $pageContent->id);
        $metaRobotsPageContentSetting->setValue($metaTagValues['pageMetaRobots']);
        $metaRobotsPageContentSetting->save();

        $metaAuthorPageContentSetting = $this->settingsFactory->getPageContentSetting("META_AUTHOR", $currentSettings, $pageContent->id);
        $metaAuthorPageContentSetting->setValue($metaTagValues['pageMetaAuthor']);
        $metaAuthorPageContentSetting->save();


    }


}