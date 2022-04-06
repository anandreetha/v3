<?php

namespace Multiple\Backend\Services\Service;

use FilesystemIterator;
use Multiple\Core\Models\CommonLibrary;
use Multiple\Core\Models\Language;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\Website;
use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View;
use Phalcon\Tag;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Multiple\Core\Models\UrlAccents;

class RenderService extends Component
{
    /**
     * The language status (cms_v3.language.status) showing whether a language is 'enabled' or 'disabled',
     * and also the currently active language too
     */
    private const LANGUAGE_STATUS_ENABLED = 1;
    private const LANGUAGE_STATUS_DISABLED = 2;

    /**
     * This is the custom wysiwyg page content widget
     * this is the widget id we need to look for when
     * getting widget page content
     */
    private const CUSTOM_WYSIWYG_WIDGET_ID = 5;

    /**
     * Website country ID
     */
    private const WEBSITE_TYPE_COUNTRY = '1';

    /**
     * Website region ID
     */
    private const WEBSITE_TYPE_REGION = '2';

    /**
     * Website type chapter ID
     */
    private const WEBSITE_TYPE_CHAPTER = '3';

    public function renderPreview($cleanDomain = "", $chapterName = "", $languageCode = "en", $slug = "index", $pageMode)
    {

        $website = null;

        if (!empty($chapterName)) {
            $fullDomain = $cleanDomain . '/' . $chapterName;

            $website = Website::findFirst(
                [
                    'clean_domain = :clean_domain:',
                    'bind' => [
                        'clean_domain' => $fullDomain
                    ],
                ]
            );
            $regionWebsite = Website::findFirst(
                [
                    'clean_domain = :clean_domain:',
                    'bind' => [
                        'clean_domain' => $cleanDomain
                    ],
                ]
            );
            $regionOrgId = $this->getWebsiteOrgIds($regionWebsite->getWebsiteOrg());
            $this->view->setVars([
                "regionId" => $regionOrgId
            ]);

        } else {
            $website = Website::findFirst(
                [
                    'clean_domain = :clean_domain:',
                    'bind' => [
                        'clean_domain' => $cleanDomain
                    ],
                ]
            );
        }

        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        $languageCode = $this->filter->sanitize($languageCode, 'string');
        $language = Language::findFirst(
            [
                'locale_code = :locale_code:',
                'bind' => [
                    'locale_code' => $this->translationUtils->normalizeLocaleCode($languageCode, true)
                ],
            ]
        );

        $websitePages = $website->getPage(['enabled = 1']);

        foreach ($websitePages as $websitePage) {
            $pageContent = $websitePage->getPageContent(
                [
                    'language_id = :language_id: AND nav_name = :nav_name:',
                    'bind' => [
                        'language_id' => $language->id,
                        'nav_name' => $slug
                    ],
                ]
            );

            $matchingPageContent = null;
            if (count($pageContent) > 0) {
                $matchingPageContent = $pageContent->getFirst();
                break;
            }
        }

        if (is_null($matchingPageContent)) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }
        $headerStrings = $this->websiteService->buildHeaderStrings($website);
        $websettings = [
            'websiteName' => $headerStrings->name,
            'websiteTagLine' => $headerStrings->tagline,
            'allSettings' => $website->getWebsiteSettings()
        ];
        if ($chapterName != "") {
            $navLinks = $this->_buildPreviewChapterNavigationLinks($website, $language, $cleanDomain, $chapterName);
        } else {
            $navLinks = $this->_buildPreviewNavigationLinks($website, $language, $cleanDomain);
        }

        $contentSettings = array();
        if ($matchingPageContent->getPageContentSettings() != false) {
            foreach ($matchingPageContent->getPageContentSettings() as $contentSetting) {
                $contentSettings[$contentSetting->Setting->name] = $contentSetting->value;
            }
        }

        // Order by there defined order
        $pageContentWidgets = $matchingPageContent->getWidgets(
            [
                "order" => "widget_order asc"
            ]
        );

        // Form the canonical URL as http:// domainName / localeCode / navSlugName
        // NB: we are assuming the canonical URL is without www. - although this could be down to user control (i.e. by adding "www." to their domain name when creating the website)
        $canonicalUrl = 'http://' . $website->cleanDomain . '/' . $this->translationUtils->normalizeLocaleCode($languageCode, false) . '/' . $slug;
		$canonicalUrl=$this->GenerateCanonical($slug,$canonicalUrl);
		
