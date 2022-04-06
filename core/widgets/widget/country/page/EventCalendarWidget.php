<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class EventCalendarWidget extends BaseWidget
{
    public function getContent($orgIds, $page, $languages)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');
        $website = $page->website;
        $this->view->orgIds = $orgIds;
        $this->view->languages = BaseWidget::configureLocales((array)$languages, $this->request->getLanguages());
        // Render a view and return its contents as a string
        if ($website->getTypeId() == 1) {
            return $this->view->render('event-calendar-widget');
        } else {
            $this->view->setViewsDir('../core/widgets/views/region/page/');
            return $this->view->render('event-region-calendar-widget');
        }
    }
}
