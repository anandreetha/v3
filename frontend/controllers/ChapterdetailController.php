<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\ChapterdetailControllerValidator;
use Multiple\Core\Utils\ConnectApiUtils;
use Phalcon\Http\Response;
use Phalcon\Db\Column;
use Exception;


/**
 * Controller to display chapter detail widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class ChapterdetailController extends BaseController
{

    public function displayAction()
    {

        if ($this->request->isPost()) {
            try {
                $validation = new ChapterdetailControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Chapter detail controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $encryptedChapterID = $this->request->getPost("chapterId");
                    $chapterId = $this->securityUtils->decrypt($encryptedChapterID);
					if($chapterId==""){
						print"<script>document.location.href='index';</script>";
						exit;
					}
					
                    $pageMode = $this->request->getPost("pageMode");
                    $chapterWebsiteAndGalleryArray = $this->getChapterWebsiteAndGallery(
                        array($chapterId),
                        $this->request->getPost("languageLocaleCode")
                    );

                    $apiResponse = $this->retrieveChapterDetails(
                        $encryptedChapterID,
                        $pageMode,
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

                        $this->view->ChapterDetails = $jsonResponse['content']['chapterDetails'];
                        $this->view->MemberCount = $jsonResponse['content']['chapterDetails']['totalMemberCount'];
                        $this->view->LeadershipTeam = $jsonResponse['content']['leadershipTeam'];
                        $this->view->OrgMembers = $jsonResponse['content']['orgMembers'];
                        $this->view->OrgType = $jsonResponse['content']['orgType'] != null ? $jsonResponse['content']['orgType'] : "";
                    }

                    // info required to check whether BR 37 applies
                    $baseUrl = $this->config->general->baseUrl;
                    $connectApiUtils = new ConnectApiUtils();

                    $doesRegionAllowOnlineApplications = $connectApiUtils->checkIfOnlineApplicationsAllowed($baseUrl, $chapterId);

                    $jsonForOnlineApplicationsCheck = json_decode($doesRegionAllowOnlineApplications->getContent(), false);
                    $this->view->OrgType = $jsonResponse['content']['orgType'] != null ? $jsonResponse['content']['orgType'] : "";
                    $this->view->jsonForOnlineApplicationsCheck = $jsonForOnlineApplicationsCheck;
                    $this->view->useLatestCode = $this->useLatestCodeHandler();
                    $this->view->chapterWebsiteAndGalleryArray = $chapterWebsiteAndGalleryArray;
                }
            } catch (Exception $exception) {
                $this->view->errorMessage = $this->getErrorMessage($this->request->getPost("languageLocaleCode"));
            }
        }

        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->hideUrls = ($pageMode === "Preview" && empty($encryptedChapterID));
        $this->view->pick("chapterdetail/display");
        $this->view->chapterId = $chapterId;
		$website_id=$this->request->getPost("website_id");
		$this->view->WebsiteSetting =$this->getChapterSettings($website_id);
		$this->view->OnlineApplications =$this->getChapterSettings($website_id,'Allow Online Applications via Website');
		$this->view->ChapterLeadershipVal =$this->getChapterSettings($website_id,'Control Chapter Leadership Section');
		$this->view->MemberListVal =$this->getChapterSettings($website_id,'Control Member List');
		$this->view->jsonResponse=$jsonResponse;
		$this->view->leadershipRole=$this->GroupLeadership($jsonResponse['content']['leadershipTeam']);
		$this->view->leadershipRoleOrder=array("visitorhost","membershipcommittee","support_leaders","rest_team","boardofadvisors");
	}
	public function GroupLeadership($leadership)
	{
		$leadarr=array();
		$presidentarr=array("role.president","role.vicepresident","role.secretarytreasurer");
		$supportarr=array("role.educationcoordinator","role.eventscoordinator","role.mentoringcoordinator","role.growthcoordinator","role.chapterwebmaster");
		
		foreach($leadership as $ls):
			if(in_array($ls['roleToken'], $presidentarr)):
				$leadarr["president"][]=$ls;
			elseif($ls['roleToken']=="role.visitorhost"):
				$leadarr["visitorhost"][]=$ls;
			elseif($ls['roleToken']=="role.membershipcommittee"):
				$leadarr["membershipcommittee"][]=$ls;	
			elseif(in_array($ls['roleToken'], $supportarr)):
				$leadarr["support_leaders"][]=$ls;
			elseif($ls['roleToken']=="role.intlboardofadvisors"):
				$leadarr["boardofadvisors"][]=$ls;				
			else:
				$leadarr["rest_team"][]=$ls;	
			endif;
		endforeach;
		return $leadarr;
	}
    /**
     * Get the chapter details
     * @param $encryptedChapterID
     * @param $pageMode
     * @param $locale
     * @return Response|\Phalcon\Http\ResponseInterface
     * @throws \Exception
     */
	public function getChapterSettings($websiteId,$sname='')
	{
		if($sname==""):
			$sname='Allow web visitors to email members';
		endif;
		
		if($websiteId):
			$sql="SELECT value as websitesetting FROM website_settings ws, setting s WHERE s.id=ws.settings_id AND s.name=:sname AND ws.website_id =:websiteid";
			$getList = $this->db->prepare($sql);
			$data = $this->db->executePrepared(
				$getList,
				[
					"sname" => $sname,
					"websiteid" => $websiteId
				],
				[
					"sname" => Column::BIND_PARAM_STR,
					"websiteid" => Column::BIND_PARAM_INT,
				]
			);
			
			$result    = $data->fetchAll();
			$rs=$result[0];
			
			return $rs['websitesetting'];
		else:
			return 'on';
		endif;
	} 
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
