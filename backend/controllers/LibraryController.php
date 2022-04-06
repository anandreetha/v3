<?php

namespace Multiple\Backend\Controllers;

use Exception;
use Multiple\Backend\Exceptions\FileSizeException;
use Multiple\Backend\Services\Service\ImageManipulationService;
use Multiple\Core\Models\CommonLibrary;
use Multiple\Core\Models\Website;
use Phalcon\Mvc\View;

class LibraryController extends BackendBaseController
{
    /**
     * @param $websiteId
     * @param $mediaType
     */
    public function manageAction($websiteId, $mediaType = 0)
    {
        $this->view->display = "none";
        $websiteId = $this->filter->sanitize($websiteId, 'int');
        $website = Website::findfirst(
            [
                'id = :id:',
                'bind' => [
                    'id' => $websiteId
                ],
            ]
        );

        if ($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $bucket = $this->mongo->selectGridFSBucket();

        if ($this->request->isPost()) {
            // Show message box if images have been uploaded
            $this->view->display = "block";
            $this->view->alertType = "alert-danger";

            if ($this->request->hasFiles() == true) {
                if ($mediaType === 'images') {
                    $extIMG = array(
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/gif'
                    );
                } else {
                    $extIMG = array(
                        'application/pdf'
                    );
                }

                foreach ($this->request->getUploadedFiles() as $file) {
                    if (in_array($file->getType(), $extIMG)) {
                        try {
                            $filesize = round($file->getSize() / 1024 / 1024, 1);
                            if ($filesize > 10) {
                                throw new FileSizeException();
                            }

                            $manipulationService = new ImageManipulationService();

                            // Save the original file
                            if ($manipulationService->isFileJpeg($file)) {
                                $resource = $manipulationService->compressFile($file, 75);
                            } else {
                                // If they are not jpeg then open the resource to save the original uncompressed image
                                $resource = fopen($file->getTempName(), "a+");
                            }
                            $file_id = $bucket->uploadFromStream($file->getName(), $resource);

                            // Unable to compress gifs, pdf or png files.
                            // In these cases just make the thumbnail id the same as the original
                            if ($manipulationService->isFileJpeg($file)) {
                                // Create a thumbnail and save
                                $stream = $manipulationService->resizeImage($file, 55);
                                $thumbnail_file_id = $bucket->uploadFromStream($file->getName(), $stream);
                                $this->cleanup($stream);
                            } else {
                                $thumbnail_file_id = $file_id;
                            }

                            $commonLibraryItems = $this->
                            createCommonLibraryObject($websiteId, $file_id, $file, $thumbnail_file_id);

                            if ($commonLibraryItems->save() === true) {
                                $this->view->message = $file->getName() . ' ' .
                                    $this->translator->_("cms.v3.admin.library.fileuploadsuccessmsgtxt");
                                $this->view->alertType = "alert-success";
                            } else {
                                throw new Exception();
                            }
                        } catch (FileSizeException $exception) {
                            $this->view->message = $file->getName() . ' ' .
                                $this->translator->_("cms.v3.admin.library.maxfilesizeerrmsg");
                        } catch (Exception $exception) {
                            $this->view->message = $file->getName() . ' ' .
                                $this->translator->_("cms.v3.admin.library.notuploaded");
                        }
                    } else {
                        $this->view->message = $file->getName() . ' ' .
                            $this->translator->_("cms.v3.admin.library.notsupportedfile");
                    }
                }
            }
        }

        $this->view->contentTitle = "Library manager";
        $this->view->contentSubTitle = "Manage Library contents";
        $this->view->setTemplateAfter('ajax-layout');
    }

    public function browseAction($websiteId)
    {
        $this->view->setTemplateAfter('image-selector');
        $websiteId = $this->filter->sanitize($websiteId, 'int');
        $website = Website::findFirstById($websiteId);

        if ($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $this->view->contentTitle = "Image Library";
        $this->view->contentSubTitle = "Select an image";

        $bucket = $this->mongo->selectGridFSBucket();

        // This is actually site library
        $siteLibrary = $website->getCommonLibrary();

        $images = array();

        $fileTypeExcluded = $this->request->get('fileTypeExcluded');
        foreach ($siteLibrary as $libraryItem) {
            $id = $libraryItem->object_id;
            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($id)));
            // Only add to the array if the file type has not been excluded
            if (strpos($fileTypeExcluded, pathinfo($bucketFile->filename)['extension']) === false) {
                if (!is_null($bucketFile)) {
                    $bucketFile->offsetSet("thumbnail_id", $libraryItem->getThumbnailObjectId());
                    $images[] = $bucketFile->jsonSerialize();
                }
            }
        }

        $directory = APP_PATH . "public/img/common-library/";
        $commonLibraryImages = glob($directory . "*.{jpg,png,gif}", GLOB_BRACE);

        foreach ($commonLibraryImages as $key => $image) {
            $obj = new \stdClass();
            $obj->filename = basename($image);
            $obj->filepath = $image;
            $obj->size = filesize($image);
            $obj->uploadDate = date("d/m/Y H:i A", filectime($image));
            $obj->type = "common";

            $images[] = $obj;
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->images = $images;
    }

    public function ckeditorbrowseAction($websiteId)
    {
        $this->view->setTemplateAfter('image-selector');
        $websiteId = $this->filter->sanitize($websiteId, 'int');
        $website = Website::findFirstById($websiteId);

        if ($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $this->view->contentTitle = "Image Library";
        $this->view->contentSubTitle = "Select an image";

        $bucket = $this->mongo->selectGridFSBucket();

        // This is actually site library
        $siteLibrary = $website->getCommonLibrary();

        $images = array();

        $fileTypeExcluded = "pdf";
        foreach ($siteLibrary as $libraryItem) {
            $id = $libraryItem->object_id;
            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($id)));
            // Only add to the array if the file type has not been excluded
            if (strpos($fileTypeExcluded, pathinfo($bucketFile->filename)['extension']) === false) {
                if (!is_null($bucketFile)) {
                    $bucketFile->offsetSet("thumbnail_id", $libraryItem->getThumbnailObjectId());
                    $images[] = $bucketFile->jsonSerialize();
                }
            }
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->images = $images;

        $documents = array();
        $fileTypeAllowed = "pdf";

        foreach ($siteLibrary as $libraryItem) {
            $id = $libraryItem->object_id;
            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($id)));
            // Only add to the array if the file type has not been excluded
            if (strpos($fileTypeAllowed, pathinfo($bucketFile->filename)['extension']) !== false) {
                if (!is_null($bucketFile)) {
                    $documents[] = $bucketFile->jsonSerialize();
                }
            }
        }


