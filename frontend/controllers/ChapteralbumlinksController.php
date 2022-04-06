<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 03/01/2018
 * Time: 13:05
 */

namespace Multiple\Frontend\Controllers;

use Multiple\Core\Models\Website;

class ChapteralbumlinksController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            $languages = $this->request->getPost("languages");
            $websiteId = json_decode($this->request->getPost("websiteId"));
            $website = Website::findFirst(
                [
                    'id = :id:',
                    'bind' => [
                        'id' => $websiteId
                    ],
                ]
            );

            $websiteChapters = Website::getChapterWebsitesForRegion($website->getId());
            $galleryTemplate = "gallery";

            $visibleWebsiteGallery = [];
            $visibleWebsiteNavName = [];
            $visibleWebsiteLanguage = [];
            $activeLocale = $languages["activeLanguage"]["localeCode"];

            foreach ($websiteChapters as $chapter) {
                if ($chapter->getLastPublished() === null) {
                    continue;
                }
                $galleryPage = $chapter->getPage(
                    [
                        'template = :template:',
                        'bind' => [
                            'template' => $galleryTemplate
                        ],
                    ]
                )->getFirst();

                if ($galleryPage != false) {
                    $pageContent = $galleryPage->pageContent;
                    $pageContent = $pageContent[0];
                    $setting = $pageContent->getPageContentSettings(
                        [
                            'setting_id = :setting_id: AND value != :value:',
                            'bind' => [
                                'setting_id' => "10",
                                'value' => "0"
                            ],
                        ]
                    )->getFirst();

                    if ($setting != false) {
                        array_push($visibleWebsiteGallery, $chapter);
                        array_push($visibleWebsiteNavName, $pageContent->nav_name);

                        // Check if current language is present in chapter
                        $isMatchingLocale = false;
                        foreach ($chapter->getWebsiteLanguage() as $language) {
                            if ($language->getLanguage()->locale_code == $activeLocale) {
                                $isMatchingLocale = true;
                                break;
                            }
                        }

                        // If they have a match then prefer that one. If it is not there, take the default (first in db)
                        if ($isMatchingLocale) {
                            array_push($visibleWebsiteLanguage, $activeLocale);
                        } else {
                            array_push(
                                $visibleWebsiteLanguage,
                                $chapter->getWebsiteLanguage()[0]->getLanguage()->locale_code
                            );
                        }
                    }
                }
            }
        }

        $this->view->visibleWebsiteGallery = $visibleWebsiteGallery;
        $this->view->visibleWebsiteNavName = $visibleWebsiteNavName;
        $this->view->visibleWebsiteLanguage = $visibleWebsiteLanguage;
        $this->view->renderStaticContent = json_decode($this->request->getPost("renderStaticContent"));

        $this->view->pick("chapteralbumlinks/display");
    }
}
