<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 29/09/2017
 * Time: 15:07
 */

namespace Multiple\Core\Widgets\Widget\Country\Page\Advancedchaptersearch;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class AdvancedChapterSearchResultWidget extends BaseWidget
{

    private $widgetContent;

    public function getContent($languages, $pageMode, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $cmsv3 = "true";
        $this->view->cmsv3 = $cmsv3;
        $this->view->languages = $languages;
        $this->view->pageMode = $pageMode;
        $arrayOfMappedSettings = iterator_to_array($this->getMappedWidgetSettings(), false);
        $jsonArrayOfObjects = array();
        foreach ($arrayOfMappedSettings as $value) {
            array_push($jsonArrayOfObjects, $this->toJson($value));
        }

        $this->view->mappedWidgetSettingsObj = $jsonArrayOfObjects;

        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/chapterlist/display"):
            $this->url->get('frontend/chapterlist/display');

        // Render a view and return its contents as a string
        return $this->view->render('advancedchaptersearch/advanced-chapter-search-list');
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
