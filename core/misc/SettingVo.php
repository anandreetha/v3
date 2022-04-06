<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 04/01/2018
 * Time: 09:52
 */

namespace Multiple\Core\Misc;

class SettingVo
{
    private $id;
    private $name;
    private $value;
    private $type;
    private $data_type;
    private $form_input_type;
    private $translate_token;
    private $usingDefault = false;
    private $displayOrder;


    /**
     * SettingKeyNameValue constructor.
     * @param $key
     * @param $name
     * @param $value
     */
    public function __construct($id = 0, String $name = "", $value = "")
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(String $name): void
    {
        $this->name = $name;
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
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->data_type;
    }

    /**
     * @param mixed $data_type
     */
    public function setDataType($data_type): void
    {
        $this->data_type = $data_type;
    }

    /**
     * @return mixed
     */
    public function getFormInputType()
    {
        return $this->form_input_type;
    }

    /**
     * @param mixed $form_input_type
     */
    public function setFormInputType($form_input_type): void
    {
        $this->form_input_type = $form_input_type;
    }

    /**
     * @return mixed
     */
    public function getTranslateToken()
    {
        return $this->translate_token;
    }

    /**
     * @param mixed $translate_token
     */
    public function setTranslateToken($translate_token): void
    {
        $this->translate_token = $translate_token;
    }


    /**
     * @return bool
     */
    public function isUsingDefault(): bool
    {
        return $this->usingDefault;
    }

    /**
     * @param bool $usingDefault
     */
    public function setUsingDefault(bool $usingDefault): void
    {
        $this->usingDefault = $usingDefault;
    }

    /**
     * @return mixed
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param mixed $displayOrder
     */
    public function setDisplayOrder($displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }


}