<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Misc\SettingsMapper;
use Multiple\Core\Misc\SettingVo;
use Multiple\Core\Models\Language;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteOrg;
use Phalcon\Mvc\Controller;

/**
 * Base class to share functions between frontend controllers.
 * Class BaseController
 * @package Multiple\Frontend\Controllers
 */
class BaseController extends Controller
{

    /**
     * Private method to find if a chapter has a website and if that website has a gallery of the active language.
     * If yes then return an array of those values to be used in display.phtml
     * @param $decryptChapterId
     * @return array
     */
    protected function getChapterWebsiteAndGallery($decryptChapterId, $code)
    {
        $websiteOrg = WebsiteOrg::findFirst(
            [
                'org_id = :org_id:',
                'bind' => [
                    'org_id' => $decryptChapterId[0]
                ],
            ]
        );
        $chapterWebsiteAndGalleryArray = [];
        if ($websiteOrg != false) {
            $websiteId = $websiteOrg->getWebsiteId();
            $websiteChapter = Website::findFirst($websiteId);
            $galleryTemplate = "gallery";

            if ($websiteChapter->getLastPublished() != null) {
                $language = Language::findFirst(
                    [
                        'locale_code = :locale_code:',
                        'bind' => [
                            'locale_code' => $code
                        ],
                    ]
                );
                // current language
                foreach ($websiteChapter->getWebsiteLanguage() as $lang) {
                    if ($lang->language_id == $language->getId()) {
                        array_push($chapterWebsiteAndGalleryArray, $websiteChapter);
                        $galleryPage = $websiteChapter->getPage(
                            [
                                'template = :template:',
                                'bind' => [
                                    'template' => $galleryTemplate
                                ],
                            ]
                        )->getFirst();

                        if ($galleryPage != false) {
                            $pageContent = $galleryPage->pageContent;
                            $pageContent = $pageContent[0];

                            $setting = $pageContent->getPageContentSettings(
                                [
                                    'setting_id = :setting_id: AND value != :value:',
                                    'bind' => [
                                        'setting_id' => "10",
                                        'value' => "0"
                                    ],
                                ]
                            )->getFirst();
                            if ($setting != false) {
                                array_push($chapterWebsiteAndGalleryArray, $galleryPage->template);
                                array_push($chapterWebsiteAndGalleryArray, $code);
                            }
                        }
                    }
                }
            }
        }
        return $chapterWebsiteAndGalleryArray;
    }

    protected function useLatestCodeHandler()
    {
        $url = $this->request->getHTTPReferer();

        $result = parse_url($url);

        // In preview mode we need to ensure latest code is used
        if(strpos($result["path"],"/preview/") !== false){
            return true;
        }

        if (!empty($result) && $result != false) {
            $matchingWebsite = Website::findFirst(array(
                'domain = :domain: or clean_domain = :domain:',
                'bind' => [
                    'domain' => $result['host'],
                ],
                'columns' => 'last_published'
            ));

        }

        if (isset($matchingWebsite)) {
            $publishedDate = date($matchingWebsite->last_published);
            $cutOffDate = date($this->config->changeVersionManagement->cutOffDate);

            // Use latest changes
            if ($publishedDate >= $cutOffDate) {
                return true;
            }
        }

        // Use old changes
        return false;

    }

    /**
     * Convert back the incoming JSON array of object back to SettingMapper object.
     * @param $incomingArray
     * @return SettingsMapper
     */
    public function convertToSettingMapper($incomingArray)
    {

        $settingsMapper = new SettingsMapper();
        foreach ($incomingArray as $setting) {
            $settingKeyNameValue = new SettingVo();
            $settingKeyNameValue->setId($setting->key);
            $settingKeyNameValue->setValue($setting->value);
            $settingKeyNameValue->setName($setting->name);
            $settingsMapper->append($settingKeyNameValue);
        }
        return $settingsMapper;
    }


    /**
     * When there is a validation error with frontend controllers or BNIC or BNI apis are down this error message
     * is get displayed.
     * @param $locateCode
     * @return mixed
     */
    public function getErrorMessage($locateCode)
    {
        $tokenArray= $this->frontendTranslator->getText($locateCode);
        return $tokenArray->_("cms.v3.admin.widget.errorMessage");
    }
	
}
