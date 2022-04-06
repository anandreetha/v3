<?php

namespace Multiple\Core\Models;

class WidgetSettings extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $widget_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $setting_id;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field widget_id
     *
     * @param integer $widget_id
     * @return $this
     */
    public function setWidgetId($widget_id)
    {
        $this->widget_id = $widget_id;

        return $this;
    }

    /**
     * Method to set the value of field setting_id
     *
     * @param integer $setting_id
     * @return $this
     */
    public function setSettingId($setting_id)
    {
        $this->setting_id = $setting_id;

        return $this;
    }


    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field widget_id
     *
     * @return integer
     */
    public function getWidgetId()
    {
        return $this->widget_id;
    }

    /**
     * Returns the value of field setting_id
     *
     * @return integer
     */
    public function getSettingId()
    {
        return $this->setting_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("widget_settings");
        $this->belongsTo('setting_id', 'Multiple\Core\Models\Setting', 'id', ['alias' => 'Setting']);
        $this->belongsTo('widget_id', 'Multiple\Core\Models\Widget', 'id', ['alias' => 'Widget']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'widget_settings';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return WidgetSettings[]|WidgetSettings|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return WidgetSettings|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
