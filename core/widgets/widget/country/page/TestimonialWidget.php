<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class TestimonialWidget extends BaseWidget
{
    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->widgetSettings = $this->getWidgetSettings();

        // Render a view and return its contents as a string
        return $this->view->render('testimonial-widget');
    }
}
