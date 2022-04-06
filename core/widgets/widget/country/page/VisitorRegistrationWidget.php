<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class VisitorRegistrationWidget extends BaseWidget
{
    public function getContent($orgIds, $languages, $page, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

//        $this->view->languages = $languages;
//        $this->view->orgIds = $orgIds;
//        $this->view->website = $page->website;
//        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/contactus/display") :
//            $this->url->get('frontend/contactus/display');

        // Render a view and return its contents as a string
        return $this->view->render('visitor-registration-widget');
    }
}
