<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Models\Website;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\EventdetailControllerValidator;
use Exception;

/**
 * Controller to display event detail form.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class EventdetailController extends BaseController
{
    const LIVE_SITE = "Live_Site";

    const PAGE_PREVIEW = "Preview";

    public function displayAction()
    {
        $pageMode = $this->request->getPost("pageMode");
        try {
            $validation = new EventdetailControllerValidator();
            $messages = $validation->validate($this->request->getPost());
            $errorMessage = "";
            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->logger->error("Event details controller: " . $message);
                }
                throw new Exception($message);
            } else {
                $lanauges = $this->request->getPost("languages");

                $encyptedEventId = urlencode($this->request->getPost("eventId"));
				$decryptEventId = $this->securityUtils->decrypt($this->request->getPost("eventId"));
				if($decryptEventId==""){
					$this->redirectIndex();
				}
                $output = $this->retrieveEventDetails($lanauges, $pageMode, $encyptedEventId);
                $jsonContent = json_decode($output->getContent());
				if(empty($jsonContent)){
					$this->redirectIndex();
				}

                $this->view->contactDetails = $this->splitContactDetails($jsonContent);
                $this->view->jsonResponse = $jsonContent;
            }
        } catch (\Exception $ex) {
            $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
        }

        $this->view->hideUrls = ($pageMode == self::PAGE_PREVIEW && $this->request->getPost("eventId") == null);

        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings = $this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->pick("eventdetail/display");
    }
	public function redirectIndex()
	{	
		print"<script>document.location.href='index';</script>";
		exit;
	}

    /**
     * Method to retrive method details. It check the page mode and based on the input it use real json data or mock one.
     * @param $languages
     * @return mixed
     * @throws \Exception
     */
    public function retrieveEventDetails($languages, $pageMode, $eventId)
    {

        $querylocalestring = BaseWidget::configureLocales($languages, $this->request->getLanguages());
        if ($pageMode == self::PAGE_PREVIEW) {
            if ($eventId == null) {
                return $this->response->setContent(MockResponses::getMockEventDetailResponse());
            }
            $jsonUrl = $this->createJsonUrl($eventId, $querylocalestring);
        } else if ($pageMode == self::LIVE_SITE) {
            if ($eventId == null) {
                throw new \Exception("Event id is empty");
            }
            $jsonUrl = $this->createJsonUrl($eventId, $querylocalestring);
        } else {
            $jsonUrl = $this->createJsonUrl($eventId, $querylocalestring);
        }

        try {
            $apiRequest = $this->client->request('GET', $jsonUrl, [
                'base_uri' => $this->config->general->baseUrl
            ]);
        } catch (Exception $ex) {
            $this->logger->error("EventdetailController " . $ex->getMessage());
            throw $ex;
        }
        $this->response->setContent($apiRequest->getBody()->getContents());
        $this->view->useLatestCode = $this->useLatestCodeHandler();
        return $this->response;
    }

    /**
     * @param $eventId
     * @param $querylocalestring
     * @return stringBNIDEV-7936
     */
    private function createJsonUrl($eventId, $querylocalestring)
    {
        return "web/open/cmsViewEventDetailsJson?encryptedEventId=" . $eventId . $querylocalestring
            . "&cmsv3=true";
    }

    /**
     * As BNIC send back a paragraph with the email link this method split the incoming email link icon
     * in order to separate it from the rest of the text and display it in different position.
     * The split is done only if email link is present
     */
    private function splitContactDetails($jsonContent)
    {
        if (strpos($jsonContent, "<a href='sendmessage") !== false) {
            $emailUrlIcon = explode('*', $jsonContent->contactPerson);
        }
        return $emailUrlIcon;
    }

}
