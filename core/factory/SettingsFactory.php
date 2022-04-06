<?php

namespace Multiple\Core\Factory;

use Multiple\Core\Models\PageContentSettings;
use Multiple\Core\Models\Setting;
use Phalcon\Mvc\User\Component;

class SettingsFactory extends Component
{

    public function getPageContentSetting($token, $currentSettings, $pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        // Get all available settings
        $allAvailableSettings = Setting::findFirst(
            [
                'type = :type: and token = :token:',
                'bind' => [
                    'type' => 'PageContent',
                    'token' => $token,
                ],
            ]
        );

        if (!$allAvailableSettings) {
            return false;
        } else {


            foreach ($currentSettings as $currentSetting) {
                if($currentSetting->setting_id == $allAvailableSettings->getId()){
                    return $currentSetting;
                }
            }

            // An existing instance didn't exist so returning a new instance with some preset initializations
            $pageSettings = new PageContentSettings();
            $pageSettings->setSettingId($allAvailableSettings->getId());
            $pageSettings->setPageContentId($pageContentId);

            return $pageSettings;

        }

    }

}