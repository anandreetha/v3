<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Exception;
use Multiple\Core\Utils\ConnectApiUtils;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\CoregroupControllerValidator;

/**
 * Controller to display core group widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class CoregroupController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $languages = $this->request->getPost("languages");
                $orgIds = $this->request->getPost("orgIds");
                $validation = new CoregroupControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Core group controller: " . $message);
                    }
                    throw  new Exception($message);
                } else {
                    // This may need changing to pass through preview mode or not, but do this for now
                    if (empty($orgIds)) {
                        $this->view->jsonResponse = MockResponses::getMockCoregroupDetailResponse();
                    } else {
                        // Php or phalcon decodes the encoded url, so we have to unencrypt and then encrypt the data
                        // again
                        $decryptedOrgId = $this->securityUtils->decrypt($orgIds);
                        $encodedUrlOrgId = $this->securityUtils->encryptUrlEncoded($decryptedOrgId);

                        $connectApiUtils = new ConnectApiUtils();

                        // Call the 'appsCmsCoreGroupDetailsNewJson' endpoint within BNIC, with encrypted coreGroupID
                        // (and the standard locales)
                        $jsonUrl = "/web/open/appsCmsCoreGroupDetailsNewJson?encryptedCoreGroupId=" . $encodedUrlOrgId .
                            BaseWidget::configureLocales($languages, $this->request->getLanguages()) . "&cmsv3=true";
                        $apiRequest = $this->client->request('GET', $jsonUrl, [
                            'base_uri' => $this->config->general->baseUrl
                        ]);
                        $output = $this->response->setContent($apiRequest->getBody()->getContents());

                        $this->view->jsonResponse = json_decode($output->getContent());

                        // retrieve chapter type
                        $this->callConnectToRetrieveOrgType($this->request->getPost("orgIds"));

                        // info required to check whether BR 37 applies
                        $baseUrl = $this->config->general->baseUrl;

                        $doesRegionAllowOnlineApplications = $connectApiUtils->checkIfOnlineApplicationsAllowed($baseUrl, $decryptedOrgId);

                        $jsonForOnlineApplicationsCheck = json_decode($doesRegionAllowOnlineApplications->getContent(), false);
                        $this->view->jsonForOnlineApplicationsCheck = $jsonForOnlineApplicationsCheck;

                        /**
                         * BNIDEV-4938  we need to remove the page from Google Index we check if the return
                         * Core group id is null which means BNIC has returned a dropped chapter. determine
                         * if the core group chapter has been dropped.
                         * BNIC using struts and it is difficult to
                         *
                         * return json response with the incoming chapter id
                         */
                        if ($this->view->jsonResponse->coreGroupId == null) {
                            $this->view->disable();
                            $this->response->setStatusCode(400, 'Bad request');
                            $coreGroupDetail = new \stdClass();
                            $coreGroupDetail->chapterId = $encodedUrlOrgId;
                            $this->response->setContentType('application/json', 'UTF-8');
                            $this->response->setContent(json_encode($coreGroupDetail));
                            return $this->response;
                        }
                    }

                }
            } catch (Exception $exception) {
                $this->logger->error("CoregroupController " . $exception->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }
        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->useLatestCode = $this->useLatestCodeHandler();
        $this->view->pick("coregroup/display");
		$website_id=$this->request->getPost("website_id");
		$chapter=new ChapterdetailController();
		$this->view->OnlineApplications =$chapter->getChapterSettings($website_id,'Allow Online Applications via Website');
		$this->view->ChapterLeadershipVal =$chapter->getChapterSettings($website_id,'Control Chapter Leadership Section');
    }

    // Retrieves OrgType data from Connect's API
    // Can be further modified to retrieve other data
    function callConnectToRetrieveOrgType($encodedCoreGroupId)
    {
        $pageMode = $this->request->getPost("pageMode");
        $apiResponse = $this->retrieveChapterDetails(
            $encodedCoreGroupId,
            $pageMode,
            $this->request->getPost("languageLocaleCode")
        );

        if ($apiResponse->getContent() != null) {
            $jsonResponse = json_decode($apiResponse->getContent(), true);
        }
        $this->view->OrgType = $jsonResponse['content']['orgType'] != null ? $jsonResponse['content']['orgType'] : "";
    }

    /**
     * Get the chapter details
     * @param $encryptedChapterID
     * @param $pageMode
     * @param $locale
     * @return Response|\Phalcon\Http\ResponseInterface
     * @throws \Exception
     */
    public function retrieveChapterDetails($encryptedChapterID, $pageMode, $locale)
    {
        try {
            $apiRequest = null;
            $connectApiUtils = new ConnectApiUtils();

            // Initiate request
            switch ($pageMode) {
                case 'Preview':
                    if (empty($encryptedChapterID)) {
                        $this->view = MockResponses::setupMockChapterDetailData($this->view);
                    } else {
                        $apiRequest = $connectApiUtils->getChapterDetailsApiRequest($encryptedChapterID, $locale);
                    }
                    break;
                case 'Live_Site':
                    if (empty($encryptedChapterID)) {
                        throw new \Exception("Chapter_id is blank");
                    }
                    $apiRequest = $connectApiUtils->getChapterDetailsApiRequest($encryptedChapterID, $locale);
                    break;
                default:
                    $apiRequest = $connectApiUtils->getChapterDetailsApiRequest($encryptedChapterID, $locale);
                    break;
            }

            // Pass over the response content from the request
            if ($apiRequest != null) {
                $this->response->setContent($apiRequest->getBody()->getContents());
            }
        } catch (Exception $ex) {
            if (!empty($ex->getResponse())) {
                $this->logger->error("Chapter detail controller(core-api): " . $ex->getMessage() . $ex->getResponse()->getStatusCode() .
                    ":" . $ex->getResponse()->getReasonPhrase());
            }

            /**
             * Due to BNIDEV-4938 jira we had to remove the deleted chapter page from Google index
             * the BNI api will return a 400 status code and we move it forward so the ajax response
             * fail function pick it up and redirect using 301 status code to a nopage. For other
             * cases and error codes we gonna display the normal error message.
             */
            if ($ex->getCode() == 400) {
                $this->response->setStatusCode(400, 'Bad request');
            }
            $this->view->errorMessage = $this->getErrorMessage($locale);
        }

        return $this->response;
    }
}