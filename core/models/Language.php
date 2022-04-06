<?php

namespace Multiple\Core\Models;

class Language extends \Phalcon\Mvc\Model
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
     * @Column(type="string", length=10, nullable=false)
     */
    protected $locale_code;

	protected $cookiebot_code;
    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $description_key;

    /**
     *
     * @var string
     * @Column(type="string", length=3, nullable=false, default="ltr")
     */
    protected $language_orientation;

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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLocaleCode(): string
    {
        return $this->locale_code;
    }
	public function getCookiebotCode(): string
    {
        return $this->cookiebot_code;
    }
    /**
     * @param string $locale_code
     */
    public function setLocaleCode(string $locale_code)
    {
        $this->locale_code = $locale_code;
    }

    /**
     * @return string
     */
    public function getDescriptionKey(): string
    {
        return trim($this->description_key);
    }

    /**
     * @param string $description_key
     */
    public function setDescriptionKey(string $description_key)
    {
        $this->description_key = $description_key;
    }

    /**
     * @return string
     */
    public function getLanguageOrientation(): string
    {
        return $this->language_orientation;
    }

    /**
     * @param string $language_orientation
     */
    public function setLanguageOrientation(string $language_orientation)
    {
        $this->language_orientation = $language_orientation;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("cms_v3");
        $this->setSource("language");
        $this->hasMany('id', 'Multiple\Core\Models\PageContent', 'language_id', ['alias' => 'PageContent']);
        $this->hasMany('id', 'Multiple\Core\Models\WebsiteLanguage', 'language_id', ['alias' => 'WebsiteLanguage']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'language';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Language[]|Language|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Language|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
