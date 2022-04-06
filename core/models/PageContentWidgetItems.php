<?php

namespace Multiple\Core\Models;

class PageContentWidgetItems extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $page_content_widget_id;
    protected $object_id;
    protected $file_name;
    protected $file_type;
    protected $order;
    protected $thumbnail_object_id;
    protected $width;
    protected $height;
    protected $created;
    protected $last_modified;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPageContentWidgetId()
    {
        return $this->page_content_widget_id;
    }

    /**
     * @param mixed $page_content_widget_id
     */
    public function setPageContentWidgetId($page_content_widget_id)
    {
        $this->page_content_widget_id = $page_content_widget_id;
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @param mixed $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param mixed $file_name
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }

    /**
     * @return mixed
     */
    public function getFileType()
    {
        return $this->file_type;
    }

    /**
     * @param mixed $file_type
     */
    public function setFileType($file_type)
    {
        $this->file_type = $file_type;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getThumbnailObjectId()
    {
        return $this->thumbnail_object_id;
    }

    /**
     * @param mixed $thumbnail_object_id
     */
    public function setThumbnailObjectId($thumbnail_object_id)
    {
        $this->thumbnail_object_id = $thumbnail_object_id;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->last_modified;
    }

    /**
     * @param mixed $last_modified
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content_widget_items");
        $this->belongsTo('page_content_widget_id', 'Multiple\Core\Models\PageContentWidgets', 'id', ['alias' => 'PageContentWidget']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content_widget_items';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageSettings[]|PageSettings|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageSettings|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function GetPageContentWidgetItemsFromWebsiteID($websiteId, $widgetId)
    {
        $tables = new \stdClass();
        $tables->PageContentWidgetItems = __NAMESPACE__ . '\PageContentWidgetItems';
        $tables->PageContentWidgets = __NAMESPACE__ . '\PageContentWidgets';
        $tables->PageContent = __NAMESPACE__ . '\PageContent';
        $tables->Page = __NAMESPACE__ . '\Page';

        return static::query()
            ->columns("DISTINCT({$tables->PageContentWidgetItems}.page_content_widget_id ) AS page_content_widget_id")
            ->join("{$tables->Page}")
            ->join("{$tables->PageContent}", "{$tables->Page}.id = {$tables->PageContent}.page_id")
            ->join("{$tables->PageContentWidgets}", "{$tables->PageContent}.id = {$tables->PageContentWidgets}.page_content_id")
            ->join("{$tables->PageContentWidgetItems}", "{$tables->PageContentWidgets}.id = {$tables->PageContentWidgetItems}.page_content_widget_id", "pcwi")
            ->where("{$tables->Page}.website_id = " . $websiteId)
            ->execute();
    }
}