        $this->view->documents = $documents;
    }

    public function deleteAction()
    {
        if ($this->request->isPost()) {
            $bucket = $this->mongo->selectGridFSBucket();
            $ids = $ids = json_decode($this->request->getPost("objectId"));

            foreach ($ids as $id) {
                // Delete from MySQL
                $objectId = $this->filter->sanitize($id, 'string');
                $object = CommonLibrary::findFirst([
                    'object_id = :objectId:',
                    'bind' => [
                        'objectId' => $objectId
                    ],
                ]);

                // If we come across an image that the user has no permission to delete then stop processing the loop and return straight away
                if ($object == false || !$this->hasUserGotPermissionToAccessWebsite($object->Website)) {
                    $this->failAndRedirect('backend/error/permissionDenied');
                    return;
                }

                $object->delete();
                // Delete from mongo both the original and the thumbnail
                $bucket->delete(new \MongoDB\BSON\ObjectId($objectId));
                if ($objectId !== $object->getThumbnailObjectId()) {
                    $bucket->delete(new \MongoDB\BSON\ObjectId($object->getThumbnailObjectId()));
                }
            }
        }
    }

    public function renameAction()
    {
        if ($this->request->isPost()) {
            $bucket = $this->mongo->selectGridFSBucket();
            $ids = $ids = json_decode($this->request->getPost("objectId"));
            $newFileNames = json_decode($this->request->getPost("newFileNames"));

            foreach ($ids as $index => $id) {
                // Update in MySQL
                $objectId = $this->filter->sanitize($id, 'string');
                $object = CommonLibrary::findFirst([
                    'object_id = :objectId:',
                    'bind' => [
                        'objectId' => $objectId
                    ],
                ]);

                // If we come across an image that the user has no permission to rename then stop processing the loop and return straight away
                if ($object == false || !$this->hasUserGotPermissionToAccessWebsite($object->Website)) {
                    $this->failAndRedirect('backend/error/permissionDenied');
                    return;
                }

                $extension = pathinfo($object->filename)['extension'];
                if (strlen($newFileNames[$index]) != 0) {
                    $newFileName = $newFileNames[$index] . '.' . $extension;
                    if (strlen($newFileName) <= 255) {
                        $object->setFileName($newFileName);
                        $object->setLastModified(date("Y-m-d H:i:s"));
                        $object->save();
                        // Update in mongo
                        $bucket->rename(new \MongoDB\BSON\ObjectId($objectId), $newFileName);
                    }
                }
            }
        }
    }

    private function compareFileNames($a, $b)
    {
        return strcasecmp($a->filename, $b->filename);
    }


    /**
     * @param $stream
     */
    private function cleanup($stream)
    {
        unset($stream);
        // Lower memory limit again to a more reasonable value
        ini_set('memory_limit', '20M');
    }

    /**
     * @param $websiteId
     * @param $file_id
     * @param $file
     * @param $thumbnail_file_id
     * @return CommonLibrary
     */
    private function createCommonLibraryObject($websiteId, $file_id, $file, $thumbnail_file_id)
    {
        $image_size = getimagesize($file->getTempName());

        $date = date("Y-m-d H:i:s");
        $commonLibraryItems = new CommonLibrary();
        $commonLibraryItems->website_id = $websiteId;
        $commonLibraryItems->object_id = (String)$file_id;
        $commonLibraryItems->file_name = $file->getName();
        $commonLibraryItems->file_type = $file->getType();
        $commonLibraryItems->order = 0;
        $commonLibraryItems->thumbnail_object_id = (String)$thumbnail_file_id;
        if ($image_size !== false) {
            $commonLibraryItems->width = $image_size[0];
            $commonLibraryItems->height = $image_size[1];
        }
        $commonLibraryItems->created = $date;
        $commonLibraryItems->last_modified = $date;
        return $commonLibraryItems;
    }

}