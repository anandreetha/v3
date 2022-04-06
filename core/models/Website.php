<?php

namespace Multiple\Core\Models;

class Website extends \Phalcon\Mvc\Model
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
    protected $domain;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $clean_domain;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=true)
     */
    protected $type_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $is_default;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $last_published;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $last_modified;

    /**
     * @var string
     * @column(type="string", nullable=false)
     */
    protected $creator;

    /**
     * @var string
     * @column(type="string", nullable=false)
     */
    protected $created_on;

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
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }


    /**
     * @param string $clean_domain
     */
    public function setCleanDomain(string $clean_domain)
    {
        $this->clean_domain = $clean_domain;
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
     * Method to set the value of field is_default
     *
     * @param integer $is_default
     * @return $this
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;

        return $this;
    }

    /**
     * Method to set the value of field $last_published
     *
     * @param string $last_published
     */
    public function setLastPublished($last_published)
    {
        $this->last_published = $last_published;
    }

    /**
     * Method to set the value of field $last_modified
     *
     * @param string $last_modified
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
    }

    /**
     * Method to set the value of creator
     *
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Method to set the value of created_on
     *
     * @param mixed $created_on
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
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
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getCleanDomain(): string
    {
        return $this->clean_domain;
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
     * Returns the value of field is_default
     *
     * @return integer
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * Returns the value of field last_published
     *
     * @return string
     */
    public function getLastPublished()
    {
        return $this->last_published;
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
     * Returns the value of field creator
     *
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }


    /**
     * Returns the value of field createdOn
     *
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("website");
        $this->hasMany('id', 'Multiple\Core\Models\Page', 'website_id', ['alias' => 'Page']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteHierarchy', 'child_website_id', ['alias' => 'WebsiteParent']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteHierarchy', 'parent_website_id', ['alias' => 'WebsiteChildren']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteLanguage', 'website_id', ['alias' => 'WebsiteLanguage']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteSettings', 'website_id', ['alias' => 'WebsiteSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteOrg', 'website_id', ['alias' => 'WebsiteOrg']);
        $this->hasMany('id', 'Multiple\Core\Models\CommonLibrary', 'website_id', ['alias' => 'CommonLibrary']);
        $this->belongsTo('type_id', 'Multiple\Core\Models\WebsiteType', 'id', ['alias' => 'WebsiteType']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'website';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Website[]|Website|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Website|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Gets websites of a particular type (country, region, chapter) for certain orgIds and filter them on the basis of country/region chosen
     *
     * @param $websiteTypeId
     * @param $orgIds
     * @param $parentOrgId
     * @return array
     */
    public static function getFilteredWebsites($websiteTypeId, $orgIds, $parentOrgId = 0)
    {
        $orgs = implode(',', $orgIds);

        if ($parentOrgId > 0) {

            $query = Website::query() -> columns(__NAMESPACE__ . '\Website.*')
                -> join(__NAMESPACE__ . '\WebsiteOrg', __NAMESPACE__ . '\Website.id = wo.website_id', 'wo')
                -> where('type_id = :typeId:  AND wo.parent_org_id=:parentOrgId: AND FIND_IN_SET(wo.org_id, :orgs:)', array('typeId' => $websiteTypeId, 'orgs' => $orgs, 'parentOrgId' => $parentOrgId))
                -> distinct(true)
                -> execute();

        } else {

            switch ($websiteTypeId) {
                case '1':
                case '2':
                    $query = Website::query()->columns(__NAMESPACE__ . '\Website.*')
                        ->join(__NAMESPACE__ . '\WebsiteOrg', __NAMESPACE__ . '\Website.id = wo.website_id', 'wo')
                        ->where('type_id = :typeId: AND FIND_IN_SET(wo.org_id, :orgs:) AND NOT EXISTS (SELECT wo2.id FROM ' . __NAMESPACE__ . '\WebsiteOrg wo2 WHERE wo2.id <> wo.id AND wo2.website_id = wo.website_id AND (NOT FIND_IN_SET(wo2.org_id, :orgs:))) ', array('typeId' => $websiteTypeId, 'orgs' => $orgs))
                        ->distinct(true)
                        ->execute();
                    break;
                case '3':
                    $query = Website::query()->columns(__NAMESPACE__ . '\Website.*')
                        ->join(__NAMESPACE__ . '\WebsiteOrg', __NAMESPACE__ . '\Website.id = wo.website_id', 'wo')
                        ->where('type_id = :typeId: AND FIND_IN_SET(wo.org_id, :orgs:)', array('typeId' => $websiteTypeId, 'orgs' => $orgs))
                        ->distinct(true)
                        ->execute();
                    break;
            }
        }

        if ($query->count()) {
            return $query;
        }

        return array();
    }
	public static function getFilteredDefaultWebsites($optCountryId)
    {
		$idcountry=$optCountryId;
		if($idcountry){
			$isdefault=1;
			$typeId=array(2,3);
			$typeId = implode(',', $typeId);
			$query = Website::query() -> columns(__NAMESPACE__ . '\Website.*')
					//-> join(__NAMESPACE__ . '\WebsiteOrg', __NAMESPACE__ . '\Website.id = wo.website_id', 'wo')
					-> where('FIND_IN_SET(type_id, :typeId:) AND is_default = :isdefault: AND id_country = :idcountry:', array('typeId'=>$typeId,'isdefault' => $isdefault,'idcountry' => $idcountry))
					-> distinct(true)
					-> execute();
			if ($query->count()) {
				return $query;
			}
		}

        return array();
    }
    /**
     * The function brings all the chapter websites for a given country
     * @param $parentOrgId
     * @return array|\Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function getChapterWebsitesForCountry($parentOrgId) {

        $chaptersOrgs= WebsiteOrg::query() ->columns(__NAMESPACE__ . '\WebsiteOrg.*')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  __NAMESPACE__ . '\WebsiteOrg.parent_org_id = wo_region.org_id', 'wo_region')
            -> where('wo_region.parent_org_id = :parentOrgId:', array('parentOrgId' => $parentOrgId))
            -> distinct(true)
            -> execute();

        $chapterWebsiteIds=array();
        foreach($chaptersOrgs as $chaptersOrg){
            $chapterWebsiteIds[]=$chaptersOrg->website_id;
        }

        // Doing in two steps as in Phalcon, will have to change to a single query if it affects the performance, in future
        $query = Website::query() -> columns(__NAMESPACE__ . '\Website.*')
           ->inWhere('id',$chapterWebsiteIds)->execute();

        if ($query->count()) {
            return $query;
        }

        return array();
    }


    /**
     * The below function is used to get all the chapter websites within a region website
     * @param $regionWebsiteId
     * @return array|\Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function getChapterWebsitesForRegion($regionWebsiteId) {
        // TODO Maybe merge into 1 query in future - but currently should be 2 efficient queries, so not too bad performance-wise
        $chaptersOrgs= WebsiteOrg::query() ->columns(__NAMESPACE__ . '\WebsiteOrg.*')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  __NAMESPACE__ . '\WebsiteOrg.parent_org_id = wo_region.org_id', 'wo_region')
            -> where('wo_region.website_id = :regionWebsiteId:', array('regionWebsiteId' => $regionWebsiteId))
            -> distinct(true)
            -> execute();

        $chapterWebsiteIds=array();
        foreach($chaptersOrgs as $chaptersOrg){
            $chapterWebsiteIds[]=$chaptersOrg->website_id;
        }

        $query = Website::query() -> columns(__NAMESPACE__ . '\Website.*')
            ->inWhere('id', $chapterWebsiteIds)
            ->orderBy("name")
            ->execute();

        if ($query->count()) {
            return $query;
        }

        return array();
    }


    /**
     * Returns all child Website models for the current Website instance
     *
     * @return array(Website)
     */
    public function getChildWebsites() {

        $childOrgs= Website::query() -> columns(__NAMESPACE__ . '\Website.*')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  'child_website.website_id = ' . __NAMESPACE__ . '\Website.id', 'child_website')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  'website_org.org_id = child_website.parent_org_id', 'website_org')
            -> where('website_org.website_id = :websiteId:', array('websiteId' => $this->id))
            -> distinct(true)
            -> execute();

        if ($childOrgs->count()) {
            return $childOrgs;
        }

        return array();
    }



    /**
     * Returns the parent Website model for the current Website instance
     *
     * @return array(Website)
     */
    public function getParentWebsite() {

        $parentWebsite= Website::query() -> columns(__NAMESPACE__ . '\Website.*')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  'parent_website.website_id = ' . __NAMESPACE__ . '\Website.id', 'parent_website')
            -> join(__NAMESPACE__ . '\WebsiteOrg',  'website_org.parent_org_id = parent_website.org_id', 'website_org')
            -> where('website_org.website_id = :websiteId:', array('websiteId' => $this->id))
            -> distinct(true)
            -> execute();

        if ($parentWebsite->count()) {
            return $parentWebsite[0];
        }

        return null;
    }
	public function getWebsitebyOrgId($org_id) {

        $Website = Website::query()->columns(__NAMESPACE__ . '\Website.*')
                        ->join(__NAMESPACE__ . '\WebsiteOrg', __NAMESPACE__ . '\Website.id = wo.website_id', 'wo')
                        ->where('wo.org_id=:org_id:', array('org_id' => $org_id))
                        ->distinct(true)
                        ->execute();

        if ($Website->count()) {
            return $Website[0];
        }

        return null;
    }

}
