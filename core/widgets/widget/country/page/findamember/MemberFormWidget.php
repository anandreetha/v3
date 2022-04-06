<?php

namespace Multiple\Core\Widgets\Widget\Country\Page\Findamember;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Phalcon\Mvc\View\Simple as SimpleView;

class MemberFormWidget extends BaseWidget
{
    public function getContent($orgIds,$languages,$page)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/findamember/');
        $this->view->orgIds = $orgIds;
        $this->view->languages = BaseWidget::configureLocales( (array) $languages,$this->request->getLanguages());
        $this->view->code = $languages->activeLanguage->localeCode;

        $website = $page->website;
        $this->view->website = $website;
        if($website->getTypeId()==1){
            return $this->view->render('find-a-member-form');
        } else {
            $this->view->setViewsDir('../core/widgets/views/region/page/findamember/');
            return $this->view->render('find-a-member-region-form');
        }


    }

}