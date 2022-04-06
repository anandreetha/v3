<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 24/10/2017
 * Time: 08:49
 */

namespace Multiple\Core\Widgets\Widget\Region\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class ChapterFormingWidget extends BaseWidget
{
    public function getContent($languages, $orgIds, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/region/page/');


        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/chapterforming/display") :
            $this->url->get('frontend/chapterforming/display');
        $this->view->languages = $languages;
        $this->view->regionIds = $orgIds;

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        // Render a view and return its contents as a string
        return $this->view->render('chapter-forming-form');
    }
}
