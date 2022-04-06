<?php

namespace Multiple\Core\Models;

class Widget extends \Phalcon\Mvc\Model
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
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $desc;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $type_id;

    protected $allow_multiple;

    protected $is_editable;

    protected $is_deletable;

    protected $has_widget_content;

    protected $translate_token = "";

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
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field desc
     *
     * @param string $desc
     * @return $this
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;

        return $this;
    }

    /**
     * Method to set the value of field type_id
     *
     * @param integer $type_id
     * @return $this
     */
    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;

        return $this;
    }

    /**
     * @param integer $allow_multiple
     */
    public function setAllowMultiple($allow_multiple)
    {
        $this->allow_multiple = $allow_multiple;
    }

    /**
     * @param integer $is_editable
     */
    public function setIsEditable($is_editable)
    {
        $this->is_editable = $is_editable;
    }

    /**
     * @param integer $is_deletable
     */
    public function setIsDeletable($is_deletable)
    {
        $this->is_deletable = $is_deletable;
    }

    /**
     * @param mixed $has_widget_content
     */
    public function setHasWidgetContent($has_widget_content)
    {
        $this->has_widget_content = $has_widget_content;
    }

    /**
     * @param string $translate_token
     */
    public function setTranslateToken(string $translate_token): void
    {
        $this->translate_token = $translate_token;
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
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field desc
     *
     * @return string
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * Returns the value of field type_id
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * @return integer
     */
    public function getAllowMultiple()
    {
        return $this->allow_multiple;
    }

    /**
     * @return integer
     */
    public function getIsEditable()
    {
        return $this->is_editable;
    }

    /**
     * @return integer
     */
    public function getIsDeletable()
    {
        return $this->is_deletable;
    }

    /**
     * @return mixed
     */
    public function getHasWidgetContent()
    {
        return $this->has_widget_content;
    }

    /**
     * @return string
     */
    public function getTranslateToken(): string
    {
        return $this->translate_token;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("widget");
        # $this->hasMany('id', 'Multiple\Core\Models\PageWidgets', 'widget_id', ['alias' => 'PageWidgets']);
        # $this->hasMany('id', 'Multiple\Core\Models\WebsiteWidgets', 'widget_id', ['alias' => 'WebsiteWidgets']);
        $this->hasMany('id', 'Multiple\Core\Models\WidgetSettings', 'widget_id', ['alias' => 'WidgetSettings']);
        $this->belongsTo('type_id', 'Multiple\Core\Models\WidgetType', 'id', ['alias' => 'WidgetType']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'widget';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Widget[]|Widget|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Widget|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
