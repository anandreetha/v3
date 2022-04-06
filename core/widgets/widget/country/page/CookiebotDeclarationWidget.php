<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class CookiebotDeclarationWidget extends BaseWidget
{
    public function getContent($languages, $pageMode, $domainName, $renderStaticContent)
    {

        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        //pass parameters to be used in the javascript ajax call

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        $pageMode = isset($pageMode) ? $pageMode : "Live_Site";

        $this->view->languages = $languages;
        $this->view->pageMode = $pageMode;
        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/cookiebotdeclaration/display") :
            $this->url->get('frontend/cookiebotdeclaration/display');

        // Render a view and return its contents as a string
        return $this->view->render('cookiebot-declaration-widget');
    }
}
