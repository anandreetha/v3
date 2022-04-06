<?php

namespace Multiple\Core\Widgets\Widget\Country\Page\Findamember;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class FindAMemberListWidget extends BaseWidget
{

    private $widgetContent;

    public function getContent($orgIds, $languages, $pageMode, $regionId, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/findamember/');
        $this->view->widgetContent = $this->retrieveMembers($languages);
        $this->view->pageMode = $pageMode;
        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/memberlist/display") :
            $this->url->get('frontend/memberlist/display');

        if (isset($orgIds) && isset($regionId)) {
            $this->view->isChapterWebsite = true;
            $this->view->orgIds = $orgIds;
            $this->view->regionId = $regionId;
        }

        return $this->view->render('find-a-member-list');
    }

    public function retrieveMembers($languages)
    {
        $cmsv3 = "true";
        $this->view->cmsv3 = $cmsv3;
        $this->view->languages = $languages;
        $this->view->isChapterWebsite = false;

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;
    }

    /**
     * @return mixed
     */
    public function getWidgetContent()
    {
        return $this->widgetContent;
    }

    /**
     * @param mixed $widgetContent
     */
    public function setWidgetContent($widgetContent)
    {
        $this->widgetContent = $widgetContent;
    }
}
