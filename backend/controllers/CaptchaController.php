<?php

namespace Multiple\Backend\Controllers;

use Multiple\Core\Models\Language;
use Multiple\Core\Models\PageContent;
use Phalcon\Mvc\Controller;

//use CmsV3\Common\Models\Language;
//use CmsV3\Common\Models\Website;
//use CmsV3\Common\Models\Navigation;
//use CmsV3\Common\Models\Template;


use Multiple\Core\Models\Website;
use Phalcon\Tag;
use Phalcon\Mvc\View;


class CaptchaController extends Controller
{
    public function initialize()
    {
        $this->view->displaySideMenu = false;
    }


    public function checkAction()
    {

        // Return json content
        $this->response->setContentType('application/json', 'UTF-8');

        $gRecaptchaRresponse = $this->request->get("g-recaptcha-response");
        if (isset($gRecaptchaRresponse) && !empty($gRecaptchaRresponse)) {


            $responseData = $this->googleCheck($gRecaptchaRresponse);
            if ($responseData->hostname != $_SERVER["HTTP_HOST"] AND $responseData->hostname != "www.bniconnectglobal.com") {

                // Pass over the response content from the request
                $this->response->setContent('{"messageSendSatus":false}');
            }
            if ($responseData->success) {

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $this->config->general->baseUrl . "/web/open/newsletterSignup");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST);
                $output = curl_exec($ch);
                curl_close($ch);

                $this->response->setContent($output);

            } else {

                $this->response->setContent('{"messageSendSatus":false}');
            }

        } else {

            $this->response->setContent('{"messageSendSatus":false}');
        }

        return $this->response;
    }


    public function handleRecaptchaAction()
    {
        // Initialise the response
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $responseArray = array(
            "eventRegStatus" => false
        );

        $gRecaptchaRresponse = $this->request->get("g-recaptcha-response");
        $emailAddress = $this->request->get("emailAddress", 'email');
        $eventId = $this->request->get("eventId", 'int');


        // Only if the post data is valid and eligible then continue
        if (!empty($emailAddress) && !empty($eventId)) {

            if (isset($gRecaptchaRresponse) && !empty($gRecaptchaRresponse)) {

                $captchaValidationResponse = $this->googleCheck($gRecaptchaRresponse);

                if ($captchaValidationResponse->success) {

                    // Call api endpoint here
                    $url = 'internal/event/' . $eventId . '/register';
                    $jsonPayload = array(
                        'emailAddress' => $emailAddress
                    );

                    try {
                        $overallRequest = $this->client->request('POST', $url, [
                            'base_uri' => $this->config->bniApi->internalCoreApiUrl,
                            'json' => $jsonPayload
                        ]);

                        $jsonBody = json_decode($overallRequest->getBody()->getContents())->content;

                        switch ($jsonBody->redirectType) {
                            case "SHOW_MESSAGE":
                                $responseArray["eventRegStatus"] = true;
                                break;
                            case "REDIRECT_AUTOMATIC":
                                $responseArray["eventRegStatus"] = true;
                                if (!is_null($jsonBody->url)) {
                                    $responseArray["redirectURL"] = $jsonBody->url;
                                }
                                break;
                        }

                    } catch (Exception $ex) {
                        $this->logger->error("There was a problem trying to register to event: " + $ex->getMessage());
                    }

                }
            }
        }

        $this->response->setContent(json_encode($responseArray));

        return $this->response;
    }

    public function handleApplicationRegistrationRecaptchaAction()
    {
        // Initialise the response
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $responseArray = array(
            "chapterRegStatus" => false
        );

        $gRecaptchaRresponse = $this->request->get("g-recaptcha-response");
        $emailAddress = $this->request->get("emailAddress", 'email');
        $chapterId = $this->request->get("chapterId", 'int');


        // Only if the post data is valid and eligible then continue
        if (!empty($emailAddress) && !empty($chapterId)) {

            if (isset($gRecaptchaRresponse) && !empty($gRecaptchaRresponse)) {

                $captchaValidationResponse = $this->googleCheck($gRecaptchaRresponse);

                if ($captchaValidationResponse->success) {

                    // Call api endpoint here
                    $url = 'internal/applicationregistration/register';
                    $jsonPayload = array(
                        'emailAddress' => $emailAddress,
                        'chapterId' => $chapterId
                    );

                    try {
                        $overallRequest = $this->client->request('POST', $url, [
                            'base_uri' => $this->config->bniApi->internalCoreApiUrl,
                            'json' => $jsonPayload
                        ]);

                        $jsonBody = json_decode($overallRequest->getBody()->getContents())->content;

                        switch ($jsonBody->redirectType) {
                            case "SHOW_MESSAGE":
                                $responseArray["chapterRegStatus"] = true;
                                break;
                            case "REDIRECT_AUTOMATIC":
                                $responseArray["chapterRegStatus"] = true;
                                if (!is_null($jsonBody->url)) {
                                    $responseArray["redirectURL"] = $jsonBody->url;
                                }
                                break;
                        }

                    } catch (Exception $ex) {
                        $this->logger->error("There was a problem trying to register to chapter: " + $ex->getMessage());
                    }

                }
            }
        }

        $this->response->setContent(json_encode($responseArray));

        return $this->response;
    }

    public function handleChapterRegistrationRecaptchaAction()
    {
        // Initialise the response
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $responseArray = array(
            "chapterRegStatus" => false
        );

        $gRecaptchaRresponse = $this->request->get("g-recaptcha-response");
        $emailAddress = $this->request->get("emailAddress", 'email');
        $chapterId = $this->request->get("chapterId", 'int');


        // Only if the post data is valid and eligible then continue
        if (!empty($emailAddress) && !empty($chapterId)) {

            if (isset($gRecaptchaRresponse) && !empty($gRecaptchaRresponse)) {

                $captchaValidationResponse = $this->googleCheck($gRecaptchaRresponse);

                if ($captchaValidationResponse->success) {

                    // Call api endpoint here
                    $url = 'internal/visitorregistration/register';
                    $jsonPayload = array(
                        'emailAddress' => $emailAddress,
                        'chapterId' => $chapterId
                    );

                    try {
                        $overallRequest = $this->client->request('POST', $url, [
                            'base_uri' => $this->config->bniApi->internalCoreApiUrl,
                            'json' => $jsonPayload
                        ]);

                        $jsonBody = json_decode($overallRequest->getBody()->getContents())->content;

                        switch ($jsonBody->redirectType) {
                            case "SHOW_MESSAGE":
                                $responseArray["chapterRegStatus"] = true;
                                break;
                            case "REDIRECT_AUTOMATIC":
                                $responseArray["chapterRegStatus"] = true;
                                if (!is_null($jsonBody->url)) {
                                    $responseArray["redirectURL"] = $jsonBody->url;
                                }
                                break;
                        }

                    } catch (Exception $ex) {
                        $this->logger->error("There was a problem trying to register to chapter: " + $ex->getMessage());
                    }

                }
            }
        }

        $this->response->setContent(json_encode($responseArray));

        return $this->response;
    }


    public function checkRecapthaAction()
    {

        // Return json content
        $this->response->setContentType('application/json', 'UTF-8');

        //todo move this to server side configuration
        $this->response->setHeader('Access-Control-Allow-Origin', '*');

        $gRecaptchaRresponse = $this->request->get("g-recaptcha-response");
        if (isset($gRecaptchaRresponse) && !empty($gRecaptchaRresponse)) {

            $responseData = $this->googleCheck($gRecaptchaRresponse);

            if ($responseData->hostname != $_SERVER["HTTP_HOST"] AND $responseData->hostname != "www.bniconnectglobal.com") {
                $this->response->setContent('{"messageSendSatus":false}');
            }
            if ($responseData->success) {
                if (!empty($_REQUEST['userId'])) {
                    $_REQUEST['userId'] = $this->securityUtils->decrypt($_REQUEST['userId']);
                    if (!is_numeric($_REQUEST['userId'])) {
                        $this->response->setContent('{"messageSendSatus":false}');
                    }
                }
                if (!empty($_REQUEST['companyId'])) {
                    $_REQUEST['companyId'] = $this->securityUtils->decrypt($_REQUEST['companyId']);
                    if (!is_numeric($_REQUEST['companyId'])) {
                        $this->response->setContent('{"messageSendSatus":false}');
                    }
                }
                if (!empty($_REQUEST['regionId'])) {
                    $_REQUEST['regionId'] = $this->securityUtils->decrypt($_REQUEST['regionId']);
                    if (!is_numeric($_REQUEST['regionId'])) {
                        $this->response->setContent('{"messageSendSatus":false}');
                    }
                }
                if (!empty($_REQUEST['countryId'])) {
                    $_REQUEST['countryId'] = $this->securityUtils->decrypt($_REQUEST['countryId']);
                    if (!is_numeric($_REQUEST['countryId'])) {
                        $this->response->setContent('{"messageSendSatus":false}');
                    }
                }
				
				$_REQUEST['messageBody'].='<br><br><br><br>';
				if(($_REQUEST['sent_by']!="")&&($_REQUEST['fromEmail']!="")):
					$_REQUEST['messageBody'].='<br>'.$_REQUEST['sent_by']." ".$_REQUEST['fromEmail'];
				endif;	
				if(($_REQUEST['originated_from']!="")&&($_REQUEST['domainName']!="")):
					$_REQUEST['messageBody'].='<br>'.$_REQUEST['originated_from']." ".$_REQUEST['domainName'];
				endif;

                $ch = curl_init();

                //curl_setopt($ch, CURLOPT_URL, $GLOBALS['serverdomain']."/bnicms/lib/curlposttest.php");
                curl_setopt($ch, CURLOPT_URL, $this->config->general->baseUrl . "/web/open/appsCmsSendMessage?recaptcha=true");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST);
                $output = curl_exec($ch);
                curl_close($ch);

                $this->response->setContent($output);

            } else {
                $this->response->setContent('{"reCaptchaFailed":true}');
            }

        } else {
            $this->response->setContent('{"reCaptchaFailed":true}');
        }

        return $this->response;

    }

    private function googleCheck($gRecaptchaRresponse)
    {

        $secret = '6LfvSREUAAAAAAjHdtlGqGNvMw61Dnolb_dHxbOr';
        //get verify response data

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $gRecaptchaRresponse);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST);
        $verifyResponse = curl_exec($ch);
        curl_close($ch);

        return json_decode($verifyResponse);

    }


}
