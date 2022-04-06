<?php
/**
 * Created by PhpStorm.
 * User: shabnam.sidhik
 * Date: 03/10/2017
 * Time: 11:02
 */

namespace Multiple\Core\Models;


class WebsiteOrg extends \Phalcon\Mvc\Model
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
    public $website_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $org_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $parent_org_id;

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
     * Method to set the value of field org_id
     *
     * @param integer $org_id
     * @return $this
     */
    public function setOrg($org_id)
    {
        $this->org_id = $org_id;

        return $this;
    }

    /**
     * @param int $parent_org_id
     */
    public function setParentOrgId(int $parent_org_id): void
    {
        $this->parent_org_id = $parent_org_id;
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
     * Returns the value of field language_id
     *
     * @return integer
     */
    public function getOrgId()
    {
        return $this->org_id;
    }

    public function getParentOrgId()
    {
        return $this->parent_org_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("website_org");
        $this->belongsTo('website_id', 'Multiple\Core\Models\Website', 'id', ['alias' => 'Website']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'website_org';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return WebsiteLanguage[]|WebsiteLanguage|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return WebsiteLanguage|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    /**
     * Get website orgs of specific website type.
     * @param $websiteTypeId
     * @return array
     */
    public static function getWebsiteOrgPerType($websiteTypeId)
    {

        $query = WebsiteOrg::query()->columns(__NAMESPACE__ . '\WebsiteOrg.*')
            ->join(__NAMESPACE__ . '\Website', __NAMESPACE__ . '\WebsiteOrg.website_id = ws.id', 'ws')
            ->where('ws.type_id = :typeId:', array('typeId' => $websiteTypeId))
            ->distinct(true)
            ->execute();

        if ($query->count()) {
            return $query;
        }

        return array();
    }
}