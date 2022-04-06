<?php

namespace Multiple\Locale;

use Exception;
use Phalcon\Mvc\User\Component;
use Phalcon\Translate\Adapter\NativeArray;

class LocaleLoader extends Component
{
    public function getLocaleTranslations()
    {
        // Check to see if the language param has been passed across in the url otherwise fallback to the browsers
        $language = $this->getUserLocale() != null ? $this->getUserLocale() : $this->request->getBestLanguage();

        $userLocale = new UserLocale($language);

        // Return a translation object
        return new NativeArray(
            [
                'content' => $userLocale->getMessages(),
            ]
        );
    }

    private function getUserLocale()
    {
		if(isset($_POST['languageLocaleCode'])): return $_POST['languageLocaleCode']; endif;
		if(isset($_POST['languages']['activeLanguage']['localeCode'])): 
			return $_POST['languages']['activeLanguage']['localeCode'];
		endif;
		
        if ($this->session->get("session-token") == null && $this->config->general->devModeEnabled) {
            return "en_US";
        }

        if($this->session->get('userId') == null) {
                return "en_US";
        }

        // Call http://[domain]:8050/internal/profile/[userId]/locale to get the user's locale
        $url = 'internal/profile/' . $this->session->get('userId') . '/locale';

        try {
            $overallRequest = $this->client->request('GET', $url, [
                'base_uri' => $this->config->bniApi->internalCoreApiUrl
            ]);

            $responseBody = json_decode($overallRequest->getBody()->getContents());
            return $responseBody->content->locale;
        } catch (Exception $ex) {
            $this->logger->error("LocaleLoader: " . $ex->getMessage());
            return "en_US";
        }
    }

    public function getText($locale)
    {

        $userLocale = new UserLocale($locale);

        // Return a translation object
        return new NativeArray(
            [
                'content' => $userLocale->getMessages(),
            ]
        );
    }
}
