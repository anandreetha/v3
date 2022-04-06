<?php

namespace Multiple\Core\Widgets\Widget\Country;

class SideNavWidget extends BaseWidget
{

    public function getContent($navItems)
    {
        $this->view->navItems = $navItems;

        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');

        // Render a view and return its contents as a string
        return $this->view->render('side-nav-widget');
    }
}
