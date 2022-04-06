<?php

namespace Multiple\Backend\Services\Service;

use Multiple\Backend\Validators\AddPageValidator;
use Multiple\Core\Models\Page;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentAllowedWidgets;
use Multiple\Core\Models\PageContentSettings;
use Multiple\Core\Models\PageHierarchy;
use Multiple\Core\Models\PageSettings;
use Multiple\Core\Models\Website;
use Phalcon\Mvc\User\Component;

class PageService extends Component
{
    /**
     * Calculates the page level for a particular cms_v3.page.id, i.e. whether it's a top level page (like "Why BNI?")
     * or a child page (like "Member List", which is a child of "Find a Member").
     *
     * @param $pageId - cms_v3.page.id (NB: not cms_v3.page_content.id)
     * @return \stdClass object with "level" and "parentPageId" values
     */
    public function calculatePageLevel($pageId)
    {
        $pageLevel = new \stdClass();
        $pageLevel->level = "top";

        // Also get the page hierarchy for this page to determine if it's a first or second level page
        $childPageHierarchy = PageHierarchy::findFirst([
            'child_page_id = :childPageId:',
            'bind' => [
                'childPageId' => $pageId
            ],
        ]);

        if ($childPageHierarchy) {
            // Check if the parent of this has a match elsewhere as a child. If so, this is a third level page. Else it's a second level page.
            // We only ever go to 2 levels deep, so we shouldn't need anything more complicated than this approach IMO.
            $grandchildPageHierarchy = PageHierarchy::findFirst([
                'child_page_id = :childPageId:',
                'bind' => [
                    'childPageId' => $childPageHierarchy->getParentPageId()
                ],
            ]);

            /**
             * If we're a grandchild page, we add 2 onto the parent page order. Else we add 1 onto the parent page order. This (with the '5' gap) means
             * that we should always have consistent ordering on the page.
             */
            if ($grandchildPageHierarchy) {
                $pageLevel->level = "grandchild";
                $pageLevel->parentPageId = $grandchildPageHierarchy->getParentPageId();
            }
            else {
                $pageLevel->level = "child";
                $pageLevel->parentPageId = $childPageHierarchy->getParentPageId();
            }
        }

        return $pageLevel;
    }

    /**
     *
     * Create a new page for a website ID
     * Create a new page associated with all
     * languages for a website
     * @param $websiteId
     */
    public function createPage($websiteId){

        try{

            if ($this->request->isPost()) {

                $validation = new AddPageValidator();
                $validation->setFilters("pageTitle","trim");
                $validation->setFilters("navName","trim");
                $messages = $validation->validate($this->request->getPost());
                $validateCustomPageUniqueness = $this->validateCustomPageUniqueness($websiteId, $this->request->getPost("navName"));
                if(count($messages) || $validateCustomPageUniqueness == true){
                    foreach ($messages as $message){
                        $this->flash->error($message);
                    }
                } else {

                    $navName =$this->request->getPost("navName","string");
                    $navName = $this->filter->sanitize($navName,"string");
                    $pageTitle = $this->request->getPost("pageTitle","string");
                    $pageTitle = $this->filter->sanitize($pageTitle,"string");
                    $pageOrder = $this->request->getPost("pageOrder","int");
                    $pageOrder = $this->filter->sanitize($pageOrder, 'int');
                    $page = null;
                    $processIndicator = false;

                    // Create the initial website record first
                    $page = $this->createNewPageRecord($websiteId,$navName,$pageOrder);
                    $processIndicator = WebsiteService::operationSuccessIndicator($page);

                    // Get all languages for a website, we'll want to iterate through each language and create a new page
                    $websiteModel = Website::findFirstById($websiteId);

                    // Iterate through each language we've found and create a new page record
                    foreach($websiteModel->WebsiteLanguage as $websiteLanguage) {
                        $pageContent = $this->createPageContentRecord($page, $pageTitle, $websiteLanguage->getLanguageId(), $navName);
                        $processIndicator = WebsiteService::operationSuccessIndicator($pageContent);

                        $pageContentAllowedWidget = $this->createPageContentAllowedWidget($pageContent);
                        $processIndicator = WebsiteService::operationSuccessIndicator($pageContentAllowedWidget);

                        $pageContentSettings = $this->createPageContentSettings($pageContent);
                        $processIndicator = WebsiteService::operationSuccessIndicator($pageContentSettings);

                        $copiedSettings = $this->copyHomepageSettings($websiteModel,$pageContent,$websiteLanguage->getLanguageId());
                        $processIndicator = WebsiteService::operationSuccessIndicator($copiedSettings);

                    }

                    if ($processIndicator) {
                        $this->flash->success($this->translator->_('cms.v3.admin.pages.pagecreated'));
                    } else {
                        throw new Exception("Unknown failure whilst trying to create a new Page");
                    }

                }
            }

        } catch (Exception $ex) {

            $this->flash->error('Page could not be created at this time.Please try again later');

            if ($page != false) {
                // Roll back
                $page->delete();
            }
        }
    }


