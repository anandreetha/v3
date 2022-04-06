<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class LeftWysiwygWidget extends BaseWidget
{

    private $widgetContent;

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->widgetContent = $this->widgetContent;

        // Render a view and return its contents as a string
        return $this->view->render('left-wysiwyg-widget');
    }

    /**
     * @param mixed $widgetContent
     */
    public function setWidgetContent($widgetContent)
    {
        $this->widgetContent = $widgetContent;
    }

}

