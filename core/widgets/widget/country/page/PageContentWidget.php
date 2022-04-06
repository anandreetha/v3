<?php
namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class PageContentWidget extends BaseWidget
{

    private $widgetContent;

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->widgetContent = $this->getWidgetContent();

        // Render a view and return its contents as a string
        return $this->view->render('page-content');
    }

    /**
     * @return mixed
     */
    public function getWidgetContent()
    {
        return $this->widgetContent;
    }

    /**
     * @param mixed $widgetContent
     */
    public function setWidgetContent($widgetContent)
    {
        $this->widgetContent = $widgetContent;
    }
}