    /**
     * Private method to persist a Page model.
     * @param $websiteId
     * @param $navName
     * @param $pageOrder
     * @return Page
     */
    private function createNewPageRecord($websiteId,$navName,$pageOrder)
    {

        if($this->request->isPost()){

            $newPage = new Page();
            $newPage->website_id = $websiteId;
            $newPage->nav_order = $navName;
            $dateTimeNow = new \DateTime();
            $newPage->setNavOrder($pageOrder);
            $newPage->lastModified = $dateTimeNow->format('Y-m-d H:i:s');
            $newPage->setTemplate("custom-page");
            if ($newPage->save() === false) {

                $errorMessage = "";

                foreach ($newPage->getMessages() as $message) {
                    $errorMessage .= $message . "\n";
                }

                throw new Exception("Page record could not be created: " . $errorMessage);

            } else {
                return $newPage;
            }
        }

        return false;
    }

    public function updatePageLastUpdateTime($pageContentWidgetId, $widgetId)
    {
        $pages = Page::GetPageFromPageContentId($pageContentWidgetId, $widgetId);

        $dateTimeNow = new \DateTime();
        foreach ($pages as $page) {
            $page->setLastModified($dateTimeNow->format('Y-m-d H:i:s'));
            $page->save();
        }
    }

    /**
     *
     * Private method to create a Page content model.
     * @param $newPage
     * @param $pageTitle
     * @param $languageId
     * @param $navName
     * @return bool
     */
    private function createPageContentRecord($newPage,$pageTitle,$languageId,$navName){
        if($this->request->isPost() && $newPage !=false) {

            $newPageContent = new PageContent();

            //This needs to change
            $newPageContent->language_id = $languageId;

            $newPageContent->title = $pageTitle;
            $newPageContent->nav_name = $navName;
            $newPageContent->draft_content = "";
            $newPageContent->language_id=$languageId;
            $newPageContent->page_id = $newPage->getId();

            if($newPageContent->save() === false) {
                $errorMessage = "";
                foreach ($newPageContent->getMessages() as $message) {
                    $errorMessage .= $message . "\n";
                }
                throw new Exception("Page content record could not be created: " . $errorMessage);
            } else {
                return $newPageContent;
            }

        }

        return false;
    }

