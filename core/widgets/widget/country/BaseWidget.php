<?php

namespace Multiple\Core\Widgets\Widget\Country;

use Phalcon\Mvc\View\Simple as SimpleView;
use Phalcon\Mvc\User\Component;

class BaseWidget extends Component
{
    private $widgetSettings;
    private $mappedWidgetSettings;
    private $renderStaticContent = false;
    private $website;

    protected $view;

    const JAVA_TWELVE_HOUR_FORMAT = "h:mm a";

    const PHP_TWELVE_HOUR_FORMAT = "h:i A";

    const JAVA_24_HOUR_FORMAT = "HH:mm";

    const PHP_24_FORMAT = "H:i";

    function __construct()
    {
        $this->view = new SimpleView();
    }

    /**
     * @return mixed
     */
    public function getWidgetSettings()
    {
        return $this->widgetSettings;
    }

    /**
     * @param mixed $widgetSettings
     */
    public function setWidgetSettings($widgetSettings)
    {
        $this->widgetSettings = $widgetSettings;
        $this->view->widgetSettingObj = $widgetSettings;
    }

    private static function languageToArray($language)
    {
        // If already an array return it
        if (is_array($language)) {
            return $language;
        }

        // Convert to array and return
        $languageArray = array(
            "localCode" => $language->localeCode,
            "descriptionKey" => $language->descriptionKey);

        return $languageArray;
    }

    /*
     * Works out which language to use (based on the ones available for a website,
     * the browser's locale via the accept-lang header, and the currently active language for the website),
     * then returns a built locale query string for BNIC usage
     */
    public static function configureLocales($languages, $acceptLangs)
    {
        $determinedlanguages = BaseWidget::getLanguages(
            $languages["availableLanguages"],
            array(BaseWidget::languageToArray($languages["activeLanguage"]))
        );

        return BaseWidget::buildLocaleQueryStringParams($determinedlanguages[1]);
    }

    /**
     * This function will return a querystring based upon the available languages for the
     * site and the selected language in the request
     * The selected language will be use if it is one of the available (which it should be)
     * otherwise default to the first available language
     *
     * Now also sets request_locale - this will be picked up by the i18N Interceptor
     * when calling BNI data endpoints from lives sites.
     * Otherwise it defaults to the browser language (this issue also existed in V2)
     *
     * @param $localeToUse - such as es or en_GB
     * @return string - up to three query string param values which help drive localisation within BNIC
     */
    public static function buildLocaleQueryStringParams($localeToUse)
    {
        $localeParts = preg_split('~_~', $localeToUse);

        $queryString = "&request_locale=" . $localeToUse . "&siteLocale=" . $localeParts[0];
        if (sizeof($localeParts) > 1) {
            $queryString .= "&siteLocaleCountry=" . $localeParts[1];
        }

        return $queryString;
    }

    /*
     * This version takes into account browser language and gives precedence to it
     * keeping as may be handy in the future

    public static function configureLocales($languages,$acceptLangs)
    {
        $queryString="";
        $siteLanguages = $languages["availableLanguages"];// languages chosen from site

        $languages = BaseWidget::getLanguages($siteLanguages, $acceptLangs);
        $browserLang =$languages[0];
        $siteLocale=$languages[1];
        $queryString .= "&browserLocale=".preg_split('~_~',$browserLang["language"])[0];
        if(sizeof(preg_split('~_~',$browserLang["language"]))>1){
            $queryString .= "&browserLocaleCountry=".preg_split('~_~',$browserLang["language"])[1];
        }
        $queryString .= "&siteLocale=".preg_split('~_~',$siteLocale)[0];
        if(sizeof(preg_split('~_~',$siteLocale) )>1){
            $queryString .= "&siteLocaleCountry=" . preg_split('~_~', $siteLocale)[1];
        }
        return $queryString;
    }
    */

