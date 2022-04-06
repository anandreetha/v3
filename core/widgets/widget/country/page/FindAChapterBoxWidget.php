<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class FindAChapterBoxWidget extends BaseWidget
{
    private $headingText;
    private $subheadingText;
    private $contentText;
    private $searchText;
    private $link;

    /**
     * @return mixed
     */
    public function getHeadingText()
    {
        return $this->headingText;
    }

    /**
     * @param mixed $headingText
     */
    public function setHeadingText($headingText)
    {
        $this->headingText = $headingText;
    }

    /**
     * @return mixed
     */
    public function getSubheadingText()
    {
        return $this->subheadingText;
    }

    /**
     * @param mixed $subheadingText
     */
    public function setSubheadingText($subheadingText)
    {
        $this->subheadingText = $subheadingText;
    }

    /**
     * @return mixed
     */
    public function getContentText()
    {
        return $this->contentText;
    }

    /**
     * @param mixed $contentText
     */
    public function setContentText($contentText)
    {
        $this->contentText = $contentText;
    }

    /**
     * @return mixed
     */
    public function getSearchText()
    {
        return $this->searchText;
    }

    /**
     * @param mixed $searchText
     */
    public function setSearchText($searchText)
    {
        $this->searchText = $searchText;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }


    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->setVars(
            [
                'headingText' => $this->getHeadingText(),
                'subheadingText' => $this->getSubheadingText(),
                'contentText' => $this->getContentText(),
                'searchText' => $this->getSearchText(),
                'link' => $this->getLink()
            ]
        );

        // Render a view and return its contents as a string
        return $this->view->render('find-a-chapter-box-widget');
    }
}
