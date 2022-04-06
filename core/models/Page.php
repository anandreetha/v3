<?php

namespace Multiple\Core\Models;

class Page extends \Phalcon\Mvc\Model
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
    protected $website_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $last_modified;


    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $nav_order;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $template;

    protected $enabled;

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
     * Method to set the value of field website_id
     *
     * @param integer $website_id
     * @return $this
     */
    public function setWebsiteId($website_id)
    {
        $this->website_id = $website_id;

        return $this;
    }

    /**
     * Method to set the value of field last_modified
     *
     * @param string $last_modified
     * @return $this
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;

        return $this;
    }


    /**
     * Method to set the value of field nav_order
     *
     * @param integer $nav_order
     * @return $this
     */
    public function setNavOrder($nav_order)
    {
        $this->nav_order = $nav_order;

        return $this;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
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
     * Returns the value of field website_id
     *
     * @return integer
     */
    public function getWebsiteId()
    {
        return $this->website_id;
    }

    /**
     * Returns the value of field last_modified
     *
     * @return string
     */
    public function getLastModified()
    {
        return $this->last_modified;
    }


    /**
     * Returns the value of field nav_order
     *
     * @return integer
     */
    public function getNavOrder()
    {
        return $this->nav_order;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page");
        $this->hasMany('id', 'Multiple\Core\Models\PageContent', 'page_id', ['alias' => 'PageContent']);
        $this->hasMany('id', 'Multiple\Core\Models\PageHierarchy', 'child_page_id', ['alias' => 'PageHierarchy']);
        $this->hasMany('id', 'Multiple\Core\Models\PageHierarchy', 'parent_page_id', ['alias' => 'PageHierarchy']);
        $this->belongsTo('website_id', 'Multiple\Core\Models\Website', 'id', ['alias' => 'Website']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Page[]|Page|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Page|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function GetPageFromPageContentId($pageContentId, $widgetId)
    {
        $tables = new \stdClass();
        $tables->PageContentWidgets = __NAMESPACE__ . '\PageContentWidgets';
        $tables->PageContent = __NAMESPACE__ . '\PageContent';
        $tables->Page = __NAMESPACE__ . '\Page';

        return static::query()
            ->columns("{$tables->Page}.*")
            ->innerJoin("{$tables->PageContent}")
            ->innerJoin($tables->PageContentWidgets)
            ->where("{$tables->PageContentWidgets}.page_content_id = {$tables->PageContent}.id")
            ->andWhere("{$tables->PageContent}.page_id = {$tables->Page}.id")
            ->andWhere("{$tables->PageContentWidgets}.page_content_id = :pageContentId:", array('pageContentId' => $pageContentId))
            ->andWhere("{$tables->PageContentWidgets}.widget_id=:widgetId:", array('widgetId' => $widgetId))
            ->execute();
    }
}
