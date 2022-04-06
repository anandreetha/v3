<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\NewsControllerValidator;
use Exception;

/**
 * Controller to news  widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class NewsController extends BaseController
{

    public function displayAction()
    {

        if ($this->request->isPost()) {
            try {
                $validation = new NewsControllerValidator();

                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $errorMessage = $message;
                        $this->logger->error("News controller: " . $errorMessage);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $orgIds = $this->request->getPost("orgIds");

                    $querylocalestring = BaseWidget::configureLocales($languages, $this->request->getLanguages());
                    $jsonUrl = "/web/open/cmsViewOrgNewsJson?orgIds=" . implode(",", $orgIds) . $querylocalestring;
                    $apiRequest = $this->client->request('GET', $jsonUrl, [
                        'base_uri' => $this->config->general->baseUrl
                    ]);
                    $output = $this->response->setContent($apiRequest->getBody()->getContents());
                    $this->view->isError = false;
                    $this->view->jsonResponse = json_decode($output->getContent())->orgNewsList;
                }
            } catch (Exception $ex) {
                $this->logger->error("NewsController " . $ex->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
                $this->view->isError = true;
            }
            $this->view->pick("news/display");
        }
    }
}
