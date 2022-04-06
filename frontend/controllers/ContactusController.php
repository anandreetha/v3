<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\ContactusControllerValidator;
use Exception;

/**
 * Controller to contact us widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class ContactusController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $validation = new ContactusControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Contact us controller: " . $message);
                    }
                    throw new Exception($message);
                } else {
                    $languages = $this->request->getPost("languages");
                    $orgIds = $this->request->getPost("orgIds");
                    $incomingWebsite = $this->request->getPost("website");
                    $website = json_decode($incomingWebsite);


                    if ($website->type_id == 1) {
                        // Call the 'appsCmsContactDetailsForNationalWebsiteJson' endpoint within BNIC,
                        // with the countryIds (and the standard locales)
                        $jsonUrl = "/web/open/appsCmsContactDetailsForNationalWebsiteJson?countryIds=" .
                            implode(",", $orgIds) . BaseWidget::configureLocales(
                                $languages,
                                $this->request->getLanguages()
                            ) . "&cmsv3=true";
                    } else {
                        $jsonUrl = "/web/open/appsCmsContactDetailsJson?regionIds=" . implode(",", $orgIds) .
                            BaseWidget::configureLocales($languages, $this->request->getLanguages()) . "&cmsv3=true";
                    }

                    $apiRequest = $this->client->request('GET', $jsonUrl, [
                        'base_uri' => $this->config->general->baseUrl
                    ]);
                    $output = $this->response->setContent($apiRequest->getBody()->getContents());

                    $this->view->jsonResponse = json_decode($output->getContent())->contactList;
                }
            } catch (Exception $ex) {
                $this->logger->error("ContactusController " . $ex->getMessage());
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }
        $this->view->pick("contactus/display");
    }
}
