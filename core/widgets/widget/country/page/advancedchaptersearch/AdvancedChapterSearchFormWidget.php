<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 05/10/2017
 * Time: 11:55
 */

namespace Multiple\Core\Widgets\Widget\Country\Page\Advancedchaptersearch;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class AdvancedChapterSearchFormWidget extends BaseWidget
{
    public function getContent($orgIds, $languages, $page)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/advancedchaptersearch/');

        $this->view->orgIds = $orgIds;
        $this->view->languages = BaseWidget::configureLocales((array)$languages, $this->request->getLanguages());
        $website = $page->website;

        // Render a view and return its contents as a string
        if ($website->getTypeId() == 1) {
            return $this->view->render('advanced-chapter-search-form');
        } else {
            $this->view->setViewsDir('../core/widgets/views/region/page/advancedchaptersearch/');
            return $this->view->render('advanced-chapter-search-region-form');
        }
    }
}
