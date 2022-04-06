<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class ApplicationRegistrationWidget extends BaseWidget
{
    public function getContent()
    {
        $this->view->setViewsDir('../core/widgets/views/country/page/');
        return $this->view->render('application-registration-widget');
    }
}
