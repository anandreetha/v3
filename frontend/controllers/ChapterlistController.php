<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\ChapterlistControllerValidator;
use Exception;

/**
 * Controller to display chapter list widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class ChapterlistController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $validation = new ChapterlistControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Chapter list controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $pageMode = $this->request->getPost("pageMode");
                    $parameters = $this->request->getPost("parameters");
                    $cmsv3 = $this->request->getPost("cmsv3");
                    $baseUrl = $this->config->general->baseUrl;
                    $querylocalestring = BaseWidget::configureLocales($languages, $this->request->getLanguages());
                    $opening = false;

                    if ($pageMode === "Preview" &&
                        strpos($parameters, 'countryIds') == false &&
                        strpos($parameters, 'countryIds') !== 0 &&
                        strpos($parameters, 'regionIds') == false &&
                        strpos($parameters, 'regionIds') !== 0) {
                        $this->view->jsonResponse = MockResponses::getMockDatatablesRow();

                        // Double check we're not trying to view the chapter list preview or find an opening preview
                        // If chapterlist === false in the params, then it's true that we have $opening
                        $opening = (strpos($parameters, 'chapterlist') === false ? true : false);
                    } else {
                        if (strpos($parameters, 'secondaryCategory') !== false ||
                            strpos($parameters, 'secondaryCategoryIds') !== false) {
                            $opening = true;
                            $jsonUrl = "web/open/appsCmsChapterListJson?" .
                                "chapterName=&chapterCity=&chapterArea=&chapterMeetingDay=&chapterMeetingTime=&" .
                                $parameters . "&cmsv3=" . $cmsv3 . $querylocalestring;
                        } elseif (strpos($parameters, 'countryIds') !== false) {
                            $jsonUrl = "web/open/appsCmsCountryChapterListJson?" .
                                $parameters . "&cmsv3=" . $cmsv3 . $querylocalestring;
                        } else {
                            $jsonUrl = "web/open/appsCmsChapterListJson2?" .
                                $parameters . "&cmsv3=" . $cmsv3 . $querylocalestring;
                        }

                        $apiRequest = $this->client->request('GET', $jsonUrl, [
                            'base_uri' => $baseUrl
                        ]);
                        $output = $this->response->setContent($apiRequest->getBody()->getContents());
                        $jsonData = json_decode($output->getContent());

                        $this->view->jsonResponse = $jsonData;
                    }
                }
            } catch (Exception $exception) {
                $this->logger->error("ChapterlistController " . $exception->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }
        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        if ($opening == true) {
            $this->view->pick("findopeningresults/display");
        } else {
            $this->view->pick("chapterlist/display");
        }
    }
}
