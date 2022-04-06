<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 29/09/2017
 * Time: 09:44
 */

namespace Multiple\Core\Models;

class PageContentWidgetSettings extends \Phalcon\Mvc\Model
{

    protected $id;

    protected $page_content_widget_id;

    protected $setting_id;

    protected $value;

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
    public function getSettingId()
    {
        return $this->setting_id;
    }

    /**
     * @param mixed $setting_id
     */
    public function setSettingId($setting_id)
    {
        $this->setting_id = $setting_id;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content_widget_settings");
        $this->belongsTo('page_content_widget_id', 'Multiple\Core\Models\PageContentWidgets', 'id', ['alias' => 'PageContentWidgets']);
        $this->belongsTo('setting_id', 'Multiple\Core\Models\Setting', 'id', ['alias' => 'Setting']);

    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content_widget_settings';
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