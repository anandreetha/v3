<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 24/10/2017
 * Time: 08:49
 */

namespace Multiple\Core\Widgets\Widget\Chapter\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Core\Models\PageContent;

class ChapterDetailFooterWidget extends BaseWidget
{
    public function getContent($languages, $orgIds, $regionId, $domainName, $renderStaticContent, $websiteId)
    {
		$partial_domain=explode(".",$domainName);
		$this->view->partial_domain=$partial_domain[0];
		
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/chapter/page/');
        $this->view->renderStaticContent = $renderStaticContent;
        $this->view->code = $languages->activeLanguage->localeCode;
        $this->view->chapterId = $orgIds;
        $this->view->regionId = $regionId;
        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/chapterdetailfooter/display") :
            $this->url->get('frontend/chapterdetailfooter/display');
        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        $this->view->memberListNavName = $this->getMemberListNavName($websiteId, $languages->activeLanguage->id);

        // Render a view and return its contents as a string
        return $this->view->render('chapter-detail-footer');
    }

    /**
     * The webmaster can change the slug name (page link) of the memberlist page, hence we can't hardcode this to "memberlist" in display.phtml and instead need to look it up
     * @param $websiteId
     * @param languageId
     * @return Returns the user-controllable slug for member list, or "memberlist" if we can't find it for any reason
     */
    private function getMemberListNavName($websiteId, $languageId) {
        $currentPageContents = PageContent::getPageContentForWebsiteAndLanguage($websiteId, $languageId);

        foreach ($currentPageContents as $currentPageContent) {
            if ($currentPageContent->getPage()->getTemplate() == "find-a-member-list") {
                return $currentPageContent->getNavName();
            }
        }

        return "memberlist";
    }

}
