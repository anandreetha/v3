<?php

namespace Multiple\Core\Models;

class PageHierarchy extends \Phalcon\Mvc\Model
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
    protected $parent_page_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $child_page_id;

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
     * Method to set the value of field parent_page_id
     *
     * @param integer $parent_page_id
     * @return $this
     */
    public function setParentPageId($parent_page_id)
    {
        $this->parent_page_id = $parent_page_id;

        return $this;
    }

    /**
     * Method to set the value of field child_page_id
     *
     * @param integer $child_page_id
     * @return $this
     */
    public function setChildPageId($child_page_id)
    {
        $this->child_page_id = $child_page_id;

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
     * Returns the value of field parent_page_id
     *
     * @return integer
     */
    public function getParentPageId()
    {
        return $this->parent_page_id;
    }

    /**
     * Returns the value of field child_page_id
     *
     * @return integer
     */
    public function getChildPageId()
    {
        return $this->child_page_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_hierarchy");
        $this->belongsTo('child_page_id', 'Multiple\Core\Models\Page', 'id', ['alias' => 'Page']);
        $this->belongsTo('parent_page_id', 'Multiple\Core\Models\Page', 'id', ['alias' => 'Page']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_hierarchy';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageHierarchy[]|PageHierarchy|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageHierarchy|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
