<?php

namespace Multiple\Core\Models;

class PageContentAllowedWidgets extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $page_content_id;
    protected $widget_id;
    protected $no_allowed_instances;

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
    public function getNoAllowedInstances()
    {
        return $this->no_allowed_instances;
    }

    /**
     * @param mixed $no_allowed_instances
     */
    public function setNoAllowedInstances($no_allowed_instances)
    {
        $this->no_allowed_instances = $no_allowed_instances;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content_allowed_widgets");
        $this->belongsTo('widget_id', 'Multiple\Core\Models\Widget', 'id', ['alias' => 'Widget']);
        $this->belongsTo('page_content_id', 'Multiple\Core\Models\PageContent', 'id', ['alias' => 'PageContent']);
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content_allowed_widgets';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageContent[]|PageContent|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageContent|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }



}