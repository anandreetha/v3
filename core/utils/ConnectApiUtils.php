<?php


namespace Multiple\Core\Utils;

/**
 * Class ConnectApiUtils
 * @package Multiple\Core\Utils
 * Collection of frequent http requsts to BNI Connect api endpoints
 */

use Phalcon\Mvc\User\Component;

class ConnectApiUtils extends Component
{
    /**
     * Check whether the region allows online applications as per BR 37
     * @param $baseUrl
     * @param $chapterId
     * @return mixed
     */

    public function checkIfOnlineApplicationsAllowed($baseUrl, $chapterId)
    {
        try {
            $apiRequest = $this->client->request(
                'GET',
                'web/open/appsCmsCanRegionCreateOnlineAppsJson?chapterId=' . $chapterId,
                ['base_uri' => $baseUrl,
                    'verify' => false]
            );

            // Pass over the response content from the request
            $this->response->setContent($apiRequest->getBody()->getContents());
            return $this->response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage() . "\n"
                . $this->client->defaults);
        }
    }

    /**
     * Get the chapter details from core api
     * @param $encryptedChapterID
     * @param null $locale
     * @return mixed
     */

    public function getChapterDetailsApiRequest($encryptedChapterID, $locale = null)
    {
        $localeQueryString = ($locale === null ? "" : "?locale={$locale}");
        $chapterID = $this->securityUtils->decrypt($encryptedChapterID);
        return $this->client->request('GET', "core-api/public/chapters/{$chapterID}/all{$localeQueryString}");
    }
}