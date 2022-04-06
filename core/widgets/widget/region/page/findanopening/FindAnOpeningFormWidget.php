<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 24/10/2017
 * Time: 08:50
 */

namespace Multiple\Core\Widgets\Widget\Region\Page\Findanopening;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class FindAnOpeningFormWidget extends BaseWidget
{
    public function getContent($languages, $orgIds)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/region/page/');

        $this->view->languages = BaseWidget::configureLocales((array)$languages, $this->request->getLanguages());
        $this->view->orgIds = $orgIds;
        $this->widgetSettingObj = $this->getWidgetSettings();

        // Render a view and return its contents as a string
        return $this->view->render('findanopening/find-an-opening-form');
    }
}
