<?php
/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 28/11/2017
 * Time: 08:26
 */

namespace Multiple\Backend\Services\Service;

use Multiple\Backend\Controllers\BackendBaseController;
use Multiple\Core\Models\CommonLibrary;
use Multiple\Core\Models\PageContentWidgetItems;

class ImageCleanupService extends BackendBaseController
{

    public function deleteImagesFromWebsite($websiteId = 0)
    {
        // Get all page content widget items
        $pageContentWidgetItems = PageContentWidgetItems::GetPageContentWidgetItemsFromWebsiteID($websiteId, 0);
        foreach ($pageContentWidgetItems as $item) {
            $this->deleteImagesFromAlbum($item->page_content_widget_id);
        }
        // Delete from the common_library table
        $this->deleteImagesFromLibrary($websiteId);
    }

    public function deleteImagesFromLibrary($websiteId = 0)
    {
        $bucket = $this->mongo->selectGridFSBucket();
        $websiteId = $this->filter->sanitize($websiteId, 'string');
        $contents = CommonLibrary::find([
            'website_id = :websiteId:',
            'bind' => [
                'websiteId' => $websiteId
            ],
        ]);

        foreach ($contents as $content) {
            $objectId = $content->getObjectId();
            $thumbnailId = $content->getThumbnailObjectId();

            try {
                $bucket->delete(new \MongoDB\BSON\ObjectId($objectId));
                if ($objectId !== $thumbnailId) {
                    $bucket->delete(new \MongoDB\BSON\ObjectId($thumbnailId));
                }
                $content->delete();
            } catch (\Exception $exception) {
                $this->logger->info(
                    'Unable to find image or delete in Mongo of imageid: ' . $objectId
                );
            }
        }

        $this->logger->info(
            'Deleted ' . count($contents) . ' images from library for website id: ' . $websiteId
        );
    }

    public function deleteImagesFromAlbum($pageContentWidgetId = 0)
    {
        $bucket = $this->mongo->selectGridFSBucket();
        $pageContentId = $this->filter->sanitize($pageContentWidgetId, 'string');
        $associatePageWidgetItems = PageContentWidgetItems::find(
            ['page_content_widget_id = :pageContentWidgetId:',
                'bind' => ['pageContentWidgetId' => $pageContentId],]
        );

        foreach ($associatePageWidgetItems as $associatePageWidgetItem) {
            $objectId = $associatePageWidgetItem->getObjectId();
            $thumbnailId = $associatePageWidgetItem->getThumbnailObjectId();

            try {
                $bucket->delete(new \MongoDB\BSON\ObjectId($objectId));

                if ($objectId !== $thumbnailId) {
                    $bucket->delete(new \MongoDB\BSON\ObjectId($thumbnailId));
                }
                $associatePageWidgetItem->delete();
            } catch (\Exception $exception) {
                $this->logger->info(
                    'Unable to find image or delete in Mongo of imageid: ' . $objectId
                );
            }
        }

        $this->logger->info(
            'Deleted ' . count($associatePageWidgetItems) .
            ' images from library for page content widget id: ' . $pageContentWidgetId
        );
    }

}