<?php

namespace Multiple\Core\Widgets\Widget\Country;

use DOMDocument;

class WysiwygWidget extends BaseWidget
{
    /**
     * @var null
     */
    private $pageHtmlContent = null;

    public function setHtmlContent($content)
    {
        $this->pageHtmlContent = $content;
    }

    /**
     * @return null
     */
    public function getPageHtmlContent()
    {
        return $this->pageHtmlContent;
    }

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');

        // So long as we have an associated website
        if (!is_null($this->getWebsite())) {
            //If we are rendering the static content we need to update the images to use their static reference
            if ($this->isRenderStaticContent() && !empty($this->getPageHtmlContent())) {
                ini_set('display_errors', 'Off');

                // Create a new DOM document obj
                $doc = new DOMDocument();

                // Load our wysiwyg html and ensure that we have utf-8 encoding
                $doc->loadHTML(mb_convert_encoding($this->getPageHtmlContent(), 'HTML-ENTITIES', 'UTF-8'));

                // Get our mongo bucket
                $bucket = $this->mongo->selectGridFSBucket();

                $this->produceStaticImageLinks($doc, $bucket);

                $this->produceStaticDocumentLinks($doc, $bucket);

                // Display the manipulated html
                $this->setHtmlContent($doc->saveHTML());
            }
        }

        $this->view->setVars(
            [
                'htmlContent' => $this->pageHtmlContent,
            ]
        );

        // Render a view and return its contents as a string
        return $this->view->render('wysiwyg-widget');
    }


    private function produceStaticImageLinks($doc, $bucket)
    {
        // Get all img tags
        $imgTags = $doc->getElementsByTagName('img');

        // Loop through each img tag
        foreach ($imgTags as $tag) {
            // Get the current image source value
            $oldSrc = $tag->getAttribute('src');

            $file = false;

            switch (true) {
                // If this is a site library image - it will use the renderImage url
                case strpos($oldSrc, "/renderImage/"):
                    // We are only interested in the file name so do a find and replace
                    $file = substr($oldSrc, strpos($oldSrc, "/renderImage/") + strlen("/renderImage/"));

                    // So long as a file name was returned
                    if ($file != false && !empty($file)) {
                        // Try and retrieve the information from mongo
                        $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId((string)$file)));

                        // Matching data
                        if (!is_null($bucketFile)) {
                            // Get the file info
                            $fileData = $bucketFile->jsonSerialize();

                            // Get the file extension
                            $fileExt = pathinfo($fileData->filename, PATHINFO_EXTENSION);

                            // Build the new url
                            $newSrc = '../img/site/' . $file . "." . $fileExt;

                            // Replace the url with the new one
                            $tag->setAttribute('src', $newSrc);
                            $tag->setAttribute('alt', $tag->getAttribute('alt'));
                        }
                    }
                    break;

                case strpos($oldSrc, "/common-library/"):
                    // We are only interested in the file name so do a find and replace
                    $file = substr($oldSrc, strpos($oldSrc, "/common-library/") + strlen("/common-library/"));

                    // Build the new url
                    $newSrc = $this->config->general->cdn . '/images/' . $file;

                    // Replace the url with the new one
                    $tag->setAttribute('src', $newSrc);
                    $tag->setAttribute('alt', $tag->getAttribute('alt'));
            }
        }

        return $doc;
    }

    private function produceStaticDocumentLinks($doc, $bucket)
    {
        $hrefTags = $doc->getElementsByTagName('a');

        foreach ($hrefTags as $hrefTag) {
            $oldSrc = $hrefTag->getAttribute('href');

            // If this is a site library image - it will use the renderImage url
            if (strpos($oldSrc, "/renderDocument/")) {
                // We are only interested in the file name so do a find and replace
                $file = substr($oldSrc, strpos($oldSrc, "/renderDocument/") + strlen("/renderDocument/"));

                // So long as a file name was returned
                if ($file != false && !empty($file)) {
                    // Try and retrieve the information from mongo
                    $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId((string)$file)));

                    // Matching data
                    if (!is_null($bucketFile)) {
                        // Get the file info
                        $fileData = $bucketFile->jsonSerialize();

                        // Get the file extension
                        $fileExt = pathinfo($fileData->filename, PATHINFO_EXTENSION);

                        // Build the new url
                        $newSrc = 'http://' . $this->getWebsite()->getDomain() .
                            '/img/site/' . $file . "." . $fileExt;

                        // Replace the url with the new one
                        $hrefTag->setAttribute('href', $newSrc);

                        if ($hrefTag->textContent === $oldSrc) {
                            $hrefTag->textContent = $newSrc;
                        }

                    }
                }
            }
        }

        return $doc;
    }
}