        // Determine whether we're in a chapter website or not, if we are, then we want to show a link to the chapters region website.
        $parentChapter = null;
        if ($website->getTypeId() === static::WEBSITE_TYPE_CHAPTER) {
            $parentChapter = $this->_buildParentChapterUrl($website, $language);
        }

        // Language and orientation tags for HTML
        $normalisedLocaleToken = str_replace('_', '-', $language->getLocaleCode()); // change from en_US to en-US
        $htmlTags = "dir='{$language->getLanguageOrientation()}' lang='{$normalisedLocaleToken}'";
        $cssFilename = ($language->getLanguageOrientation() == "ltr") ? "styles-ltr.css" : "styles-rtl.css";

        // Set vars and pass back to view
        $this->view->setVars(
            [
                'website' => $website,
                'navLinks' => $navLinks,
                'websettings' => $websettings,
                'pageTitle' => $matchingPageContent->getTitle(),
                'page' => $matchingPageContent->Page,
                'pageTemplate' => $matchingPageContent->Page->template,
                'pageWidgets' => $pageContentWidgets,
                'orgIds' => $this->getWebsiteOrgIds($website->getWebsiteOrg()),
                'domainName' => $website->cleanDomain,
                'languages' => $this->getLanguagesForDropdown($website, $language, false, $matchingPageContent),
                'contentSettings' => $contentSettings,
                'pageMode' => $pageMode,
                'renderStaticContent' => false,
                'canonicalUrl' => $canonicalUrl,
                'htmlTags' => $htmlTags,
                'cssFilename' => $cssFilename,
                'regionWebsite' => $parentChapter
            ]
        );

        //Render the template layout only
        $this->view->setRenderLevel(
            View::LEVEL_LAYOUT
        );

