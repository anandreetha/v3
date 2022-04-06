<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class NewsletterWidget extends BaseWidget
{
    public function getContent($languages, $orgIds, $domainName, $renderStaticContent)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $this->view->orgIds = $orgIds;
        $this->view->cleandomain = $domainName;
        $this->view->formUrl = $renderStaticContent ? 'http://' . $domainName .
            '/bnicms/v3/backend/captcha/check' : $this->url->get('backend/captcha/check');

        $querylocalestring = BaseWidget::configureLocales((array)$languages, $this->request->getLanguages());
        $jsonUrl = "/web/open/appsCmsGetAllCountryList?" . $querylocalestring;
        
        try {
            $apiRequest = $this->client->request('GET', $jsonUrl, [
                'base_uri' => $this->config->general->baseUrl
            ]);
        } catch (Exception $ex) {
            $this->logger->error("NewsletterWidget: " . $ex->getMessage());
            throw $ex;
        }

        $output = $this->response->setContent($apiRequest->getBody()->getContents());
        $this->view->jsonResponse = json_decode($output->getContent());

        // Render a view and return its contents as a string
        return $this->view->render('newsletter-widget');
    }
}