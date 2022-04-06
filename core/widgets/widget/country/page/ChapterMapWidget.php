<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class ChapterMapWidget extends BaseWidget
{
    public function getContent($orgIds, $domainName, $website, $languages, $renderStaticContent)
    {
        $this->view->setViewsDir('../core/widgets/views/country/page/');
        $this->view->countryIds = $orgIds;
        $this->view->domainName = $domainName;
        $this->view->languages = $languages->activeLanguage->localeCode;

        $this->view->bniConnectDomain = $renderStaticContent ?
            '/' : $this->config->general->baseUrl;
        $this->view->apiDomain = $renderStaticContent ?
            '/bnicms/v3/' : $this->config->general->baseUri;


        $websettings = [
            'allSettings' => $website->getWebsiteSettings()
        ];
        $googleMapApiKey = "";
        foreach ($websettings['allSettings'] as $websetting) {
            if ($websetting->getSettingsId() == '3') {
                $googleMapApiKey = $websetting->value;
            }
        }
        $this->view->googleMapApiKey = $googleMapApiKey == null ? "" : $googleMapApiKey;
        return $this->view->render('visit-a-chapter-widget');
    }
}
