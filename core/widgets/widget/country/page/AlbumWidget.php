<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Models\PageContentWidgetItems;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class AlbumWidget extends BaseWidget
{

    private $widgetItems;
    private $keyIdentifer;

    /**
     * @return mixed
     */
    public function getKeyIdentifer()
    {
        return $this->keyIdentifer;
    }

    /**
     * @param mixed $keyIdentifer
     */
    public function setKeyIdentifer($keyIdentifer)
    {
        $this->keyIdentifer = $keyIdentifer;
    }

    /**
     * @return mixed
     */
    public function getWidgetItems()
    {
        return $this->widgetItems;
    }

    /**
     * @param mixed $widgetItems
     */
    public function setWidgetItems($widgetItems)
    {
        $this->widgetItems = $widgetItems;
    }

    public function getContent()
    {
        $images = array();

        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $widgetSettings = $this->getWidgetSettings();

        // Define the static album file path  for the website
        $albumLibPath = $this->getWebsite()->getDomain() . '/img/album/';

        if (count($widgetSettings) > 0) {
            if (!is_null($this->getWidgetItems())) {
                $associatePageWidgetItems = PageContentWidgetItems::find("page_content_widget_id=" .
                    $this->getWidgetItems()[0]->page_content_widget_id);

                foreach ($associatePageWidgetItems as $widgetItem) {
                    $imageMetaData = array();
                    $imageMetaData["fileName"] = $widgetItem->file_name;
                    $imageMetaData["object"] = $widgetItem->object_id;
                    $imageMetaData["thumbnail_object_id"] = $widgetItem->thumbnail_object_id;

                    if ($this->isRenderStaticContent()) {
                        $pathinfo = pathinfo($widgetItem->file_name);
                        $imageMetaData["staticFileUrl"] = $albumLibPath . $widgetItem->object_id .
                            "." . $pathinfo['extension'];
                        $imageMetaData["staticThumbnailUrl"] = $albumLibPath . $widgetItem->thumbnail_object_id .
                            "." . $pathinfo['extension'];
                    }
                    $images[] = $imageMetaData;
                }
            }
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->images = $images;
        $this->view->widgetSettings = $widgetSettings;
        $this->view->identifier = $this->getKeyIdentifer();

        // Render a view and return its contents as a string
        return $this->view->render('album-widget');
    }

    private function compareFileNames($a, $b)
    {
        return strcasecmp(basename($a["fileName"]), basename($b["fileName"]));
    }


}