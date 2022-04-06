<?php

namespace Multiple\Backend\Controllers;

use Exception;
use Multiple\Backend\Services\Service\ImageManipulationService;
use Multiple\Core\Models\PageContentWidgetItems;
use Multiple\Core\Models\PageContentWidgets;
use Multiple\Backend\Exceptions\FileSizeException;

class AlbumController extends BackendBaseController
{

    public function manageAction($pageContentWidgetId)
    {
        $pageContentWidgetId = $this->filter->sanitize($pageContentWidgetId, 'int');
        $pageContentWidget = PageContentWidgets::findFirstById($pageContentWidgetId);

        if ($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        // Create back link to the edit widget
        $this->view->redirectToUrl = $this->tag->linkTo(array("backend/page/editPage/" . PageContentWidgets::find($pageContentWidgetId)[0]->page_content_id, '<i class="fa fa-arrow-left fa-lg pull-right"></i>', "class" => "newPageModal btn btn-default", "title" => $this->translator->_('cms.v3.admin.pages.backpage')));

        $bucket = $this->mongo->selectGridFSBucket();
        if ($this->request->isPost()) {

            // Show message box if images have been uploaded
            $this->view->alertType = "alert-danger";

            if ($this->request->hasFiles() == true) {

                $extIMG = array(
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/gif',
                );

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

                            $image_size = getimagesize($file->getTempName());

                            $date = date("Y-m-d H:i:s");
                            $pageWidgetItems = new PageContentWidgetItems();
                            $pageWidgetItems->page_content_widget_id = $pageContentWidgetId;
                            $pageWidgetItems->object_id = (String)$file_id;
                            $pageWidgetItems->file_name = $file->getName();
                            $pageWidgetItems->file_type = $file->getType();
                            $pageWidgetItems->order = 0;
                            $pageWidgetItems->thumbnail_object_id = (String)$thumbnail_file_id;
                            if ($image_size !== false) {
                                $pageWidgetItems->width = $image_size[0];
                                $pageWidgetItems->height = $image_size[1];
                            }
                            $pageWidgetItems->created = $date;
                            $pageWidgetItems->last_modified = $date;

                            if ($pageWidgetItems->save() === true) {
                                $this->view->alertType = "alert-success";
                                $this->view->message = $file->getName() . ' ' . $this->translator->_('cms.v3.admin.gallery.fileuploadsuccessmsgtxt');

                            } else {
                                throw new Exception();
                            }

                        } catch (FileSizeException $exception) {
                            $this->view->message = $file->getName() . ' ' . $this->translator->_('cms.v3.admin.gallery.maxfilesizeerror');
                        } catch (Exception $exception) {
                            $this->view->message = $file->getName() . ' ' . $this->translator->_('cms.v3.admin.gallery.notuploaded');
                        }
                    } else {
                        $this->view->message = $file->getName() . ' ' . $this->translator->_('cms.v3.admin.gallery.unsupporteduploadedfilesuffixtxt');
                    }
                }
            }
        }

        $associatePageWidgetItems = PageContentWidgetItems::find(
            ['page_content_widget_id = :pageContentWidgetId:',
                'bind' => ['pageContentWidgetId' => $pageContentWidgetId],]
        );

        $images = array();

        foreach ($associatePageWidgetItems as $widgetItem) {
            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($widgetItem->object_id)));

            if (!is_null($bucketFile)) {
                $bucketFile->offsetSet("thumbnail_id", $widgetItem->getThumbnailObjectId());
                $images[] = $bucketFile->jsonSerialize();
            }
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->images = $images;
        $this->view->contentTitle = $this->translator->_('cms.v3.admin.gallery.managealbumheadertxt');
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.gallery.managealbumsubheadertxt');
        $this->view->widgetId = $pageContentWidgetId;

    }

    public function deleteAlbumItemAction($pageContentWidgetId = 0, $objectId = "")
    {

        $pageContentWidgetId = $this->filter->sanitize($pageContentWidgetId, 'int');

        $pageContentWidget = PageContentWidgets::findFirstById($pageContentWidgetId);
        if ($pageContentWidget == false || !$this->hasUserGotPermissionToAccessWebsite($pageContentWidget->PageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $objectId = $this->filter->sanitize($objectId, 'string');

        if ($pageContentWidgetId > 0 && $objectId !== "") {

            // Need to check that the image being deleted is tied to album
            $associatePageWidgetItems = PageContentWidgetItems::find(
                [
                    'page_content_widget_id = :pageContentWidgetId: AND object_id = :objectId:',
                    'bind' => [
                        'pageContentWidgetId' => $pageContentWidgetId,
                        'objectId' => $objectId,
                    ],
                ]
            );

            if (count($associatePageWidgetItems) > 0) {
                $matchingRecord = $associatePageWidgetItems->getFirst();
                $bucket = $this->mongo->selectGridFSBucket();
                $bucket->delete(new \MongoDB\BSON\ObjectId($matchingRecord->object_id));
                if ($matchingRecord->object_id !== $matchingRecord->getThumbnailObjectId()) {
                    $bucket->delete(new \MongoDB\BSON\ObjectId($matchingRecord->getThumbnailObjectId()));
                }
                $matchingRecord->delete();

                $this->flashSession->success($matchingRecord->file_name . " ".$this->translator->_('cms.v3.admin.gallery.imagedeletedsuffixtxt'));
            }

            $this->response->redirect('backend/album/manage/' . $pageContentWidgetId);

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
}