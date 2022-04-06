<?php

namespace Multiple\Core\Models;
use Multiple\Core\Models\Website;

class PageContent extends \Phalcon\Mvc\Model
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
    protected $page_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $nav_name;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $language_id;

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
     * Method to set the value of field page_id
     *
     * @param integer $page_id
     * @return $this
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;

        return $this;
    }

    /**
     * Method to set the value of field title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Method to set the value of field nav_name
     *
     * @param string $nav_name
     * @return $this
     */
    public function setNavName($nav_name)
    {
        $this->nav_name = $nav_name;

        return $this;
    }

    /**
     * Method to set the value of field language_id
     *
     * @param integer $language_id
     * @return $this
     */
    public function setLanguageId($language_id)
    {
        $this->language_id = $language_id;

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
     * Returns the value of field page_id
     *
     * @return integer
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * Returns the value of field title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the value of field nav_name
     *
     * @return string
     */
    public function getNavName()
    {
        return $this->nav_name;
    }

    /**
     * Returns the value of field language_id
     *
     * @return integer
     */
    public function getLanguageId()
    {
        return $this->language_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content");
        $this->belongsTo('language_id', 'Multiple\Core\Models\Language', 'id', ['alias' => 'Language']);
        $this->belongsTo('page_id', 'Multiple\Core\Models\Page', 'id', ['alias' => 'Page']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentSettings', 'page_content_id', ['alias' => 'PageContentSettings']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentWidgets', 'page_content_id', ['alias' => 'Widgets']);
        $this->hasMany('id', 'Multiple\Core\Models\PageContentAllowedWidgets', 'page_content_id', ['alias' => 'AllowedWidgets']);

    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageContent[]|PageContent|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PageContent|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Gets websites of a particular type (country, region, chapter) for certain orgIds
     * @param $websiteId
     * @param $languageId
     * @return bool|\Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function getPageContentForWebsiteAndLanguage($websiteId, $languageId,$type_id='',$country_id='')
    {
        $tables = new \stdClass();
        $tables->PageContent = __NAMESPACE__. '\PageContent';
        $tables->Page = __NAMESPACE__ . '\Page';

        $old_website_id=$websiteId;
		
		if((($type_id=="2")||($type_id=="3"))&&($country_id!="")):			
			$countrytemplate = Website::findfirst([
                'id_country = :idcountry: and type_id=:typeid:',
                'bind' => [
                    'idcountry' => $country_id,
					'typeid'=>$type_id
                ],
            ]);		
			
			if($countrytemplate) $websiteId=$countrytemplate->id;				
		endif;

        $page_content= PageContent::query()
            ->columns("{$tables->PageContent}.*")
            ->innerJoin($tables->Page, "{$tables->PageContent}.page_id = {$tables->Page}.id")
            ->where("{$tables->Page}.website_id=:websiteId:", ['websiteId' => $websiteId])
            ->andWhere("{$tables->PageContent}.language_id=:languageId:", ['languageId' => $languageId])
            ->execute();
			
		if(count($page_content)==0):
			$websiteId=$old_website_id;
			$page_content= PageContent::query()
            ->columns("{$tables->PageContent}.*")
            ->innerJoin($tables->Page, "{$tables->PageContent}.page_id = {$tables->Page}.id")
            ->where("{$tables->Page}.website_id=:websiteId:", ['websiteId' => $websiteId])
            ->andWhere("{$tables->PageContent}.language_id=:languageId:", ['languageId' => $languageId])
            ->execute();
		
		endif;
			
		return $page_content;	

    }

    /**
     * Get the page nav from the language id and page id
     * @param $languageId
     * @param $pageId
     * @return \Phalcon\Mvc\Model\ResultsetInterface
     */
    public static function getNavNameFromForPageAndLanguage($languageId, $pageId) {
        $query = PageContent::query()
            ->columns(__NAMESPACE__.'\PageContent.nav_name')
            ->where('language_id = :language_id: AND page_id = :page_id:', array (
                'language_id' => $languageId,
                'page_id' => $pageId
            ))
            ->execute();

        return $query;
    }

}
