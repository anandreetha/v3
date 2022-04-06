<?php

namespace Multiple\Core\Models;

class PageContentWidgets extends \Phalcon\Mvc\Model
{

    protected $id;

    protected $widget_id;

    protected $page_content_id;

    protected $widget_order;

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
    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * @param mixed $widget_id
     */
    public function setWidgetId($widget_id)
    {
        $this->widget_id = $widget_id;
    }

    /**
     * @return mixed
     */
    public function getPageContentId()
    {
        return $this->page_content_id;
    }

    /**
     * @param mixed $page_content_id
     */
    public function setPageContentId($page_content_id)
    {
        $this->page_content_id = $page_content_id;
    }

    /**
     * @return mixed
     */
    public function getWidgetOrder()
    {
        return $this->widget_order;
    }

    /**
     * @param mixed $widget_order
     */
    public function setWidgetOrder($widget_order)
    {
        $this->widget_order = $widget_order;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content_widgets");
        $this->belongsTo('page_content_id', 'Multiple\Core\Models\PageContent', 'id', ['alias' => 'PageContent']);
        $this->belongsTo('widget_id', 'Multiple\Core\Models\Widget', 'id', ['alias' => 'Widget']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentWidgetSettings', 'page_content_widget_id', ['alias' => 'PageContentWidgetSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentWidgetItems', 'page_content_widget_id', ['alias' => 'PageContentWidgetItems']);

    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content_widgets';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageWidgets[]|PageWidgets|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageWidgets|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
