<?php

namespace Multiple\Core\Models;

class Setting extends \Phalcon\Mvc\Model
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
     * @var string
     * @Column(type="string", length=500, nullable=false)
     */
    protected $default_value;

    /**
     *
     * @var string
     * @Column(type="string", length=1, nullable=true)
     */
    protected $type;

    protected $data_type;

    protected $form_input_type;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $translate_token = "";

    protected $display_order;
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
     * Method to set the value of field default_value
     *
     * @param string $default_value
     * @return $this
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;

        return $this;
    }

    /**
     * Method to set the value of field type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $data_type
     */
    public function setDataType($data_type)
    {
        $this->data_type = $data_type;
    }

    /**
     * @param mixed $form_input_type
     */
    public function setFormInputType($form_input_type)
    {
        $this->form_input_type = $form_input_type;
    }

    /**
     * @param string $translate_token
     */
    public function setTranslateToken(string $translate_token)
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
     * Returns the value of field default_value
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * Returns the value of field type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->data_type;
    }

    /**
     * @return mixed
     */
    public function getFormInputType()
    {
        return $this->form_input_type;
    }

    /**
     * @return string
     */
    public function getTranslateToken()
    {
        return $this->translate_token;
    }

    /**
     * @return mixed
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param mixed $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = $display_order;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("setting");
        $this->hasMany('id', 'Multiple\Core\Models\PageSettings', 'settings_id', ['alias' => 'PageSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteSettings', 'settings_id', ['alias' => 'WebsiteSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\WidgetSettings', 'setting_id', ['alias' => 'WidgetSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentSettings', 'setting_id', ['alias' => 'PageContentSettings']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'setting';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Setting[]|Setting|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Setting|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
