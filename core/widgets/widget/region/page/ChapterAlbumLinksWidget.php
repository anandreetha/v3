<?php

namespace Multiple\Core\Widgets\Widget\Region\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class ChapterAlbumLinksWidget extends BaseWidget
{

    public function getContent($languages, $renderStaticContent, $domainName)
    {
        $this->view->ajaxUrl = $renderStaticContent ? ("/bnicms/v3/frontend/chapteralbumlinks/display") :
            $this->url->get('frontend/chapteralbumlinks/display');
        $this->view->languages = BaseWidget::configureLocales( (array) $languages,$this->request->getLanguages());
        $this->view->code = $languages->activeLanguage->localeCode;
        $this->view->renderStaticContent = $renderStaticContent;

//      A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/region/page/');

        $this->view->$renderStaticContent = $renderStaticContent;

        $website = $this->getWebsite();
        $this->view->websiteId = $website->getId();

        // Render a view and return its contents as a string
        return $this->view->render('chapter-album-links-widget');
    }
}
