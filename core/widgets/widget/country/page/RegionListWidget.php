<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class RegionListWidget extends BaseWidget
{
    public function getContent($orgIds, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->ajaxUrl = $renderStaticContent ? ('/bnicms/v3/frontend/regionlist/display') :
            $this->url->get('frontend/regionlist/display');
        $this->logger->debug(
            'Region list url: ' . $this->view->ajaxUrl
        );

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;
        $this->view->orgIds = $orgIds;

        // Render a view and return its contents as a string
        return $this->view->render('region-list-widget');
    }
}