    /**
     * Private method to save a page conteant allowed widget models.
     * @param $pageContent
     * @return bool|mixed
     */
    private function createPageContentAllowedWidget($pageContent){
        if($this->request->isPost() && $pageContent !=false) {

            $testimonialWidget = new PageContentAllowedWidgets();

            $testimonialWidget->setPageContentId($pageContent->id);
            $testimonialWidget->setWidgetId(6);
            $testimonialWidget->setNoAllowedInstances(1);

            $wysiwigWindget = new PageContentAllowedWidgets();
            $wysiwigWindget->setPageContentId($pageContent->id);
            $wysiwigWindget->setWidgetId(5);
            $wysiwigWindget->setNoAllowedInstances(1);


            $widgetArray = array();
            array_push($widgetArray,$testimonialWidget);
            array_push($widgetArray,$wysiwigWindget);

            foreach ($widgetArray as $widget){

                if ($widget->save() === false) {
                    $errorMessage = "";
                    foreach ($widget->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Page content allow widget Record could not be created: " . $errorMessage);
                }
            }
            return true;
        }
        return false;
    }


    /**
     * private method to save a page content setting entity.
     * @param $pageContent
     * @return bool|PageSettings
     */
    private function createPageContentSettings($pageContent){

        if($this->request->isPost() && $pageContent != false) {
            $bniString = "BNI";
            $largestReferralOrganizationString = ":Largest Referral Organization";
            $indexFollowString = "INDEX,FOLLOW";

            $displayInNavigation = new PageContentSettings();
            $displayInNavigation->setPageContentId($pageContent->id);
            $displayInNavigation->setSettingId(10);
            $displayInNavigation->setValue("0");

            $leadHeading = new PageContentSettings();
            $leadHeading->setPageContentId($pageContent->id);
            $leadHeading->setSettingId(43);
            $leadHeading->setValue(html_entity_decode($pageContent->getTitle(), ENT_QUOTES | ENT_XML1, 'UTF-8'));

            $metaTitle = new PageContentSettings();
            $metaTitle->setPageContentId($pageContent->id);
            $metaTitle->setSettingId(12);
            $metaTitle->setValue(html_entity_decode($pageContent->getTitle(), ENT_QUOTES | ENT_XML1, 'UTF-8'));

            $metaKeyword = new PageContentSettings();
            $metaKeyword->setPageContentId($pageContent->id);
            $metaKeyword->setSettingId(13);
            $metaKeyword->setValue(html_entity_decode($pageContent->getTitle() . "," . $bniString, ENT_QUOTES | ENT_XML1, 'UTF-8'));


            $metaDescription = new PageContentSettings();
            $metaDescription->setPageContentId($pageContent->id);
            $metaDescription->setSettingId(14);
            $metaDescription->setValue(html_entity_decode($bniString . $largestReferralOrganizationString, ENT_QUOTES | ENT_XML1, 'UTF-8'));

            $metaRobots = new PageContentSettings();
            $metaRobots->setPageContentId($pageContent->id);
            $metaRobots->setSettingId(15);
            $metaRobots->setValue(html_entity_decode($indexFollowString, ENT_QUOTES | ENT_XML1, 'UTF-8'));

            $metaAuthor = new PageContentSettings();
            $metaAuthor->setPageContentId($pageContent->id);
            $metaAuthor->setSettingId(16);
            $metaAuthor->setValue(html_entity_decode($bniString, ENT_QUOTES | ENT_XML1, 'UTF-8'));


            $settingArray = array();
            array_push($settingArray,$displayInNavigation);
            array_push($settingArray,$leadHeading);
            array_push($settingArray, $metaTitle);
            array_push($settingArray, $metaKeyword);
            array_push($settingArray, $metaDescription);
            array_push($settingArray, $metaRobots);
            array_push($settingArray, $metaAuthor);

            foreach ($settingArray as $setting){

                if ($setting->save() === false) {
                    $errorMessage = "";
                    foreach ($setting->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Page content setting record could not be created: " . $errorMessage);
                }
            }

            return true;
        }
        return  false;
    }


    private function copyHomepageSettings($websiteModel,$pageContent,$languageId){
        if($this->request->isPost() && $pageContent != false) {
            $settingArray = array();
            $excludedSettings = array(10, 12, 13, 14, 15, 16, 43);

            $homePages = $websiteModel->getPage(
                [
                    'template = :template:',
                    'bind' => [
                        'template' => 'home'
                    ],
                ]
            );

            $matchingHomePage = $homePages->getFirst();
            $matchingHomePageContent = $matchingHomePage->getPageContent(
                [
                    'language_id = :language_id:',
                    'bind' => [
                        'language_id' => $languageId
                    ],
                ]
            )->getFirst();

            $matchingHomePageContentSettings = $matchingHomePageContent->getPageContentSettings();
            foreach ($matchingHomePageContentSettings as $matchingHomePageContentSetting) {
                if (!in_array($matchingHomePageContentSetting->setting_id, $excludedSettings)) {
                    $newPageContentSetting = new PageContentSettings();
                    $newPageContentSetting->setPageContentId($pageContent->id);
                    $newPageContentSetting->setSettingId($matchingHomePageContentSetting->setting_id);
                    $newPageContentSetting->setValue($matchingHomePageContentSetting->value);

                    array_push($settingArray, $newPageContentSetting);
                }
            }

            foreach ($settingArray as $setting) {
                if ($setting->save() === false) {
                    $errorMessage = "";
                    foreach ($setting->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Custom Page content setting record could not be copied: " . $errorMessage);
                }
            }
            return true;
        }
        return false;

    }

    /**
     * Private method to loop through every page content of every page of the current website and check
     * if the nave name already exists.
     * @param $websiteId
     * @param $navName
     * @return bool
     */
    private function validateCustomPageUniqueness($websiteId,$navName){
        $website = Website::findFirstById($websiteId);
        $pages = $website->page;
        foreach ($pages as $page){
            $pageContent = $page->pageContent;
            foreach ($pageContent as $content){
                if($content->nav_name == $navName){
                    $message =  $this->translator->_('cms.v3.admin.custompage.pagenavtitleuniquevalidationmsg');
                    $this->flash->error($message);
                    return true;
                }
            }
        }
        return false;
    }

}