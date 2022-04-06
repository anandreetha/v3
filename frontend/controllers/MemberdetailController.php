<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\MemberdetailControllerValidator;
use Exception;

/**
 * Controller to display member list widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class MemberdetailController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
				$parameters = $this->request->getPost("parameters");
				parse_str($parameters, $encmember);
				if(array_key_exists('encryptedMemberId', $encmember)):
					$encryptedMemberId = $this->securityUtils->decrypt($encmember['encryptedMemberId']);
				else: $encryptedMemberId =""; endif;
				
				if(array_key_exists('encryptedUserId', $encmember)):
					$encryptedUserId = $this->securityUtils->decrypt($encmember['encryptedUserId']);
				else: $encryptedUserId =""; endif;
				
				
				if(($encryptedMemberId=="")&&($encryptedUserId=="")){
					print"<script>document.location.href='index';</script>";
					exit;
				}
				
                $validation = new MemberdetailControllerValidator();
                $messages = $validation->validate($this->request->getPost());
				
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Member details controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $parameters = $this->request->getPost("parameters");
                    $baseUrl = $this->config->general->baseUrl;
                    $querylocalestring = BaseWidget::configureLocales($languages, $this->request->getLanguages());
					$pageMode = $this->request->getPost("pageMode");
					

                    if (strpos($parameters, "encryptedMemberId") !== false ||
                        strpos($parameters, "encryptedUserId") !== false) {
                        $apiResponse = $this->retrieveMemberDetails($parameters, $querylocalestring, $baseUrl);
                        $jsonResponse = json_decode($apiResponse->getContent(), true);
                        $mockSendmessageurl = false;
                        $this->view->jsonResponse = $jsonResponse;
                    } else {
                        if ($pageMode == "Preview") {
                            $mockSendmessageurl = true;
                            $jsonResponse = MockResponses::getMockMemberDetailsResponse();
                        }
                    }
					if(count(array_filter($jsonResponse))==0){
						print"<script>document.location.href='index';</script>";
						exit;
					}
					
                    $this->view->hideUrls = ($pageMode === "Preview" &&
                        empty(strpos($parameters, "encryptedMemberId")));
                    $this->view->jsonResponse = $jsonResponse;
                    $this->view->mockSendmessageurl = $mockSendmessageurl;

                    /**
                     * BNIDEV-5195 as part of this jira when the member is dropped we redirect to no page.
                     * This is to remove the page from Google index.
                     */
                    if (!$mockSendmessageurl && json_decode($apiResponse->getContent())->memberId == null) {
                        $this->view->disable();
                        $this->response->setStatusCode(410, 'Bad request');
                    }
                }
            } catch (Exception $exception) {
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }       
		
		$mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
		$this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
		
		$chapter=new ChapterdetailController();
		$website_id=$this->request->getPost("website_id");
		$this->view->WebsiteSetting=$chapter->getChapterSettings($website_id);	
        $this->view->pick("memberdetail/display");
    }
    public function retrieveMemberDetails($queryString, $querylocalestring, $baseUrl)
    {
        try {
            $apiRequest = $this->client->request(
                'GET',
                'web/open/appsCmsMemberDetailsForNationalWebsiteJson?' . $queryString . $querylocalestring . "&cmsv3=true",
                ['base_uri' => $baseUrl]
             );
        } catch (Exception $ex) {
            $this->logger->error("MemberdetailController " . $ex->getMessage());
            throw $ex;
        }
        // Pass over the response content from the request
        $this->response->setContent($apiRequest->getBody()->getContents());
        return $this->response;
    }
}
