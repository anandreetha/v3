<?php

namespace Multiple\Frontend\Controllers;

use Multiple\Core\Models\WebsiteOrg;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class ConsumeController extends BaseController
{

    public function chapterInfoAction($encryptedChapterID = 0)
    {
        // Disable the view
        $this->view->disable();
        $encryptedChapterID = $this->request->get('encodedChapterId');
        $locale = $this->request->get("locale");
        $tokenArray = $this->frontendTranslator->getText($locale);

        // Return json content
        $this->response->setContentType('application/json', 'UTF-8');

        $chapterID = $this->securityUtils->decrypt($encryptedChapterID);
        if ($this->dispatcher->getParam("chapterID") !== null) {
            $chapterID = $this->dispatcher->getParam("chapterID");
        }

        // Sanitize the id parameter
        $SanitizedChapterID = $this->filter->sanitize($chapterID, 'int');
        try {
            // Initiate request
            $apiRequest = $this->client->request('GET', 'core-api/public/chapters/' .
                $SanitizedChapterID . '?locale=' . $locale);
            $jsonString = $apiRequest->getBody()->getContents();
            $jsonResponse = json_decode($jsonString);
            $time = BaseWidget::getHour($jsonResponse->content->chapterDetails->meetingTime, $tokenArray);
            $jsonResponse->content->chapterDetails->meetingTime = $time;
            $jsonResponse->content->chapterDetails->meetingDay = $tokenArray->_('common.daynames.' .
                strtolower($jsonResponse->content->chapterDetails->meetingDay == 'THURSDAY' ? 'THR' :
                    substr($jsonResponse->content->chapterDetails->meetingDay, 0, 3)));
            // Pass over the response content from the request
            $jsonString = json_encode($jsonResponse);
            $this->response->setContent($jsonString);
        } catch (Exception $ex) {
            $this->logger->error("ConsumeController " . $ex->getMessage());
            $this->response->setContent(json_encode(array()));

            if (!empty($ex->getResponse())) {
                dump($ex->getResponse()->getReasonPhrase());
                dump($ex->getResponse()->getStatusCode());
            }
        }

        return $this->response;
    }

    public function getTYFCBValueAction()
    {
        $this->view->disable();
        $encryptedChapterID = $this->request->get('encodedChapterId');
        $chapterID = $this->securityUtils->decrypt($encryptedChapterID);
        $this->response->setContentType('application/json', 'UTF-8');

        $currencyCodeRequest = $this->client->request('GET', '/internal/tyfcb-goal/'.$chapterID , [
            'base_uri' => $this->config->bniApi->internalMemberApiUrl
        ]);
        $jsonResponse = json_decode($currencyCodeRequest->getBody()->getContents());
        $tyfcbDetail = new \stdClass();
        if($jsonResponse != null){
            $tyfcbDetail->tyfcbval = $jsonResponse->totalAmount;
            $tyfcbDetail->currencycode = $jsonResponse->currency;
        }


        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setContent(json_encode($tyfcbDetail));
        return $this->response;
    }
}
