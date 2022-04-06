<?php

namespace Multiple\Backend\Controllers;

use Multiple\Core\Models\Language;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\Website;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag;

class RenderController extends BackendBaseController
{

    /**
     * The language status (cms_v3.language.status) showing whether a language is 'enabled' or 'disabled',
     * and also the currently active language too
     */
    const LANGUAGE_STATUS_ENABLED = 1;
    const LANGUAGE_STATUS_DISABLED = 2;


    /**
     * Builds an object that is used to power the languages drop down feature
     *
     * @param $website
     * @param $activeLanguage
     * @param $onlyShowEnabled
     * @return \stdClass object with currently active language and also available languages
     */
    private function getLanguagesForDropdown($website, $activeLanguage, $onlyShowEnabled)
    {
        $dropdownLanguages = new \stdClass();

        $languagesNav = array();

        foreach ($website->getWebsiteLanguage() as $webLang) {
            if (!$onlyShowEnabled || ($onlyShowEnabled && $webLang->getStatus() == self::LANGUAGE_STATUS_ENABLED)) {
                $language = $webLang->getLanguage();

                $languageNav = new \stdClass();
                $languageNav->localeCode = $language->getLocaleCode();
                $languageNav->descriptionKey = $language->getDescriptionKey();

                $languagesNav[] = $languageNav;
            }
        }

        $currentLanguage = new \stdClass();
        $currentLanguage->localeCode = $activeLanguage->getLocaleCode();
        $currentLanguage->descriptionKey = $activeLanguage->getDescriptionKey();

        $dropdownLanguages->availableLanguages = $languagesNav;
        $dropdownLanguages->activeLanguage = $currentLanguage;

        return $dropdownLanguages;
    }

    public function previewAction($cleanDomain = "", $languageCode = "en", $slug = "index")
    {
        $website = $this->websiteService->getWebsiteFromDomain($cleanDomain);
        if($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)){
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        if($slug=="nopage"){
            $this->response->setStatusCode(410, 'Redirecting to error page');
            $this->view->disable();
        } else {
            $this->renderService->renderPreview($cleanDomain, "", $languageCode, $slug, "Preview");
        }
    }

    public function previewChapterAction($cleanDomain = "",$chapterName = "", $languageCode = "en", $slug = "index")
    {
        $website = $this->websiteService->getWebsiteFromDomain($cleanDomain . "/" . $chapterName);
        if($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)){
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        } else {
            $this->renderService->renderPreview($cleanDomain, $chapterName, $languageCode, $slug, "Preview");
        }
    }

    public function renderImageAction($objectId)
    {

        $this->view->disable();

        $bucket = $this->mongo->selectGridFSBucket();

        ini_set('memory_limit', '100M');
        $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($objectId)));

        if (!is_null($bucketFile)) {
            $objectId = $bucketFile->_id;
            $extension = pathinfo($bucketFile->filename)['extension'];
            header('Content-Type: image/' . $extension);
            header('Pragma: public');
            header('Cache-Control: max-age=31536000, public');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));

            // Open a stream of binary data and fpassthru reads the data until EOF
            echo fpassthru($bucket->openDownloadStream($objectId));
        } else {
            echo "Problem, try again later";
        }

    }


    public function renderDocumentAction($objectId)
    {
        $this->view->disable();

        $bucket = $this->mongo->selectGridFSBucket();

        $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($objectId)));


        if (!is_null($bucketFile)) {
            $objectId = $bucketFile->_id;

            $stream = $bucket->openDownloadStream($objectId);

            if ($stream) {
                header('Content-Type: application/pdf');
                echo stream_get_contents($stream);
            } else {
                echo "Problem, try again later";
            }
        } else {
            echo "Problem, try again later";
        }
        $this->view->setTemplateAfter('ajax-layout');

    }


    // TODO Temp copy and pasted into WebsiteController, WeblanguageController and PageController, move to central place after RBAC work more fleshed out
    private function getWebsiteOrgIds($websiteOrgs)
    {
        $weborgs = array();

        foreach ($websiteOrgs as $org) {
            $weborgs[] = $org->orgId;
        }

        return $weborgs;
    }

    /**
     * Build the array of html format urls
     * and return an array or urls
     * @param $website
     * @param $language
     * @param $domain
     * @return array
     */
    private function _buildNavigationLinks($website, $language, $domain)
    {
        $navLinksFull = array();
        $navLinksMobile = array();
        $navLinksSide = array();

        // Iterate through the websites pages
        foreach ($website->Page as $websitePage) {

            $matchingPage = $websitePage->getPageContent(
                [
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $language->id
                    ],
                ]
            )->getFirst();

            // Get a single page's overall settings, and then
            $page = $matchingPage->getPage(["order" => "nav_order"]);
            $navSetting = $this->settingsFactory->getPageContentSetting("NAVIGATION_LOCATION", $matchingPage->getPageContentSettings(), $matchingPage->id);
            $pageContents = $page->getPageContent([
                'language_id = :language_id:',
                'bind' => [
                    'language_id' => $language->id
                ],
            ]);

            $pageContent = $pageContents->getFirst();

            if ((int)$navSetting->value === 1) {
				$page_nav_name=strtolower($pageContent->nav_name);
				if(($page_nav_name!="index")){
					$navLinksFull[] = Tag::linkTo("backend/render/preview/" . $domain . "/" . $language->locale_code . "/" . $pageContent->nav_name, $pageContent->title);
					$navLinksMobile[] = Tag::linkTo("backend/render/preview/" . $domain . "/" . $language->locale_code . "/" . $pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>');
				}
            } else if ((int)$navSetting->value === 2) {
                $navLinksSide[] = Tag::linkTo("backend/render/preview/" . $domain . "/" . $language->locale_code . "/" . $pageContent->nav_name, $pageContent->title);
            }

        }


        $navLinks = array(
            "full" => $navLinksFull,
            "mobile" => $navLinksMobile,
            "side" => $navLinksSide
        );

        return $navLinks;
    }

}