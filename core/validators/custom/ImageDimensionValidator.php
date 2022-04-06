<?php

namespace Multiple\Core\Validators\Custom;

use Phalcon\Di\Injectable;

class ImageDimensionValidator extends Injectable
{
    private $expectedHeight;
    private $expectedWidth;

    /**
     * ImageDimensionValidator constructor.
     * @param $expectedHeight
     * @param $expectedWidth
     */
    public function __construct($expectedHeight, $expectedWidth)
    {
        $this->expectedHeight = $expectedHeight;
        $this->expectedWidth = $expectedWidth;
    }

    public function validateImageDimensions($url)
    {
        // Validate internal urls i.e the urls to the mongo driven library
        if (strpos($url, "/v3/backend/render/renderImage/")) {
            // Get our mongo bucket
            $bucket = $this->mongo->selectGridFSBucket();

            // We are only interested in the file name so do a find and replace
            $file = substr($url, strpos($url, "/renderImage/") + strlen("/renderImage/"));

            // So long as a file name was returned
            if ($file != false && !empty($file)) {
                // Try and retrieve the file information from mongo
                $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId((string)$file)));

                // Matching file data found in MongoDb
                if (!is_null($bucketFile)) {
                    // Create and open a temp file for querying
                    $temp = tmpfile();

                    // Get the temporary files path
                    $path = stream_get_meta_data($temp)['uri'];

                    // Download the content stream to the temp file
                    $bucket->downloadToStream(new \MongoDB\BSON\ObjectId((string)$file), $temp);

                    // Get the width and height of the image
                    list($width, $height) = getimagesize($path);

                    // Close and destroy temp file
                    fclose($temp);

                    $validDimensions = $this->validDimensions(
                        $this->getExpectedHeight(),
                        $height,
                        $this->getExpectedWidth(),
                        $width
                    );

                    if ($validDimensions) {
                        return true;
                    }
                }
            }
        } elseif (!strpos($url, "/v3/backend/render/renderImage/")) {
            // Url structure must conform to following pattern
            $pattern = '/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:[a-zA-Z])|\d+\.\d+\.\d+\.\d+)/';

            // Url is valid
            if (preg_match($pattern, $url)) {
                // Get the image dimensions from the url provided
                @$imageDimensions = getimagesize(str_replace(" ", "%20", $url));

                // Dimensions have been analysed
                if ($imageDimensions != false) {
                    // Get the width and height of the image
                    list($width, $height) = $imageDimensions;

                    $validDimensions = $this->validDimensions(
                        $this->getExpectedHeight(),
                        $height,
                        $this->getExpectedWidth(),
                        $width
                    );
                    if ($validDimensions) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function validDimensions($expectedHeight, $actualHeight, $expectedWidth, $actualWidth)
    {
        $validHeight = $this->validateHeight($expectedHeight, $actualHeight);
        $validWidth = $this->validateWidth($expectedWidth, $actualWidth);

        if ($validHeight && $validWidth) {
            return true;
        }
        return false;
    }

    private function validateHeight($expected, $actual)
    {
        if ($expected === $actual) {
            return true;
        }
        return false;
    }

    private function validateWidth($expected, $actual)
    {
        if ($expected === $actual) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getExpectedHeight()
    {
        return $this->expectedHeight;
    }

    /**
     * @param mixed $expectedHeight
     */
    public function setExpectedHeight($expectedHeight): void
    {
        $this->expectedHeight = $expectedHeight;
    }

    /**
     * @return mixed
     */
    public function getExpectedWidth()
    {
        return $this->expectedWidth;
    }

    /**
     * @param mixed $expectedWidth
     */
    public function setExpectedWidth($expectedWidth): void
    {
        $this->expectedWidth = $expectedWidth;
    }

}