    public static function getLanguages($siteLanguages, $browserLanguages)
    {

        $browserLang = str_replace('-', '_', $browserLanguages[0]);

        foreach ($siteLanguages as $siteLanguage) {
            $siteLanguage = (array)$siteLanguage;
            $siteLang[] = $siteLanguage["localeCode"];
        }

        if ($browserLang == null) {
            $siteLocale = $siteLang[0];
        } else {
            $configuredLocale = array_intersect($siteLang, $browserLang);
            if ($configuredLocale != null) {
                $siteLocale = implode(',', $configuredLocale);
            } else {
                $siteLocale = $siteLang[0];
            }
        }
        return array($browserLang, $siteLocale);
    }

    /**
     * Will be removed, need to pass in via widget properties instead
     * @return mixed
     */
    protected function getPageMode()
    {
        return $this->view->getVar("pageMode");
    }


    /**
     * It takes and incoming token Array and hour and first it find the timeFormat token, then it transform it from
     * a Java format to php format and then it use this php format to return the formatted time.
     * @param $hour
     * @return string
     */
    public static function getHour($hour, $tokenArray)
    {

        $timeFormat = $tokenArray->_("common.dateformat.shorttime");
        if ($timeFormat == self::JAVA_TWELVE_HOUR_FORMAT) {
            $timeFormat = self::PHP_TWELVE_HOUR_FORMAT;
        } elseif ($timeFormat == self::JAVA_24_HOUR_FORMAT) {
            $timeFormat = self::PHP_24_FORMAT;
        }
        $time = new \DateTime($hour);
        $formattedTime = $time->format($timeFormat);

        // if the time is 12 format then replace the am or pm with the value from the the token.
        if ($timeFormat == self::JAVA_TWELVE_HOUR_FORMAT) {
            $formattedTime = str_replace("AM", $tokenArray->_("admin.chapter.editattributes.am"), $formattedTime);
            $formattedTime = ltrim($formattedTime, '0');
        } else {
            $formattedTime = str_replace("PM", $tokenArray->_("admin.chapter.editattributes.pm"), $formattedTime);
            $formattedTime = ltrim($formattedTime, '0');
        }
        return $formattedTime;
    }

    /**
     * @return bool
     */
    public function isRenderStaticContent(): bool
    {
        return $this->renderStaticContent;
    }

    /**
     * @param bool $renderStaticContent
     */
    public function setRenderStaticContent(bool $renderStaticContent): void
    {
        $this->renderStaticContent = $renderStaticContent;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     */
    public function setWebsite($website): void
    {
        $this->website = $website;
    }

    /**
     * @return mixed
     */
    public function getMappedWidgetSettings()
    {
        return $this->mappedWidgetSettings;
    }

    /**
     * @param mixed $mappedWidgetSettings
     */
    public function setMappedWidgetSettings($mappedWidgetSettings): void
    {
        $this->mappedWidgetSettings = $mappedWidgetSettings;
        $this->view->mappedWidgetSettings = $mappedWidgetSettings;
    }

    /**
     * Convert the incoming setting to stdClass to be able to use the json_encode javascript method.
     * It's easier to user the json_encode method rather
     * to implement a custom method to be able to json the SettingVo object.
     * @param $incomingSetting
     * @return \stdClass
     */
    public function toJson($incomingSetting)
    {
        $settingKeyNameValue = new \stdClass();
        $settingKeyNameValue->key = $incomingSetting->getId();
        $settingKeyNameValue->name = $incomingSetting->getName();
        $settingKeyNameValue->value = $incomingSetting->getValue();
        return $settingKeyNameValue;
    }

    protected function convertWidgetImagePathToStaticUrl($customUrl)
    {
        $strToReplace = $this->config->general->baseUri . "backend/render/renderImage/";

        // We only want to replace links if they are internal urls, not external
        if (strpos($customUrl, $strToReplace) !== false) {
            $bucket = $this->mongo->selectGridFSBucket();

            $file = str_replace($strToReplace, "", $customUrl);

            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId((string)$file)));
            if (!is_null($bucketFile)) {
                $fileData = $bucketFile->jsonSerialize();
                $fileExt = pathinfo($fileData->filename, PATHINFO_EXTENSION);

                $customUrl = '/img/site/'
                        . $file . "." . $fileExt;

            }
        }

        return $customUrl;
    }


}
