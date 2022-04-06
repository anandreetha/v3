<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Exception;
use Multiple\Core\Utils\ConnectApiUtils;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\ChapterdetailFooterControllerValidator;
use Phalcon\Http\Response;

/**
 * Controller to display chapter detail footer widget for chapter websites.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class ChapterdetailfooterController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $validation = new ChapterdetailFooterControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Chapter detail controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $chapterId = $this->request->getPost("chapterId");
                    $regionId = $this->request->getPost("regionId");
                    $renderStaticContent = $this->request->getPost("renderStaticContent");
                    $chapterWebsiteAndGalleryArray = $this->getChapterWebsiteAndGallery(
                        $chapterId,
                        $this->request->getPost("languageLocaleCode")
                    );
                    $apiResponse = $this->retrieveChapterDetails(
                        $chapterId,
                        $this->request->getPost("languageLocaleCode")
                    );

                    // If there is no JSON response then use the mock content
                    if ($apiResponse->getContent() != null) {
                        $jsonResponse = json_decode($apiResponse->getContent(), true);

                        $tokenArray = $this->frontendTranslator->getText($this->request->getPost("languageLocaleCode"));
                        $time = BaseWidget::getHour(
                            $jsonResponse['content']['chapterDetails']['meetingTime'],
                            $tokenArray
                        );
                        $jsonResponse['content']['chapterDetails']['meetingTime'] = $time;
                        $jsonResponse['content']['chapterDetails']['meetingDay'] = $tokenArray->_('common.daynames.' .
                            strtolower($jsonResponse['content']['chapterDetails']['meetingDay'] == 'THURSDAY' ? 'THR' :
                                substr($jsonResponse['content']['chapterDetails']['meetingDay'], 0, 3)));

                        $this->view->OrgType = $jsonResponse['content']['orgType'] != null ? $jsonResponse['content']['orgType'] : "";
                        $this->view->ChapterDetails = $jsonResponse['content']['chapterDetails'];
                        $this->view->LeadershipTeam = $jsonResponse['content']['leadershipTeam'];
                        $this->view->OrgMembers = $jsonResponse['content']['orgMembers'];
                        $this->view->OrgMemberCount = $jsonResponse['content']['chapterDetails']['totalMemberCount'];
                        $this->view->OrgType = $jsonResponse['content']['orgType'];
                        $this->view->chapterId = implode(', ', $chapterId);
                        $this->view->regionId = implode(',', $regionId);
                        $this->view->renderStaticContent = $renderStaticContent;
                    }

                    $jsonUrl = "/web/open/cmsChapterViewUpcomingSpeakersJson?chapterId=" . implode(', ', $chapterId) .
                        BaseWidget::buildLocaleQueryStringParams($this->request->getPost("languageLocaleCode"));

                    $bniRequest = $this->client->request('GET', $jsonUrl, [
                        'base_uri' => $this->config->general->baseUrl,
                        'verify' => false
                    ]);
                    $output = $this->response->setContent($bniRequest->getBody()->getContents());
                    $jsonData = json_decode($output->getContent());

                    $baseUrl = $this->config->general->baseUrl;
                    $connectApiUtils = new ConnectApiUtils();
                    $doesRegionAllowOnlineApplications = $connectApiUtils->checkIfOnlineApplicationsAllowed($baseUrl, $chapterId[0]);
                    $jsonForOnlineApplicationsCheck = json_decode($doesRegionAllowOnlineApplications->getContent(), false);
                    $this->view->OrgType = $jsonResponse['content']['orgType'] != null ? $jsonResponse['content']['orgType'] : "";
                    $this->view->jsonForOnlineApplicationsCheck = $jsonForOnlineApplicationsCheck;
                    $this->view->chapterWebsiteAndGalleryArray = $chapterWebsiteAndGalleryArray;
                    $this->view->jsonData = $jsonData;
                    $this->view->memberListLink = $this->request->getPost("memberListNavName");
                }
            } catch (Exception $exception) {
                $this->view->errorMessage = $this->getErrorMessage($this->request->getPost("languageLocaleCode"));
                $this->logger->error("Unable to display footer: " . $exception->getMessage() . $exception->getTraceAsString());
            }
        }
        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->useLatestCode = $this->useLatestCodeHandler();
        $this->view->pick("chapterdetailfooter/display");
		$website_id=$this->request->getPost("website_id");
		$chapter=new ChapterdetailController();
		$this->view->OnlineApplications =$chapter->getChapterSettings($website_id,'Allow Online Applications via Website');
		$this->view->partial_domain =$this->request->getPost("partial_domain");
		$this->view->ChapterLeadershipVal =$chapter->getChapterSettings($website_id,'Control Chapter Leadership Section');
		$this->view->MemberListVal =$chapter->getChapterSettings($website_id,'Control Member List');
		
		$this->view->leadershipRole=$chapter->GroupLeadership($jsonResponse['content']['leadershipTeam']);
		$this->view->leadershipRoleOrder=array("visitorhost","membershipcommittee","support_leaders","rest_team","boardofadvisors");
	}

    /**
     * Get the chapter details
     * @param $chapterID
     * @param $pageMode
     * @param $locale
     * @return Response|\Phalcon\Http\ResponseInterface
     * @throws \Exception
     */
    public function retrieveChapterDetails($chapterID, $locale)
    {
        try {
            $apiRequest = $this->getChapterDetailsApiRequest($chapterID, $locale);

            // Pass over the response content from the request
            if ($apiRequest != null) {
                $this->response->setContent($apiRequest->getBody()->getContents());
            }
        } catch (Exception $ex) {
            $this->logger->error("Unable to retrieve chapter details: " . $ex->getMessage());

            $this->response->setContent(json_encode(array()));
            if (!empty($ex->getResponse())) {
                dump($ex->getResponse()->getReasonPhrase());
                dump($ex->getResponse()->getStatusCode());
            }
        }

        return $this->response;
    }

    /**
     * Get the chapter details from core api
     * @param $chapterID
     * @param null $locale
     * @return mixed
     */
    private function getChapterDetailsApiRequest($chapterID, $locale = null)
    {
        $chapterID = implode(', ', $chapterID);
        $localeQueryString = ($locale === null ? "" : "?locale={$locale}");
        return $this->client->request('GET', "core-api/public/chapters/{$chapterID}/all{$localeQueryString}");
    }
}
