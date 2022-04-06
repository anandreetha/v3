<?php

namespace Multiple\Core\Models;

class WebsiteHierarchy extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $parent_website_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $child_website_id;

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
     * Method to set the value of field parent_website_id
     *
     * @param integer $parent_website_id
     * @return $this
     */
    public function setParentWebsiteId($parent_website_id)
    {
        $this->parent_website_id = $parent_website_id;

        return $this;
    }

    /**
     * Method to set the value of field child_website_id
     *
     * @param integer $child_website_id
     * @return $this
     */
    public function setChildWebsiteId($child_website_id)
    {
        $this->child_website_id = $child_website_id;

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
     * Returns the value of field parent_website_id
     *
     * @return integer
     */
    public function getParentWebsiteId()
    {
        return $this->parent_website_id;
    }

    /**
     * Returns the value of field child_website_id
     *
     * @return integer
     */
    public function getChildWebsiteId()
    {
        return $this->child_website_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("website_hierarchy");
        $this->belongsTo('child_website_id', 'Multiple\Core\Models\Website', 'id', ['alias' => 'Website']);
        $this->belongsTo('parent_website_id', 'Multiple\Core\Models\Website', 'id', ['alias' => 'Website']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'website_hierarchy';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return WebsiteHierarchy[]|WebsiteHierarchy|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return WebsiteHierarchy|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
