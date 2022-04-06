<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Exception;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\MemberlistControllerValidator;
use Phalcon\Http\Response;
use Phalcon\Db\Column;

/**
 * Controller to display member detail widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class MemberlistController extends BaseController
{

    const LIMIT = 250;
    public $errorMessage = "";

    public function displayAction()
    {

        if ($this->request->isPost()) {
            $chooseChapterWebsiteDisplay = false;
            try {
                $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
                $mappedWidgetSettings= $this->convertToSettingMapper($mappedWidgetSettingsJson);

                $validation = new MemberlistControllerValidator();
                $messages = $validation->validate($this->request->getPost());

                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Member list controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $parameters = $this->request->getPost("parameters");
                    $cmsv3 = $this->request->getPost("cmsv3");
                    $pageMode = $this->request->getPost("pageMode");
                    $baseUrl = $this->config->general->baseUrl;
                    $querylocalestring = BaseWidget::configureLocales($languages, $this->request->getLanguages());

                    /**
                     * As per BNIDEV-5112 requested for chapter website
                     * we need to display a different table than normal member list table. This table need to look like
                     * the member list inside chapter detail widget.
                     * The chapter detail widget call BNI api to get the relevant data. In this case here we check if
                     * a specific url parameter exists to identify if the the request coming from a chapter website.
                     * If yes make a call to BNI api to get the data.
                     * Also it choose a different display than normal member list display.phtml
                     */
                    $isChapterWebsite = strpos($parameters, 'chapterWebsite') !== false;
                    $this->logger->debug(
                        'Chapter Website url parameter is: ' . $isChapterWebsite
                    );
                    if (isset($isChapterWebsite) && $isChapterWebsite == true) {
                        $chooseChapterWebsiteDisplay = true;

                        preg_match('~chapterName=(.*?)&regionIds=~', $parameters, $chapterIdTable);
                        $this->logger->debug(
                            'Chapter id is: ' . $chapterIdTable[1]
                        );
                        $apiResponse = $this->getChapterWebsiteMemberList($chapterIdTable[1],
                            $this->request->getPost("languages")["activeLanguage"]["localeCode"],
                            $this->request->getPost("language")["activeLanguages"]);

                        // If there is no JSON response then use the mock content
                        if ($apiResponse->getContent() != null) {
                            $jsonResponse = json_decode($apiResponse->getContent(), true);

                            $this->view->OrgMembers = $jsonResponse['content']['orgMembers'];
                            $this->view->OrgType = $jsonResponse['content']['orgType'];
                        }
                    } else {
                        if ($pageMode === "Preview" &&
                            strpos($parameters, 'countryIds') == false &&
                            strpos($parameters, 'countryIds') !== 0 &&
                            strpos($parameters, 'regionIds') == false &&
                            strpos($parameters, 'regionIds') !== 0) {
                            $this->view->jsonResponse = MockResponses::getMockDatatablesRow();
                            $this->view->widgetSettingObj = $widgetSettings;
                        } else {
                            if (strpos($parameters, 'countryIds') !== false) {
                                /* Query String is: ?countryIds=&regionId=&chapterName=&chapterCity=&chapterArea
                                =&memberCategory=&memberKeywords=&memberFirstName=&memberLastName=&memberCompany= */
                                $jsonUrl = "web/open/appsCmsCountryMembersListJson?"
                                    . $parameters . "&cmsv3=" . $cmsv3 . $querylocalestring;
                            } else {
                                $jsonUrl = "web/open/appsCmsMembersListJson2?"
                                    . $parameters . "&cmsv3=" . $cmsv3 . $querylocalestring;
                            }

                            $apiRequest = $this->client->request('GET', $jsonUrl, [
                                'base_uri' => $baseUrl
                            ]);
                            $output = $this->response->setContent($apiRequest->getBody()->getContents());
                            $jsonData = json_decode($output->getContent());
                            if (count($jsonData->aaData) > self::LIMIT) {
                                $this->errorMessage = $mappedWidgetSettings->
                                getValueByName("Your search generated more than 250 results. Please refine your search to see fewer results.");
                                $jsonData->aaData =
                                    array_slice($jsonData->aaData, 0, self::LIMIT);
                            }
                            $this->view->jsonResponse = $jsonData;
                        }
                    }

                    $this->view->errorMessage = $this->errorMessage;
                }
            } catch (Exception $ex) {
                $this->logger->error("MemberlistController " . $ex->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }

            $this->view->mappedWidgetSettings = $mappedWidgetSettings;

            $this->view->exceptionTypeError =$this->
                getErrorMessage($this->request->getLanguages()[0]["language"]);


            if ($chooseChapterWebsiteDisplay == true) {
				$chapter=new ChapterdetailController();
				$website_id=$this->request->getPost("website_id");
				
				$memberlist=$chapter->getChapterSettings($website_id,"Control Member List");
				if($memberlist=="off"):
					$activeLanguageId=$languages['activeLanguage']['id'];
					$mem_menu=$this->CheckChapterMemberPage($website_id,$activeLanguageId);
					if($mem_menu==0):
						print"<script>document.location.href='index';</script>";
						exit;
					endif;
				endif;
				
                $this->view->pick("chapterWebsiteMemberlist/display");
				
				$this->view->WebsiteSetting=$chapter->getChapterSettings($website_id);
            } else {
                $this->view->pick("memberlist/display");
            }
        }
    }
	private function CheckChapterMemberPage($website_id,$activeLanguageId)
	{
		$query="SELECT ps.value FROM page p, page_content pc, page_content_settings ps
				WHERE p.id=pc.page_id AND pc.id=ps.page_content_id
				AND p.template='find-a-member-list' AND ps.setting_id=10 AND p.website_id=:websiteid AND pc.language_id=:LanguageId";
	
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"websiteid" => $website_id,
				"LanguageId" => $activeLanguageId
			],
			[
				"websiteid" => Column::BIND_PARAM_INT,
				"LanguageId" => Column::BIND_PARAM_INT,
			]
		);
		
		$result    = $data->fetchAll();
		$rs=$result[0];
	
		return $rs['value'];
	}

    /**
     * If a request come from a chapter website call BNI api to get the member list data.
     * @param $chapterId
     * @param $localeString
     * @return Response|\Phalcon\Http\ResponseInterface
     */
    private function getChapterWebsiteMemberList($chapterId, $localeString,$locale)
    {
        try {
            $this->logger->debug(
                'getChapterWebsiteMemberList: chapter id is: ' . $chapterId . " and locale is " . $localeString
            );

            $apiRequest = null;
            $apiRequest = $this->getMemberlistApiDetails($chapterId, $localeString);

            // Pass over the response content from the request
            if ($apiRequest != null) {
                $this->response->setContent($apiRequest->getBody()->getContents());
            }
        } catch (Exception $ex) {
            if (!empty($ex->getResponse())) {
                $this->logger->error("Member detail controller(core-api): " . $ex->getResponse()->getStatusCode() .
                    ":" . $ex->getResponse()->getReasonPhrase());
            }
            $this->errorMessage = $this->getErrorMessage($locale);
        }

        $this->logger->debug(
            'getChapterWebsiteMemberList: Response is: ' . $this->response->getContent()
        );

        return $this->response;
    }

    /**
     * Get members details from core api
     * @param $chapterID
     * @param null $locale
     * @return mixed
     */
    private function getMemberlistApiDetails($chapterID, $locale = null)
    {
        $this->logger->debug(
            'getMemberlistApiDetails: Chapter id  is: ' . $chapterID . " and locale is: " . $locale
        );
        $localeQueryString = ($locale === null ? "" : "?locale={$locale}");
        return $this->client->request('GET', "core-api/public/chapters/{$chapterID}/members{$localeQueryString}");
    }
}
