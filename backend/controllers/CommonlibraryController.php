<?php

namespace Multiple\Backend\Controllers;


class CommonlibraryController extends BackendBaseController
{
    public function browseAction()
    {
        $this->view->setTemplateAfter('image-selector');


        $directory = APP_PATH . "public/img/common-library/";
        $images = glob($directory . "*.{jpg,png,gif}", GLOB_BRACE);

        foreach ($images as $key => $image) {

            $images[$key] = array(
                "image" => $image,
                "size" => filesize($image),
                "modifiedDate" => date("d/m/Y H:i A", filectime($image))
            );
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->contentTitle = "Common Library";
        $this->view->contentSubTitle = "Select an item";
        $this->view->images = $images;
    }

    private function compareFileNames($a, $b)
    {
        return strcasecmp(basename($a["image"]), basename($b["image"]));
    }

}