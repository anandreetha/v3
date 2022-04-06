<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 05/10/2017
 * Time: 13:51
 */

namespace Multiple\Core\Widgets\Widget\Country\Page\Advancedchaptersearch;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class AdvancedChapterSearchDetailWidget extends BaseWidget
{

    private $widgetContent;

    public function getContent($pageMode, $languages, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');


        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/chapterdetail/display") :
            $this->url->get('frontend/chapterdetail/display');

        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;
        $this->view->pageMode = $pageMode;
        $this->view->code = $languages->activeLanguage->localeCode;
        // Render a view and return its contents as a string
        return $this->view->render('advancedchaptersearch/advanced-chapter-search-detail');
    }
}
