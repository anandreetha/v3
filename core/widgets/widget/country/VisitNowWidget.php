<?php

namespace Multiple\Core\Widgets\Widget\Country;

class VisitNowWidget extends BaseWidget
{
    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');

        // Render a view and return its contents as a string
        return $this->view->render('visit-now-widget');
    }
}
