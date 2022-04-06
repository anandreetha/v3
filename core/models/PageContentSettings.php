<?php

namespace Multiple\Core\Models;

class PageContentSettings extends \Phalcon\Mvc\Model
{
    protected $id;
    protected $page_content_id;
    protected $setting_id;
    protected $value;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $page_content_id
     */
    public function setPageContentId($page_content_id)
    {
        $this->page_content_id = $page_content_id;
    }

    /**
     * @param mixed $setting_id
     */
    public function setSettingId($setting_id)
    {
        $this->setting_id = $setting_id;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPageContentId()
    {
        return $this->page_content_id;
    }

    /**
     * @return mixed
     */
    public function getSettingId()
    {
        return $this->setting_id;
    }



    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("page_content_settings");
        $this->belongsTo('setting_id', 'Multiple\Core\Models\Setting', 'id', ['alias' => 'Setting']);
        $this->belongsTo('page_content_id', 'Multiple\Core\Models\PageContent', 'id', ['alias' => 'PageContent']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'page_content_settings';
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
     * Get the
     * @param $settingId
     * @param $languageId
     * @param $websiteId
     * @return mixed
     * @throws \Exception
     */
    public static function getPageContentSettingsFromJoin($settingId, $languageId, $websiteId)
    {

        // Make sure we've got all the params before trying any query
        // we don't want to update the wrong values if the query partially matches
        if (!isset($settingId) || !isset($languageId) || !isset($websiteId)) {
            throw new \Exception('Missing parameters for SQL query');
        }

        // Not really useful for anything other than display purposes
        $joinTableNames = new \stdClass();
        $joinTableNames->PageContentSettings = __NAMESPACE__ . '\PageContentSettings';
        $joinTableNames->PageContent = __NAMESPACE__ . '\PageContent';
        $joinTableNames->Page = __NAMESPACE__ . '\Page';

        return PageContentSettings::query()
            ->columns("{$joinTableNames->PageContentSettings}.*")
            ->innerJoin($joinTableNames->PageContent, "{$joinTableNames->PageContent}.id = {$joinTableNames->PageContentSettings}.page_content_id")
            ->innerJoin($joinTableNames->Page, "{$joinTableNames->PageContent}.page_id = {$joinTableNames->Page}.id")
            ->where("{$joinTableNames->PageContentSettings}.setting_id={$settingId}")
            ->andWhere("{$joinTableNames->PageContent}.language_id={$languageId}")
            ->andWhere("{$joinTableNames->Page}.website_id={$websiteId}")
            ->execute();

    }

}
