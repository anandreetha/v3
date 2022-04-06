<?php

namespace Multiple\Core\Widgets\Widget\Country\Page\Findamember;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Phalcon\Mvc\View\Simple as SimpleView;

class MemberDetailsWidget extends BaseWidget
{
    public function getContent($orgIds, $languages, $pageMode, $domainName, $website, $renderStaticContent)
    {

        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->websiteTypeId = $website->typeId;
        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/memberdetail/display") :
            $this->url->get('frontend/memberdetail/display');
        $this->view->languages = $languages;
        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        $this->pageMode =  $pageMode;
        $this->view->pageMode = $pageMode;

        return $this->view->render('findamember/find-a-member-detail');
    }
}