        //Render the v3 template
        $this->view->setTemplateBefore('v3');
    }
	private function GenerateCanonical($slug,$canonicalUrl){
		return $canonicalUrl;
	}
    /**
     * Build a standard class of data that we'll need
     * in the view to keep a link back up to the parent
     * (region) website
     * @param $website \Multiple\Core\Models\Website
     * @param $language \Multiple\Core\Models\Language
     * @param $isPublish
     * @return \stdClass
     */
    private function _buildParentChapterUrl($website, $language, $isPublish = false)
    {
        $parentChapter = new \stdClass();

        $urlParts = explode('/', $website->getCleanDomain());
        $parentChapter->name = $urlParts[0];

        $normalizedLocaleCode = $this->translationUtils->normalizeLocaleCode($language->getLocaleCode(), false);

        if ($isPublish === true) {
            $parentChapter->type = 'publish';
            $parentChapter->website = "http://{$urlParts[0]}/{$normalizedLocaleCode}/index";
        }

        if ($isPublish === false) {
            $parentChapter->type = 'preview';
            // Use the tag helper so that we don't have to manually try and figure out the current base url etc
            $parentChapter->website = $this->tag->linkTo(array("preview/{$urlParts[0]}/{$normalizedLocaleCode}/index", 'Regional Website', 'class' => 'hidden-xs', "target" => "_blank"));
        }

        return $parentChapter;
    }

    /**
     * Build a config file with the available
     * languages for the website given
     * @param $website
     * @throws \Exception
     * @throws \Phalcon\Mvc\Dispatcher\Exception
     */
    public function buildStaticContentConfigFile($website)
    {
        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        $directory = $this->websiteHelper->getWebsiteDirectory($website);

        $websiteLanguages = $website->getWebsiteLanguage();

        // Get the index page url, this can be changed at any time so we need to keep reference to this
        $languageIndexPages = $this->_getIndexPageForWebsite($website);

        if ($websiteLanguages === false) {
            throw new \Exception("Could not find any languages for website {$website->getCleanDomain()}");
        }

        $currentAvailableLanguages = [];
        $counter = 0;

        foreach ($websiteLanguages as $websiteLanguage) {
            $language = $websiteLanguage->getLanguage();
            if ((int)$websiteLanguage->status === 1) {

                if (array_key_exists($websiteLanguage->getLanguageId(), $languageIndexPages)) {
                    if($website->getWebsiteType()->id == 3){
                        // Each index in the array will be turned into a line in a file
                        $currentAvailableLanguages[$counter] = array(
                            'locale' => $this->translationUtils->normalizeLocaleCode($language->getLocaleCode(), false),
                            'index' => $languageIndexPages[$websiteLanguage->getLanguageId()],
                            'type' => $website->getWebsiteType()->id,
                            'domain' => $this->websiteHelper->strip_accents($website->clean_domain));

                    }else {

                        // Each index in the array will be turned into a line in a file
                        $currentAvailableLanguages[$counter] = array(
                            'locale' => $this->translationUtils->normalizeLocaleCode($language->getLocaleCode(), false),
                            'index' => $languageIndexPages[$websiteLanguage->getLanguageId()],
                            'type' => $website->getWebsiteType()->id,
                            'domain' => $website->clean_domain);
                    }


                }
                $counter++;
            }
        }

        // Use real path to figure out what the absolute path is. Add a trailing slash to that then.
        $configFile = realpath('../') . '/' . $directory . '/config.json';

        // If we have an existing config file for this project then nuke it
        if (file_exists($configFile)) {
            unlink($configFile);
        }

        // Create a new ini file, implode each index in the array with an EOL special character to force line breaks
        $newFile = file_put_contents($configFile, json_encode($currentAvailableLanguages), LOCK_EX);

        if ($newFile === false) {
            throw new \Exception("Unable to write file: {$configFile}");
        }
    }

    public function _getIndexPageForWebsite($website)
    {
        $indexPages = [];

        $websiteLanguage = $website->getWebsiteLanguage();

        foreach ($websiteLanguage as $language) {
            // BNISUPP-3317 - the previous code was hammering the DB (including mistakenly loading _all_ PageContent records for a language, i.e. not limited to this website!)
            // However the user can no longer rename the nav name of the homepage from "index", so we will now just hard-code this.
            $indexPages[$language->getLanguageId()] = 'index';
        }

        return $indexPages;
    }

    public function renderStaticContent($website, $parentWebsite = null)
    {
        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        $cleanDomain = $website->clean_domain;
        if (isset($parentWebsite)) {
            $regionOrgId = $this->getWebsiteOrgIds($parentWebsite->getWebsiteOrg());
            $this->view->setVars([
                "regionId" => $regionOrgId
            ]);
        }

        $directory = $this->websiteHelper->getWebsiteDirectory($website);

        $fileContainer = array();

        $enabledLanguages = $website->getWebsiteLanguage(
            [
                'status = :status:',
                'bind' => [
                    'status' => 1
                ],
            ]
        );

        $enabledLanguagesId = array();
        foreach ($enabledLanguages as $enabledLanguage) {
            $enabledLanguagesId[] = $enabledLanguage->language_id;
        }

        foreach ($website->getPage(['enabled = 1']) as $page) {
            $pageContents = $page->getPageContent(
                array(
                    "conditions" => "language_id in ({id:array})",
                    "bind" => array("id" => $enabledLanguagesId)
                )
            );

            foreach ($pageContents as $matchingPageContent) {
                $language = $matchingPageContent->getLanguage();

                // Use realpath to get php to generate an absolute path for ../../../ which would work out as /var/www/html/....
                $localeDir = realpath('../../v3/') . '/' . $directory . '/application/views/' . $this->translationUtils->normalizeLocaleCode($language->locale_code, false);

                if (!file_exists($localeDir)) {
                    mkdir($localeDir, 0775, true);
                }

                $headerStrings = $this->websiteService->buildHeaderStrings($website);
                $websettings = [
                    'websiteName' => $headerStrings->name,
                    'websiteTagLine' => $headerStrings->tagline,
                    'allSettings' => $website->getWebsiteSettings()
                ];
                $navLinks = $this->_buildStaticNavigationLinks($website, $language);

                $contentSettings = array();
                if ($matchingPageContent->getPageContentSettings() != false) {
                    foreach ($matchingPageContent->getPageContentSettings() as $contentSetting) {
                        $contentSettings[$contentSetting->Setting->name] = $contentSetting->value;
                    }
                }

                // Order by there defined order
                $pageContentWidgets = $matchingPageContent->getWidgets(
                    [
                        "order" => "widget_order asc"
                    ]
                );

                // Form the canonical URL as http:// domainName / localeCode / navSlugName
                // NB: we are assuming the canonical URL is without www. - although this could be down to user control (i.e. by adding "www." to their domain name when creating the website)
                $canonicalUrl = 'http://' . $website->cleanDomain . '/' . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . '/' . $matchingPageContent->nav_name;
				$canonicalUrl=$this->GenerateCanonical($matchingPageContent->nav_name,$canonicalUrl);
				
                $parentChapter = null;

                // Determine whether we're in a chapter website or not, if we are, then we want to show a link to the chapters region website.
                if ($website->getTypeId() === static::WEBSITE_TYPE_CHAPTER) {
                    $parentChapter = $this->_buildParentChapterUrl($website, $language, true);
                }

                // Language and orientation tags for HTML
                $normalisedLocaleToken = str_replace('_', '-', $language->getLocaleCode()); // change from en_US to en-US
                $htmlTags = "dir='{$language->getLanguageOrientation()}' lang='{$normalisedLocaleToken}'";
                $cssFilename = ($language->getLanguageOrientation() == "ltr") ? "styles-ltr.css" : "styles-rtl.css";

                // Set vars and pass back to view
                $this->view->setVars(
                    [
                        'website' => $website,
                        'navLinks' => $navLinks,
                        'websettings' => $websettings,
                        'pageTitle' => $matchingPageContent->getTitle(),
                        'page' => $matchingPageContent->Page,
                        'pageTemplate' => $matchingPageContent->Page->template,
                        'pageWidgets' => $pageContentWidgets,
                        'orgIds' => $this->getWebsiteOrgIds($website->getWebsiteOrg()),
                        'domainName' => $website->cleanDomain,
                        'languages' => $this->getLanguagesForDropdown($website, $language, true, $matchingPageContent, true),
                        'contentSettings' => $contentSettings,
                        'pageMode' => "Preview",
                        'renderStaticContent' => true,
                        'canonicalUrl' => $canonicalUrl,
                        'htmlTags' => $htmlTags,
                        'cssFilename' => $cssFilename,
                        'regionWebsite' => $parentChapter
                    ]
                );


                //Render the template layout only
                $this->view->setRenderLevel(
                    View::LEVEL_LAYOUT
                );

                //Render the v3 template
                $this->view->setTemplateBefore('v3');

                $view = clone $this->view;

                $exclusions = array("");

                if (!in_array($matchingPageContent->nav_name, $exclusions)) {
                    $params = array();
                    $params["cleanDomain"] = $cleanDomain;
                    $params["languageCode"] = $language->locale_code;
                    $params["slug"] = $matchingPageContent->nav_name;
                    $params["pageMode"] = "Live_Site";

                    try {
                        $view->start();
                        $view->render("render", "preview", $params); //Pass a controller/action as parameters if required
                        $view->finish();

                        $file = '../' . $directory . '/application/views/' . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $matchingPageContent->nav_name . ".phtml";
                        $label = '/' . $language->locale_code . "/" . $matchingPageContent->nav_name . ".phtml";

                        // Write the contents to the file,
                        // using the FILE_APPEND flag to append the content to the end of the file
                        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
                        if (file_put_contents($file, $view->getContent(), LOCK_EX)) {
                            $fileContainer[$label] = true;

                        } else {
                            $fileContainer[$label] = false;
                        }
                    } catch (\Exception $ex) {

                        $view->finish();
                        // TODO - There seems to be a mismatch between the DB website data and the available pages - introduced a catch all which will allow the site to be published but with that page missing
                        // TODO - This may just be bad data - but need to confirm - if just bad data then this catch all could still be valid.
                    }
                }
            }
        }

        // Render the admin layout
        $this->view->setTemplateBefore('admin');
        return $fileContainer;
    }

    public function renderSitemap($website)
    {
        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        // See if the website has the sitemap setting
        $matchingWebsiteSetting = $website->getWebsiteSettings(
            [
                'settings_id = :settingid:',
                'bind' => [
                    'settingid' => 6
                ],
            ]);

        if (count($matchingWebsiteSetting) > 0) {
            $sitemapSetting = $matchingWebsiteSetting->getFirst();
            $sitemapSettingContent = html_entity_decode($sitemapSetting->value);

            $directory = $this->websiteHelper->getWebsiteDirectory($website);

            // Define the public file path for the published website
            $publicDest = '../' . $directory . '/public/';

            $sitemapPath = $publicDest . "sitemap.xml";

            if (file_put_contents($sitemapPath, $sitemapSettingContent, LOCK_EX)) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    public function renderRobots($website)
    {
        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        // See if the website has the sitemap setting
        $matchingWebsiteSetting = $website->getWebsiteSettings(
            [
                'settings_id = :settingid:',
                'bind' => [
                    'settingid' => 5
                ],
            ]);

        if (count($matchingWebsiteSetting) > 0) {
            $sitemapSetting = $matchingWebsiteSetting->getFirst();
            $sitemapSettingContent = html_entity_decode($sitemapSetting->value);

            $directory = $this->websiteHelper->getWebsiteDirectory($website);

            // Define the public file path for the published website
            $publicDest = '../' . $directory . '/public/';

            $sitemapPath = $publicDest . "robots.txt";

            if (file_put_contents($sitemapPath, $sitemapSettingContent, LOCK_EX)) {
                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    public function renderImageStaticContent($website)
    {
        if ($website == false) {
            throw new \Phalcon\Mvc\Dispatcher\Exception('Not found', \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND);
        }

        // Get the file path for the website
        $directory = $this->websiteHelper->getWebsiteDirectory($website);

        // Define the file path to the image directory for the website
        $imgDest = '../' . $directory . '/public/img/';
        $siteLibPath = '../' . $directory . '/public/img/site/';
        $albumLibPath = '../' . $directory . '/public/img/album/';

        // We need to clear up previous image files
        // Although this may have been picked up by another tasks
        if (file_exists($imgDest)) {
            $this->cleanUpOldFiles($imgDest);
        }

        if (!file_exists($siteLibPath)) {
            mkdir($siteLibPath, 0777, true);
        }


        if (!file_exists($albumLibPath)) {
            mkdir($albumLibPath, 0777, true);
        }

        //Get all associated albums and their items
        $allAssociatedGalleryItems = $this->getAllAssociatedGalleryItems($website->id);

        // Get site library images
        $siteLibraryImages = $website->getCommonLibrary();

        if (is_array($allAssociatedGalleryItems)) {
            foreach ($allAssociatedGalleryItems as $albums) {
                foreach ($albums as $albumItem) {

                    // Produce the static image file
                    $this->produceStaticFile($albumItem['object_id'], $albumLibPath);
                }

            }
        }

        if (count($siteLibraryImages) > 0) {
            foreach ($siteLibraryImages as $siteLibraryImage) {

                // Produce the static image file
                $this->produceStaticFile($siteLibraryImage->object_id, $siteLibPath);

                // Produce the static thumbnail file
                if ($siteLibraryImage->thumbnail_object_id != null) {
                    $this->produceStaticFile($siteLibraryImage->thumbnail_object_id, $siteLibPath);
                }
            }
        }
    }

    private function getAllAssociatedGalleryItems($websiteId)
    {
        $widgetItemsContainer = array();

        $matchingWebsite = Website::findFirst($websiteId);

        if ($matchingWebsite === false) {
            return false;
        }

        $matchingPages = $matchingWebsite->getPage(
            [
                'template = :template:',
                'bind' => [
                    'template' => "gallery"
                ],
            ]
        );

        if ($matchingPages === false) {
            return false;
        }

        $matchingPage = $matchingPages->getFirst();

        $matchingPageContents = $matchingPage->getPageContent();

        if ($matchingPageContents === false) {
            return false;
        }

        foreach ($matchingPageContents as $matchingPageContent) {
            $matchingPageContentRecord = $matchingPageContent;

            $albumWidgets = $matchingPageContentRecord->getWidgets(
                [
                    'widget_id = :widget_id:',
                    'bind' => [
                        'widget_id' => 8
                    ],
                ]
            );

            if (count($albumWidgets) > 0) {
                foreach ($albumWidgets as $albumWidget) {
                    $albumWidgetItems = $albumWidget->getPageContentWidgetItems();

                    if (count($albumWidgetItems) > 0) {
                        $widgetItemsContainer[] = $albumWidgetItems->toArray();
                    }

                }
            }

        }


        return $widgetItemsContainer;


    }

    private function cleanUpOldFiles($filePath)
    {
        $di = new RecursiveDirectoryIterator($filePath, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
    }

    private function produceStaticFile($objectId, $destination)
    {
        try {
            $bucket = $this->mongo->selectGridFSBucket();
            $imageId = new \MongoDB\BSON\ObjectId($objectId);
            $imageBucketFile = $bucket->findOne(array('_id' => $imageId));
            $imageBucketFileExtension = pathinfo($imageBucketFile->filename)['extension'];
            $imageFile = fopen($destination . $objectId . "." . $imageBucketFileExtension, 'wb');
            $bucket->downloadToStream($imageId, $imageFile);
        } catch (\Exception $ex) {

        }

    }


    /**
     * Build the array of html format urls
     * and return an array or urls
     * @param $website
     * @param $language
     * @param $domain
     * @return array
     */
    private function _buildPreviewNavigationLinks($website, $language, $domain)
    {
        $navLinksFull = array();
        $navLinksMobile = array();
        $navLinksSide = array();
        $navLinksFooter = array();

        // Iterate through the websites pages
        foreach ($website->getPage(
            [
                "enabled = 1",
                "order" => "nav_order, last_modified ASC"
            ]
        ) as $websitePage) {

            $matchingPage = $websitePage->getPageContent(
                [
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $language->id
                    ],
                ]
            )->getFirst();

            // Get a single page's overall settings, and then
            $page = $matchingPage->Page;
            $navSetting = $this->settingsFactory->getPageContentSetting("NAVIGATION_LOCATION", $matchingPage->getPageContentSettings(), $matchingPage->id);
            $pageContents = $page->getPageContent([
                'language_id = :language_id:',
                'bind' => [
                    'language_id' => $language->id
                ],
            ]);

            $pageContent = $pageContents->getFirst();

            if ((int)$navSetting->value === 1 || ((int)$navSetting->value === 2)) {
                $navLinksFooter[$pageContent->nav_name] = Tag::linkTo("backend/render/preview/" . $domain
                    . "/" . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title);
            }

            if ($page->template == "home") {
                $home = $this->url->get("backend/render/preview/" . $domain . "/" . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) .
                    "/" . $pageContent->nav_name);
            }

            if ((int)$navSetting->value === 1) {
				$page_nav_name=strtolower($pageContent->nav_name);
				if(($page_nav_name!="index")){
					$navLinksFull[] = Tag::linkTo("backend/render/preview/" . $domain . "/" .
						$this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title);
					$navLinksMobile[] = Tag::linkTo("backend/render/preview/" . $domain . "/" . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) .
						"/" . $pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>');
				}
            } else if ((int)$navSetting->value === 2) {
                $navLinksSide[] = Tag::linkTo("backend/render/preview/" . $domain . "/" . $this->translationUtils->normalizeLocaleCode($language->locale_code, false)
                    . "/" . $pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>');
            }
        }


        $navLinks = array(
            "home" => $home,
            "full" => $navLinksFull,
            "mobile" => $navLinksMobile,
            "side" => $navLinksSide,
            "footer" => $navLinksFooter,
            "language" => $language->locale_code
        );

        return $navLinks;
    }


    private function _buildPreviewChapterNavigationLinks($website, $language, $domain, $chapterName)
    {
        $navLinksFull = array();
        $navLinksMobile = array();
        $navLinksSide = array();
        $navLinksFooter = array();

        $chapterName = $this->websiteHelper->strip_accents($chapterName);

        // Iterate through the websites pages
        foreach ($website->getPage(
            [
                "enabled = 1",
                "order" => "nav_order, last_modified ASC"
            ]
        ) as $websitePage) {

            $matchingPage = $websitePage->getPageContent(
                [
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $language->id
                    ],
                ]
            )->getFirst();

            // Get a single page's overall settings, and then
            $page = $matchingPage->Page;
            $navSetting = $this->settingsFactory->getPageContentSetting("NAVIGATION_LOCATION", $matchingPage->getPageContentSettings(), $matchingPage->id);
            $pageContents = $page->getPageContent([
                'language_id = :language_id:',
                'bind' => [
                    'language_id' => $language->id
                ],
            ]);

            $pageContent = $pageContents->getFirst();

            if ((int)$navSetting->value === 1 || ((int)$navSetting->value === 2)) {
                $navLinksFooter[$pageContent->nav_name] = Tag::linkTo("preview/" . $domain . "/" . $chapterName . "/" .
                    $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title);
            }

            if ($page->template == "home") {
                $home = $this->url->get("preview/" . $domain . "/" . $chapterName . "/" . $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name);
            }

            if ((int)$navSetting->value === 1) {
				$page_nav_name=strtolower($pageContent->nav_name);
				if(($page_nav_name!="index")){
					$navLinksFull[] = Tag::linkTo("preview/" . $domain . "/" . $chapterName . "/" .
						$this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title);

					$navLinksMobile[] = Tag::linkTo("preview/" . $domain . "/" . $chapterName . "/" .
						$this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>');
				}
            } else if ((int)$navSetting->value === 2) {
                $navLinksSide[] = Tag::linkTo("preview/" . $domain . "/" . $chapterName . "/" .
                    $this->translationUtils->normalizeLocaleCode($language->locale_code, false) . "/" . $pageContent->nav_name, $pageContent->title);
            }

        }


        $navLinks = array(
            "home" => $home,
            "full" => $navLinksFull,
            "mobile" => $navLinksMobile,
            "side" => $navLinksSide,
            "footer" => $navLinksFooter,
            "language" => $language->locale_code
        );

        return $navLinks;
    }

    private function _buildStaticNavigationLinks($website, $language)
    {
        $navLinksFull = array();
        $navLinksMobile = array();
        $navLinksSide = array();
        $navLinksFooter = array();

        // Iterate through the websites pages
        foreach ($website->getPage(
            [
                "enabled = 1",
                "order" => "nav_order, last_modified ASC"
            ]
        ) as $websitePage) {

            $matchingPage = $websitePage->getPageContent(
                [
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $language->id
                    ],
                ]
            )->getFirst();

            // TODO: We should really get a 50x page in place for this.
            // We can't have pages failing, Throw an exception and give some relevant information for now.
            if ($matchingPage === false) {
                Throw new \Exception("We cannot find page content for WebsiteID: {$website->getId()} and Page ID: {$websitePage->getId()}");
            }

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
            if ((int)$navSetting->value === 1 || ((int)$navSetting->value === 2)) {
                $navLinksFooter[$pageContent->nav_name] = Tag::linkTo($pageContent->nav_name, $pageContent->title, false);
            }

            // If we don't have a page then lets just continue on the next iteration.
            // TODO: Put some logging at this point to figure out why we don't have a page?2
            if (!$page) {
                continue;
            }

            if ($page->template == "home") {
                $home = $this->url->get($pageContent->nav_name, false);
            }

            if ((int)$navSetting->value === 1) {
				$page_nav_name=strtolower($pageContent->nav_name);
				if(($page_nav_name!="index")){
					$navLinksFull[] = Tag::linkTo($pageContent->nav_name, $pageContent->title, false);
					$navLinksMobile[] = Tag::linkTo($pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>', false);
				}
            } else if ((int)$navSetting->value === 2) {
                $navLinksSide[] = Tag::linkTo($pageContent->nav_name, $pageContent->title . '<span class="ico-048"></span>', false);
            }

        }

        $navLinks = array(
            "home" => $home,
            "full" => $navLinksFull,
            "mobile" => $navLinksMobile,
            "side" => $navLinksSide,
            "footer" => $navLinksFooter,
            "language" => $this->translationUtils->normalizeLocaleCode($language->locale_code, false)
        );

        return $navLinks;
    }


    private function getWebsiteOrgIds($websiteOrgs)
    {
        $weborgs = array();

        foreach ($websiteOrgs as $org) {
            $weborgs[] = $org->orgId;
        }

        return $weborgs;
    }

    /**
     * Builds an object that is used to power the languages drop down feature
     *
     * @param $website
     * @param $activeLanguage
     * @param $onlyShowEnabled
     * @param $pageTemplate
     * @param $isPublish
     * @return \stdClass object with currently active language and also available languages
     */
    private function getLanguagesForDropdown($website, $activeLanguage, $onlyShowEnabled, $pageTemplate = null, $isPublish = false)
    {
        $dropdownLanguages = new \stdClass();
        $urlprefix = substr($this->request->getURI(), 1, strpos($this->request->getURI(), 'preview/') + 7);
        $languagesNav = array();

        foreach ($website->getWebsiteLanguage() as $webLang) {

            $languageNav = new \stdClass();

            if (!$onlyShowEnabled || $webLang->getStatus() == self::LANGUAGE_STATUS_ENABLED) {

                $language = $webLang->getLanguage();
                $normalizedLocaleCode = $this->translationUtils->normalizeLocaleCode($language->getLocaleCode(), false);

                $pageNavSettings = PageContent::getNavNameFromForPageAndLanguage($language->getId(), $pageTemplate->getPageId());
                if ($isPublish === true) {
                    // Do we need to prefix http?
                    $scheme = (strpos($website->getDomain(), 'http') === false) ? 'http://' : '';
                    $languageNav->type = 'published';
					
					$actual_url='';
					$lng_url="{$scheme}{$website->getDomain()}/{$normalizedLocaleCode}/{$pageNavSettings[0]->nav_name}";
					
					$asscents=new UrlAccents();
					if(filter_var($lng_url, FILTER_VALIDATE_URL)):
						$actual_url=$lng_url;
					else:
						$actual_url=$asscents->removeAccentsUrl($lng_url); 
					endif;	
					
					
                    $languageNav->url = $actual_url;
                    $languageNav->descriptionKey = $this->translator->_($language->getDescriptionKey());
                    $languageNav->id = $language->getId();
                    $languageNav->localeCode = $language->getLocaleCode();

                } else {
                    $languageNav->type = 'preview';
					
					$actual_url='';
					$lng_url=$this->config->general->baseUrl . $urlprefix . "{$website->getDomain()}/{$normalizedLocaleCode}/{$pageNavSettings[0]->nav_name}";
					
					$asscents=new UrlAccents();
					if(filter_var($lng_url, FILTER_VALIDATE_URL)):
						$actual_url=$lng_url;
					else:
						$actual_url=$asscents->removeAccentsUrl($lng_url); 
					endif;	
					
                    $languageNav->url = $actual_url;
                    $languageNav->id = $language->getId();
                    $languageNav->localeCode = $language->getLocaleCode();
                    $languageNav->descriptionKey = $this->translator->_($language->getDescriptionKey());
                }

                $languagesNav[] = $languageNav;
            }
        }

        $currentLanguage = new \stdClass();
        $currentLanguage->id = $activeLanguage->getId();
        $currentLanguage->localeCode = $activeLanguage->getLocaleCode();
        $currentLanguage->descriptionKey = $this->translator->_($activeLanguage->getDescriptionKey());
		$currentLanguage->cookieBotCode=$activeLanguage->getCookiebotCode();

        $dropdownLanguages->availableLanguages = $this->translationUtils->sortLanguages($languagesNav, function ($a) {
            return $a->descriptionKey;
        });
        $dropdownLanguages->activeLanguage = $currentLanguage;

        return $dropdownLanguages;
    }
	
    public function convertHexToAscii($string)
    {
        $foundHexArray = [];

        // Get rid of the backslashes and split the string
        $sanitizedString = str_replace('\\', '', $string);
        $parts = str_split($string);
        $counter = 0;
        do {

            // Get the current character we're iterating over
            $currentString = $parts[$counter];

            // If we don't end up on a 'x' then we don't care, do the next iteration of the loop
            if ($currentString !== '\\') {
                $counter++;
                continue;
            }

            // If we've got an x char from above, then we want to get the current counter, and the following two values to get the whole hex string
            // Store the hex string in the array with the value as the ascii value of the hex
            if (!is_null($parts)) {
                $foundHexString = "{$parts[$counter+1]}{$parts[$counter + 2]}{$parts[$counter + 3]}";
                if (!array_key_exists($foundHexString, $foundHexArray)) {
                    $foundHexArray[$foundHexString] = chr(hexdec($foundHexString));
                }

                $counter += 3;
            }
        } while ($counter < count($parts));

        // Replace all the hex we've found with the ascii value
        foreach ($foundHexArray as $hex => $ascii) {
            $sanitizedString = str_replace($hex, $ascii, $sanitizedString);
        }

        return $sanitizedString;
    }

}
