<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class NewsWidget extends BaseWidget
{
    public function getContent($orgIds, $languages, $domainName, $renderStaticContent)
    {
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/news/display") :
            $this->url->get('frontend/news/display');
        $this->logger->debug(
            'News url: ' . $this->view->ajaxUrl
        );
        $this->view->languages = $languages;
        $this->view->orgIds = $orgIds;

        // Render a view and return its contents as a string
        return $this->view->render('news-widget');
    }
}
