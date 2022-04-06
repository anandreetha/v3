<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\ChapterformingControllerValidator;
use Exception;

/**
 * Controller to display chapter forming widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class ChapterformingController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $validation = new ChapterformingControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Chapter forming controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $regionIds = $this->request->getPost("regionIds");


                    $queryString = BaseWidget::configureLocales($languages, $this->request->getLanguages());
                    $jsonUrl = "/web/open/appsCmsCoreGroupListJson?regionIds=" .
                        implode(",", $regionIds) . $queryString . "&cmsv3=true";

                    $apiRequest = $this->client->request('GET', $jsonUrl, [
                        'base_uri' => $this->config->general->baseUrl
                    ]);

                    $output = $this->response->setContent($apiRequest->getBody()->getContents());
                    $jsonData = json_decode($output->getContent());


                    $this->view->response = $jsonData;
                    $this->view->languages = $languages;
                }
            } catch (Exception $exception) {
                $this->logger->error("ChapterformingController " . $exception->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }
        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->pick("chapterforming/display");
    }
}
