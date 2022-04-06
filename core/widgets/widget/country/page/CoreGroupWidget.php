<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 19/10/2017
 * Time: 08:58
 */

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class CoreGroupWidget extends BaseWidget
{

    public function getContent($languages, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->languages = $languages;
        $this->view->ajaxUrl = $renderStaticContent ? ('/bnicms/v3/frontend/coregroup/display') :
            $this->url->get('frontend/coregroup/display');

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        return $this->view->render('core-group-widget');
    }
}
