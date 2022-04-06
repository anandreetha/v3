<?php

namespace Multiple\Backend\Controllers;

use Multiple\Core\Models\Language;
use Multiple\Core\Models\WebsiteOrg;
use Phalcon\Mvc\Controller;
use Exception;

class BackendBaseController extends BaseController
{

    public function beforeExecuteRoute(){
        // Throw out the user if we aren't logged into the API, and we aren't in dev mode
        if ($this->session->get("session-token") == null) {
            $this->failAndRedirect('backend/error/auth');
            return false;
        }
    }

    public function initialize()
    {

        $showNavMenu = array();
        $showNavMenu["country"] = $this->showNavMenuItem($this->constants->getCountryWebsiteTypeId());
        $showNavMenu["region"] = $this->showNavMenuItem($this->constants->getRegionWebsiteTypeId());
        $showNavMenu["chapter"] = $this->showNavMenuItem($this->constants->getChapterWebsiteTypeId());
        $showNavMenu["all"] = $this->showAllSitesNavMenuItem();

        $this->initProfileInfo();

        $this->assets->addJs('js/adminlte.min.js');
        $this->assets->addJs('js/dataTables.bootstrap.min.js');
        $this->assets->addJs('js/dataTables.responsive.js');
        $this->assets->addJs('js/dateformat.js');
        $this->assets->addJs('js/draganddrop.js');
        $this->assets->addInlineJs('var multiUploadMessage = "' . $this->translator->_('cms.v3.admin.library.multifileuploadmsgtxt') . '";');
        $this->assets->addJs('js/ajaxImageUpload.js');
        $this->assets->addJs($this->config->general->cdn . '/js/jQuery/idleTimeout/jquery.idletimeout.js');
        $this->assets->addJs($this->config->general->cdn . '/js/jQuery/idleTimeout/jquery.idletimer.changed.js');
        $this->assets->addJs($this->config->general->cdn . '/js/jQuery/idleTimeout/jquery.idletimer.js');
        $this->assets->addJs($this->config->general->cdn . '/js/common.min.js');
        $this->assets->addJs($this->config->general->cdn . '/bundles/jquery.mmenu-6.1.0/jquery.mmenu.all.js', false);
        $this->assets->addJs($this->config->general->cdn . '/bundles/jquery-validation-1.16.0/dist/jquery.validate.min.js', false);

        $this->assets->addCss('css/ionicons.css');
        $this->assets->addCss('css/theme.css');
        $this->assets->addCss('css/dataTables.bootstrap.min.css');
        $this->assets->addCss($this->config->general->cdn . '/bundles/jquery.mmenu-6.1.0/jquery.mmenu.all.css', false);

        $this->view->displaySideMenu = true;
        $this->view->contentTitle = "";
        $this->view->contentSubTitle = "";
        $this->view->showNavItem = $showNavMenu;

        /*
         * Determine whether the logged in user uses a right-to-left language (such as Hebrew), or a left-to-right language (like all other languages currently).
         * We make use of bootstrap RTL and our own theme-rtl.css to then make the bootstrap-driven admin panel look roughly correct.
         *
         * Unfortunately AdminLTE does not support RTL (as explained in BNIDEV-4176), so we need to think about how we approach this then, but the below approach covers
         * 95% of the RTL work.
         */
        $userLocalisationValues = $this->getLocalisationHtmlAndTimezoneForUser();
        $isRightToLeft = strpos($userLocalisationValues[0], 'rtl') !== false;

        $this->view->htmlTags = $userLocalisationValues[0];
        $this->view->isRightToLeft = $isRightToLeft;
        $this->view->buttonPullDirection = $isRightToLeft ? 'pull-left' : 'pull-right';
        $this->view->userTimezone = $userLocalisationValues[1];

        if ($isRightToLeft) {
            $this->assets->addCss('css/bootstrap-rtl.min.css');
            $this->assets->addCss('css/theme-rtl.css');
            $this->assets->addCss('css/adminlte-rtl/AdminLTE-rtl.css');
            $this->assets->addCss('css/adminlte-rtl/_all-skins-rtl.css');
        } else {
            $this->assets->addCss('css/AdminLTE.css');
            $this->assets->addCss('css/skin-red.css');
        }
		$this->assets->addCss($this->config->general->cdn . '/new_template/libs/bni-font-icon-4.0/style.css');

    }

    /**
     * Populate two variables which drive the user's name and profile image in the top right of the admin panel
     */
    private function initProfileInfo() {
        try {
            $apiRequest = $this->client->request('GET', '/core-api/profile/summary', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->session->get("session-token")
                ]
            ]);

            $profileSummary = json_decode($apiRequest->getBody()->getContents());
            $this->view->fullName = $profileSummary->content->firstName . ' ' . $profileSummary->content->lastName;

            if (array_key_exists('profileImageId', $profileSummary->content) && $profileSummary->content->profileImageId != null) {
                $this->view->profileImage = $this->config->bniApi->apiBrokerUrl . '/core-api/public/image/' . $profileSummary->content->profileImageId;
            } else {
                $this->view->profileImage = $this->config->general->baseUri . 'img/default_profile.gif';
            }
            $this->creator = $profileSummary->content->firstName . ' ' . $profileSummary->content->lastName;
        } catch (Exception $ex) {
            // If we have a 401 unauthorized error, our API session has expired - so redirect the user to the auth error page
            $this->logger->error("BackendBaseController " . $ex->getMessage());
            if (!empty($ex->getResponse())) {
                if ($ex->getResponse()->getStatusCode() === 401 || $ex->getResponse()->getStatusCode() === 500) {
                    $this->failAndRedirect('backend/error/auth');
                    return;
                }
            }
        }
    }

    /**
     * @return array, [0] being the HTML attributes, e.g. dir="ltr" lang="en-US" and [1] being the user's timezone
     */
    public function getLocalisationHtmlAndTimezoneForUser() {
        if ($this->session->get("session-token") == null || $this->session->get("userId") == null) {
            return array("dir='ltr' lang='en-GB'", "Europe/London");
        }

        // Call http://[domain]:8050/internal/profile/[userId]/locale to get the user's orientation
        $url = 'internal/profile/' . $this->session->get('userId') . '/locale';

        try {
            $overallRequest = $this->client->request('GET', $url, [
                'base_uri' => $this->config->bniApi->internalCoreApiUrl
            ]);

            $jsonBody = json_decode($overallRequest->getBody()->getContents()) -> content;

            $normalisedLocaleToken = str_replace('_', '-', $jsonBody->locale); // change from en_US to en-US
            $htmlAttributes = "dir='{$jsonBody->orientation}' lang='{$normalisedLocaleToken}'";
            return array($htmlAttributes, $jsonBody->timezone);

        } catch (Exception $ex) {
            $this->logger->error("Could not get the page orientation for the user due to exception: " + $ex->getMessage());
            return array("dir='ltr' lang='en-GB'", "Europe/London");
        }
    }

    protected function showAllSitesNavMenuItem()
    {
        return $this->session->get('userId') === "1";
    }

    protected function showNavMenuItem($typeId)
    {
        return $this->authenticationService->hasAnyOrgPermissions($this->constants->getWebsitePermission($typeId), $this->session->get("session"));
    }
}