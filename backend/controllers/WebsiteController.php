<?php

namespace Multiple\Backend\Controllers;

use Embed\Embed;
use Multiple\Core\Models\Language;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteOrg;
use Multiple\Core\Models\WebsiteType;
use \Multiple\Core\Models\Page;
use Phalcon\Mvc\View;
use Phalcon\Http\Response;

use Multiple\Backend\Exceptions\LanguagePublishException;
use Multiple\Backend\Exceptions\WebsiteDirectoryException;
use Phalcon\Db\Column;
//use Phalcon\Logger;

class WebsiteController extends BackendBaseController
{

    const ALL_WEBSITES_ADDED_ERROR_MESSAGE = 'All possible websites have been added.';

    const WEBSITE_TYPE_COUNTRY = 1;
    const WEBSITE_TYPE_REGION = 2;
    const WEBSITE_TYPE_CHAPTER = 3;

    public function indexAction()
    {
    }

    /**
     * @param $websiteOrgList
     * @return array
     */
    public function convertWebsiteOrgToArray($websiteOrgList)
    {
        $orgsIdList = array();
        foreach ($websiteOrgList as $org) {
            array_push($orgsIdList, $org->org_id);
        }
        return $orgsIdList;
    }

    // TODO Temp copy and pasted into WebsiteController, WeblanguageController and PageController, move to central place after RBAC work more fleshed out
    private function getWebsiteOrgIds($websiteOrgs)
    {
        $weborgs = array();

        foreach ($websiteOrgs as $org) {
            $weborgs[] = $org->orgId;
        }

        return $weborgs;
    }

    public function addAction($typeId)
    {
        //post action to create the website
        $creator = $this->creator;
        $this->websiteService->createWebsite($creator, $typeId);

        // get the list of user countries from their session, ignoring those already with a website
        $countryObjList = $this->authenticationService->getParentCountries($this->session->get("session"));

        // If creating a country - remove those countries already with a website
        if ($typeId == 1) {
            $websiteOrgList = WebsiteOrg::getWebsiteOrgPerType($typeId);
            $orgsIdList = $this->convertWebsiteOrgToArray($websiteOrgList);
            $countryObjList = array_filter(
                array_values($countryObjList),
                function ($e) use ($orgsIdList) {
                    return !in_array($e->orgId, $orgsIdList);
                }
            );
        }

        uasort($countryObjList, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->orgName);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->orgName);
            return strcmp($at, $bt);
        });

        $this->view->typeId = $typeId;
        $this->view->inputWebsiteCountryList = $countryObjList;
        $this->view->websiteType = WebsiteType::findFirst();
        $this->view->setTemplateAfter('ajax-layout');
    }
	public function countrylevelAction($countryid='')
    {
	   $this->view->countryid = $countryid; 	
	   $this->view->setTemplateAfter('ajax-layout');
    }
	public function addcountrylevelAction($countryid)
    {
	   
	   $this->view->countryid = $countryid; 
	   $creator = $this->creator;
	   
	   $typeId=2;
       $this->websiteService->processCountryLevelWebsite($creator, $typeId,$countryid);
	   
	   $typeId=3;
	   $this->websiteService->processCountryLevelWebsite($creator, $typeId,$countryid);
	   
	   
	   $this->flash->success($this->translator->_('cms.v3.admin.websitecreation.newwebsiteallwebsitesaddedsuccessmsg'));
	   $this->view->setTemplateAfter('ajax-layout');
	   $redirect_url='backend/website/list/countrylevel'.$countryid;
	   
	   print'<script>window.parent.location.reload();</script>';
    }
	
    public function getRegionsAction($countryId)
    {
        // Disable the view
        $this->view->disable();

        // Return json content
        $this->response->setContentType('application/json', 'UTF-8');

        // get the list of user regions from their session, ignoring those already with a website
        $regionObjList = array_values($this->authenticationService->getParentRegions($this->session->get("session")));

        // filter the over all user regions to get those only for the selected country Id
        $regionObjList = array_filter(
            $regionObjList,
            function ($e) use ($countryId) {
                return $e->parentOrgId == $countryId; // get the list of user countries from their session, or return 2 countries if in dev mode
            }
        );

        // Remove those already assigned to a website
        $websiteOrgList = WebsiteOrg::getWebsiteOrgPerType(2);
        $orgsIdList = $this->convertWebsiteOrgToArray($websiteOrgList);
        $regionObjList = array_filter(
            $regionObjList,
            function ($e) use ($orgsIdList) {
                return !in_array($e->orgId, $orgsIdList);
            }
        );
        $this->response->setContent(json_encode($regionObjList));
        return $this->response;

    }

    public function deletePageAction()
    {
        $response = new Response();
        $pageId = $this->filter->sanitize($this->request->getPost("pageid"), 'int');
        $currentPage = Page::findFirstById($pageId);

        if (!$this->hasUserGotPermissionToAccessWebsite($currentPage->Website)) {
            $response->setStatusCode(400);
            return $response->setContent(json_encode(array('error' => 'No permission to delete pages on this website')));
        }

        // Double check that the page we are deleting is a custom page and we've not somehow done a mischief with the ID on the view
        if ($currentPage->getTemplate() !== 'custom-page') {
            $response->setStatusCode(400);
            return $response->setContent(json_encode(array('error' => 'We\'re trying to delete a page that is not a \'custom-page\'')));
        }

        try {
            $currentPage->delete();
            $response->setStatusCode(204);

        } catch (\Exception $ex) {
            $response->setStatusCode(400);
            $response->setContent(json_encode(array('error' => $ex->getMessage())));
        }
    }

    private function listCountryWebsites()
    {
        return Website::getFilteredWebsites(1,
            $this->authenticationService->getPermissionOrgs($this->constants->getCountryWebsitePermission(), $this->session->get("session")));//parentOrgId is usually passed for region/chapter
    }
	private function listCountryLevelWebsites($optCountryId)
    {
        return Website::getFilteredDefaultWebsites($optCountryId);
    }
    private function listRegionWebsites($optCountryId)
    {
        return Website::getFilteredWebsites(2,
            $this->authenticationService->getPermissionOrgs($this->constants->getRegionWebsitePermission(), $this->session->get("session")), $optCountryId);
    }

    private function listChapterWebsites($optRegionId)
    {
        return Website::getFilteredWebsites(3,
            $this->authenticationService->getPermissionOrgs($this->constants->getChapterWebsitePermission(), $this->session->get("session")), $optRegionId);
    }

    private function listChapterWebsitesPerCountry($optCountryId)
    {
        return Website::getChapterWebsitesForCountry($optCountryId);
    }

    public function listAction($websiteType = "", $grandparentId = "", $parentId = "")
    {
        $currentPage = $this->request->get('page', 'int', 1);

        // Only allow viewing all websites in the same list (e.g. the /website/list URL) if we're in dev mode or we're the sys admin
        if (!in_array($websiteType, ["countries", "regions", "chapters","countrylevel"]) && $this->session->get('userId') !== "1") {
            // Can the user access countries?
            if ($this->showNavMenuItem($this->constants->getCountryWebsiteTypeId())) {
                $this->failAndRedirect('backend/website/list/countries');
            } elseif ($this->showNavMenuItem($this->constants->getRegionWebsiteTypeId())) {
                $this->failAndRedirect('backend/website/list/regions');
            } elseif ($this->showNavMenuItem($this->constants->getChapterWebsiteTypeId())) {
                $this->failAndRedirect('backend/website/list/chapters');
            } else {
                $this->failAndRedirect('backend/error/permissionDenied');
            }

            return;
        }

        // Do some more real checks - if the user's viewing /website/list/countries, do they have permission to view countries? Etc
        if (($websiteType === "countries" && !$this->showNavMenuItem($this->constants->getCountryWebsiteTypeId())) ||
            ($websiteType === "regions" && !$this->showNavMenuItem($this->constants->getRegionWebsiteTypeId())) ||
            ($websiteType === "chapters" && !$this->showNavMenuItem($this->constants->getChapterWebsiteTypeId()))) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $this->view->websiteType = $websiteType;
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.websitelist.subheading');
        $this->view->displaySideMenu = false;

        $countryList = array_values($this->authenticationService->getParentCountries($this->session->get("session")));
        uasort($countryList, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->orgName);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->orgName);
            return strcmp($at, $bt);
        });

        $orgCountries = array();
        $firstCountryId = -1;
        if ($countryList != null) {
            foreach ($countryList as $country) {
                $orgCountries[$country->orgId] = $country->orgName;
                if ($firstCountryId === -1) $firstCountryId = $country->orgId;
            }
        }

        $this->view->orgCountries = $orgCountries;


        $regionList = array_values($this->authenticationService->getParentRegions($this->session->get("session")));
        uasort($regionList, function ($at, $bt) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $at->orgName);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $bt->orgName);
            return strcmp($at, $bt);
        });
		
		if(($websiteType=="countrylevel")&&(count($orgCountries)>1)):
			$grandparentOrgId = $grandparentId == "" ? "" : $grandparentId;
		else:
			$grandparentOrgId = $grandparentId == "" ? $firstCountryId : $grandparentId;
		endif;	

        $orgRegions = array();
        $firstRegionId = -1;
        if ($regionList != null) {
            foreach ($regionList as $region) {
                if ($region->parentOrgId == $grandparentOrgId) {
                    $orgRegions[$region->orgId] = $region->orgName;
                    if ($firstRegionId === -1) $firstRegionId = $region->orgId;
                }
            }
        }

        $parentOrgId = $parentId == "" ? $firstRegionId : $parentId;

        switch ($websiteType) {

            case 'countries':
                $websites = $this->listCountryWebsites();
                $this->view->typeId = 1;
                $this->view->contentTitle = $this->translator->_('cms.v3.admin.websitelist.countryheading');
                $this->view->orgNameMap = $this->authenticationService->getCountryNames();
                break;

            case 'regions':
                $websites = $this->listRegionWebsites($grandparentOrgId);
                $this->view->typeId = 2;
                $this->view->orgNameMap = $this->authenticationService->getRegionNames();
                $this->view->contentTitle = $this->translator->_('cms.v3.admin.websitelist.regionheading');
                break;

            case 'chapters':
                $websites = (!($parentOrgId > 0) && $grandparentOrgId != -1) ? $this->listChapterWebsitesPerCountry($grandparentOrgId) : $this->listChapterWebsites($parentOrgId);
                $this->view->typeId = 3;
                $this->view->orgNameMap = $this->authenticationService->getChapterNames();
                $this->view->contentTitle = $this->translator->_('cms.v3.admin.websitelist.chapterheading');
                break;
			case 'countrylevel':
                $websites = $this->listCountryLevelWebsites($grandparentOrgId);
				//$websites = $this->listRegionWebsites($grandparentOrgId);
				$this->view->preSelectedCountryId = $grandparentOrgId;
                $this->view->typeId = "";
                $this->view->orgNameMap = $this->authenticationService->getChapterNames();
                $this->view->contentTitle = $this->translator->_('cms.v3.admin.sidebar.countryleveltemplates');
                break;	

            default:
                $websites = Website::find(['id_country IS NULL OR id_country=0']);
                $orgIdToName = array();
                $orgNameMap = $this->authenticationService->getCountryNames() + $this->authenticationService->getRegionNames() + $this->authenticationService->getChapterNames();
                foreach ($websites as $website) {
                    foreach ($website->getWebsiteOrg() as $org) {
                        if (array_key_exists($org->orgId, $orgNameMap)) {
                            $orgIdToName[$org->orgId] = $orgNameMap[$org->orgId];
                        }
                    }
                }

                $this->view->typeId = "";
                $this->view->orgNameMap = $orgIdToName;
                $this->view->contentTitle = $this->translator->_('cms.v3.admin.websitelist.allheading');
                break;
        }

        $this->view->websites = $websites;

        if ($this->view->typeId == 2) {
            $this->view->preSelectedCountryId = $grandparentOrgId;

        } else if ($this->view->typeId == 3) {

            $this->view->orgRegions = $orgRegions;
            $this->view->preSelectedCountryId = $grandparentOrgId;
            $this->view->preSelectedRegionId = $parentOrgId;

        }
    }

    public function viewAction($websiteId, $languageId = 1)
    {

        $websiteId = $this->filter->sanitize($websiteId, 'int');

        /** @var $website \Multiple\Core\Models\Website */
        $website = Website::findFirstById($websiteId);
        $websiteSettings = [];

        if ($website != null) {

            if (!$this->hasUserGotPermissionToAccessWebsite($website)) {
                $this->failAndRedirect('backend/error/permissionDenied');
                return;
            }
            // Build back button navigation
            $this->view->redirectToUrl = $this->_buildBackNavigationLink($website);

            if ($website->is_default != '1') {
                $fixedSettings [] = $this->buildWebsiteNameSetting($website);

                switch ($website->type_id) {
                    case '1':
                        $selectableCountries = $this->buildCountrySetting($website);
                        $fixedSettings [] = $selectableCountries;
                        $fixedSettings [] = $this->buildNewsCountrySetting($website, $selectableCountries->setting->selectable_values);
                        break;
                    case '2':
                        $selectableRegions = $this->buildRegionSetting($website);
                        $fixedSettings [] = $selectableRegions;
                        $fixedSettings [] = $this->buildNewsRegionSetting($website, $selectableRegions->setting->selectable_values);
                        array_push($fixedSettings, $this->buildRegionLocationSetting($website));
                        array_push($fixedSettings, $this->buildRegionExecutiveDirectorSetting($website));
                        array_push($fixedSettings, $this->buildRegionContactTelephoneSetting($website));
                        array_push($fixedSettings, $this->buildRegionContactEmailSetting($website));
                        break;
                }
            } else {
                $fixedSettings = array();
            }

            foreach ($website->WebsiteSettings as $websiteSetting) {
                if ($websiteSetting->setting->formInputType <> 'fixed') {
                    if ($this->request->isPost()) {
                        $websiteSetting->value = $this->request->getPost('settingValue/' . $websiteSetting->id);
                    }
                    $websiteSettings[] = $websiteSetting;
                }
            }

            //If a chapters region hasn't been published yet we need to hide the publish button for the chapter
            if ($website->getTypeId() == 3) {
                if (count($website->getWebsiteOrg()) > 0) {
                    $websiteOrg = $website->getWebsiteOrg()->getFirst();
                    $parentWebsiteOrg = WebsiteOrg::findFirst("org_id = " . $websiteOrg->parent_org_id);
                    if (isset($parentWebsiteOrg)) {
                        $parentWebsite = $parentWebsiteOrg->getWebsite();
                        if (is_null($parentWebsite->getLastPublished())) {
                            $this->view->chapterRegionPublished = false;
                        } else {
                            $this->view->chapterRegionPublished = true;
                        }
                    }
                }
            }
			$allow_access=$this->authenticationService->getCountryAuthCheck($website->id);
			$this->view->allow_access = $allow_access;

            $this->view->website = $website;
            $this->view->fixedSettings = $fixedSettings;
            $this->view->websiteSettings = $websiteSettings;
            $this->view->contentTitle = $website->name;
            if ($website->getTypeId() == 3) {
                $this->view->contentSubTitle = $this->websiteHelper->strip_accents($website->clean_domain);
            }else {
                $this->view->contentSubTitle = $website->clean_domain;
            }

            $this->populatePagesView($website, $languageId);
            $this->populateLibraryView($website);
        } else {
            $this->flashSession->error($this->translator->_('cms.v3.admin.websitelist.websiteviewdontexist'));
            $this->response->redirect('backend/website/list');
        }
    }

    private function buildWebsiteNameSetting($website)
    {
        $websiteNameSetting = new \stdClass();
        $websiteNameSetting->id = 'fixedSettingInputWebsiteName';
        if ($this->request->isPost()) {
            $websiteNameSetting->value = $this->request->getPost('fixedSettingInputWebsiteName');
        } else {
            $websiteNameSetting->value = $website->name;
        }
        $websiteNameSetting->setting = new \stdClass();
        $websiteNameSetting->setting->name = 'text_input';
        $websiteNameSetting->setting->translate_token = 'cms.v3.admin.settings.websitename';

        return $websiteNameSetting;
    }

    private function buildRegionLocationSetting($website)
    {

        $websiteNameSetting = new \stdClass();
        $websiteNameSetting->id = 'fixedSettingInputLocation';
        if ($this->request->isPost()) {
            $websiteNameSetting->value = $this->request->getPost('fixedSettingInputLocation');
        } else {
            foreach ($website->websiteSettings as $setting) {
                if ($setting->settingsId == 331) {
                    $websiteNameSetting->value = property_exists($setting, 'value') ? $setting->value : $setting->default_value;
                }
            }

        }
        $websiteNameSetting->setting = new \stdClass();
        $websiteNameSetting->setting->name = 'Location/Area';
        $websiteNameSetting->setting->translate_token = 'cms.v3.admin.settings.locationArea';

        return $websiteNameSetting;

    }

    private function buildRegionExecutiveDirectorSetting($website)
    {

        $websiteNameSetting = new \stdClass();
        $websiteNameSetting->id = 'fixedSettingInputExecutiveDirector';
        if ($this->request->isPost()) {
            $websiteNameSetting->value = $this->request->getPost('fixedSettingInputExecutiveDirector');
        } else {
            foreach ($website->websiteSettings as $setting) {
                if ($setting->settingsId == 332) {
                    $websiteNameSetting->value = property_exists($setting, 'value') ? $setting->value : $setting->default_value;
                }
            }

        }
        $websiteNameSetting->setting = new \stdClass();
        $websiteNameSetting->setting->name = 'Executive Director';
        $websiteNameSetting->setting->translate_token = 'cms.v3.admin.settings.executivedirector';

        return $websiteNameSetting;

    }

    private function buildRegionContactTelephoneSetting($website)
    {

        $websiteNameSetting = new \stdClass();
        $websiteNameSetting->id = 'fixedSettingInputContactTelephone';
        if ($this->request->isPost()) {
            $websiteNameSetting->value = $this->request->getPost('fixedSettingInputContactTelephone');
        } else {
            foreach ($website->websiteSettings as $setting) {
                if ($setting->settingsId == 333) {
                    $websiteNameSetting->value = property_exists($setting, 'value') ? $setting->value : $setting->default_value;
                }
            }

        }
        $websiteNameSetting->setting = new \stdClass();
        $websiteNameSetting->setting->name = 'Contact Telephone';
        $websiteNameSetting->setting->translate_token = 'cms.v3.admin.settings.contactTelephone';

        return $websiteNameSetting;

    }

    private function buildRegionContactEmailSetting($website)
    {

        $websiteNameSetting = new \stdClass();
        $websiteNameSetting->id = 'fixedSettingInputContactEmail';
        if ($this->request->isPost()) {
            $websiteNameSetting->value = $this->request->getPost('fixedSettingInputContactEmail');
        } else {
            foreach ($website->websiteSettings as $setting) {
                if ($setting->settingsId == 334) {
                    $websiteNameSetting->value = property_exists($setting, 'value') ? $setting->value : $setting->default_value;
                }
            }

        }
        $websiteNameSetting->setting = new \stdClass();
        $websiteNameSetting->setting->name = 'Contact Email';
        $websiteNameSetting->setting->translate_token = 'cms.v3.admin.settings.contactEmail';

        return $websiteNameSetting;

    }


    private function buildCountrySetting($website)
    {
        $countrySetting = new \stdClass();
        $countrySetting->id = 'fixedSettingSelectCountries';
        $countrySetting->setting = new \stdClass();
        if ($this->request->isPost()) {
            $availableCountries = $this->websiteService->getWebsiteCountries($website);
            $postedCountries = $this->request->getPost('fixedSettingSelectCountries');

            foreach ($availableCountries as $country) {
                if (in_array($country->value, $postedCountries)) {
                    $country->selected = true;
                } else {
                    $country->selected = false;
                }
            }
            $countrySetting->setting->selectable_values = $availableCountries;
        } else {
            $countrySetting->setting->selectable_values = $this->websiteService->getWebsiteCountries($website);
        }
        $countrySetting->setting->multiple = true;
        $countrySetting->setting->name = 'select_input';
        $countrySetting->setting->translate_token = 'cms.v3.admin.settings.countries';

        uasort($countrySetting->setting->selectable_values, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->display);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->display);
            return strcmp($at, $bt);
        });

        return $countrySetting;
    }

    private function buildNewsCountrySetting($website, $selectableCountries)
    {
        $newsCountrySetting = new \stdClass();
        $newsCountrySetting->id = 'fixedSettingSelectNewsCountries';
        if ($this->request->isPost()) {
            $selectedOrgs = $this->request->getPost('fixedSettingSelectNewsCountries');
            $allSelectableCountries = $selectableCountries;
        } else {
            // Get the website settings
            $allSelectableCountries = [];
            $websiteSetting = $this->websiteService->getWebsiteSetting($website, $this->constants->getNewsCountryWebsiteSettingId());

            if ($websiteSetting !== false) {
                $selectedOrgs = explode(',', $websiteSetting->value);
                $allSelectableCountries = $this->websiteService->getWebsiteCountries($website);
            }

        }

        $newsCountrySetting->setting = new \stdClass();
        $newsCountrySetting->setting->multiple = true;
        $newsCountrySetting->setting->name = 'select_input';
        $newsCountrySetting->setting->translate_token = 'cms.v3.admin.settings.newscountries';

        foreach ($allSelectableCountries as $selectableCountry) {

            if ($selectableCountry->selected) {
                if (!in_array($selectableCountry->value, $selectedOrgs)) {
                    $selectableCountry = $this->websiteService->buildSelectableValue($selectableCountry->value, $selectableCountry->display, false);
                } else {
                    $selectableCountry = $this->websiteService->buildSelectableValue($selectableCountry->value, $selectableCountry->display, true);
                }
                $newsCountrySetting->setting->selectable_values [] = $selectableCountry;
            }

        }

        uasort($newsCountrySetting->setting->selectable_values, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->display);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->display);
            return strcmp($at, $bt);
        });

        return $newsCountrySetting;
    }

    private function buildRegionSetting($website)
    {
        $regionSetting = new \stdClass();
        $regionSetting->id = 'fixedSettingSelectRegions';
        $regionSetting->setting = new \stdClass();
        if ($this->request->isPost()) {
            $availableRegions = $this->websiteService->getWebsiteRegions($website);
            $postedRegions = $this->request->getPost('fixedSettingSelectRegions');

            foreach ($availableRegions as $region) {
                if (in_array($region->value, $postedRegions)) {
                    $region->selected = true;
                } else {
                    $region->selected = false;
                }
            }
            $regionSetting->setting->selectable_values = $availableRegions;
        } else {
            $regionSetting->setting->selectable_values = $this->websiteService->getWebsiteRegions($website);
        }
        $regionSetting->setting->multiple = true;
        $regionSetting->setting->name = 'select_input';
        $regionSetting->setting->translate_token = 'cms.v3.admin.settings.regions';

        uasort($regionSetting->setting->selectable_values, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->display);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->display);
            return strcmp($at, $bt);
        });

        return $regionSetting;
    }

    private function buildNewsRegionSetting($website, $selectableRegions)
    {
        $newsRegionSetting = new \stdClass();
        $newsRegionSetting->id = 'fixedSettingSelectNewsRegions';
        if ($this->request->isPost()) {
            $selectedOrgs = $this->request->getPost('fixedSettingSelectNewsRegions');
            $allSelectableRegions = $selectableRegions;
        } else {
            // Get the website settings
            $allSelectableRegions = [];
            $websiteSetting = $this->websiteService->getWebsiteSetting($website, $this->constants->getNewsRegionWebsiteSettingId());

            if ($websiteSetting !== false) {
                $selectedOrgs = explode(',', $websiteSetting->value);
                $allSelectableRegions = $this->websiteService->getWebsiteRegions($website);
            }

        }

        $newsRegionSetting->setting = new \stdClass();
        $newsRegionSetting->setting->multiple = true;
        $newsRegionSetting->setting->name = 'select_input';
        $newsRegionSetting->setting->translate_token = 'cms.v3.admin.settings.newsregions';

        foreach ($allSelectableRegions as $selectableRegion) {

            if ($selectableRegion->selected) {
                if (!in_array($selectableRegion->value, $selectedOrgs)) {
                    $selectableRegion = $this->websiteService->buildSelectableValue($selectableRegion->value, $selectableRegion->display, false);
                } else {
                    $selectableRegion = $this->websiteService->buildSelectableValue($selectableRegion->value, $selectableRegion->display, true);
                }
                $newsRegionSetting->setting->selectable_values [] = $selectableRegion;
            }

        }

        uasort($newsRegionSetting->setting->selectable_values, function ($a, $b) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a->display);
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b->display);
            return strcmp($at, $bt);
        });

        return $newsRegionSetting;
    }
	public function FindAllChildChapters($clean_domain)
	{
		$response=array();
		$query=" SELECT id FROM website WHERE type_id=3 AND clean_domain LIKE :cleandomain";
	
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"cleandomain" => $clean_domain.'%'
			],
			[
				"cleandomain" => Column::BIND_PARAM_STR
			]
		);
		
		$result    = $data->fetchAll();	
		foreach($result as $rs):
			$response[]=$rs['id'];
		endforeach;
		return $response;	
	}
    public function deleteAction($websiteId, $websiteReturnType = false)
    {
        /** @var $website \Multiple\Core\Models\Website */
        $website = Website::findFirstById($websiteId);

        if ($website != null) {
            if (!$this->hasUserGotPermissionToAccessWebsite($website)) {
                $this->view->disable();
                return $this->response->redirect('backend/error/permissionDenied');
            }

            $isError = false;
            if ($website->type_id == 2) {
				$c=0; $child_arr=array();
				$chapterWebsites = Website::getChapterWebsitesForRegion($websiteId);
                foreach ($chapterWebsites as $chapterWebsite) {
                    $child_arr[]=$chapterWebsites[$c]->id;
					$c++;
					if ($this->websiteService->deleteWebsite($chapterWebsite)[0] == false) {
                        $isError = true;
                        $this->flash->error($this->translator->_('cms.v3.admin.pages.unabletodeleteregion'));
                        break;
                    }
                }
				$childweb=$this->FindAllChildChapters($website->clean_domain);	
				$oldchild=array_diff($childweb,$child_arr);
				foreach($oldchild as $oc):
					$old_website = Website::findFirstById($oc);
					$this->websiteService->deleteWebsite($old_website);
				endforeach;	
            }
            $result = $this->websiteService->deleteWebsite($website);
            if (!$isError) {
                if ($result[0] == true) {
                    $this->flashSession->success($result[1]);
                } else {
                    $this->flashSession->error($result[1]);
                }
            }
        } else {
            $this->flashSession->error($this->translator->_('cms.v3.admin.websitelist.websitedontexist'));
        }
        // Work out the url to send the use back to after we've deleted a site
        // But if the user is coming from the "all websites" section then
        // we don't want to redirect them to a site specific list page
        $websitePath = ($websiteReturnType === false ? "" : "/{$websiteReturnType}");

        return $this->response->redirect("backend/website/list{$websitePath}");
    }

    private function populatePagesView($website, $languageId)
    {
        $currentPage = $this->request->get('page', 'int', 1);

        $languages = array();
        foreach ($website->getWebsiteLanguage() as $webSitelanguage) {
            $language = $webSitelanguage->Language;
            $languages[$language->id] = $this->translator->_(trim($language->description_key));
        }

        $languages = $this->translationUtils->sortLanguages($languages, function ($a) {
            return $a;
        });

        // Unsupported language, use the first language for this website instead
        if (!array_key_exists($languageId, $languages)) {
            if (count($website->getWebsiteLanguage()) > 0) {
                $languageId = $website->getWebsiteLanguage()[0]->language_id;
            } else {
                $this->flash->error('Unsupported language, using default');
                $languageId = 1;
            }
        }

        $languageId = $this->filter->sanitize($languageId, 'int');
        $selectedLanguage = Language::findFirst($languageId);

        $pages = $website->getPage(['enabled = 1']);
        $pageContents = array();

        /*
         * Calculate the 'page order' (page.nav_order) for this page, i.e. the order we use in the 'background' of the datatable.
         * We say 'background' because we have child and grandchild pages which are 'exempt' from this page order, so what we'll do
         * (pending any better ideas!) is get the nav order and multiply it by 5. Then if we find a child page, we'll insert this
         * in between this new gap (of 5) so that it stays in-order on-screen. Better ideas welcome!
         */
        $backgroundPageOrder = array();
        foreach ($pages as $page) {
            $backgroundPageOrder[$page->getId()] = $page->getNavOrder() * 5;
        }

        foreach ($pages as $page) {

            // Get the matching page content for this language
            $matchingPage = $page->getPageContent(
                [
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $languageId
                    ],
                ]
            )->getFirst();

            // Skip trying to read any pages with any missing page content
            if($matchingPage == false){
                continue;
            }

            $pageLevel = $this->pageService->calculatePageLevel($page->getId());

            if ($pageLevel->level === "grandchild") {
                if (isset($backgroundPageOrder[$pageLevel->parentPageId])) {
                    $backgroundPageOrder[$page->getId()] = $backgroundPageOrder[$pageLevel->parentPageId] + 2;
                }
            } else if ($pageLevel->level === "child") {
                if (isset($backgroundPageOrder[$pageLevel->parentPageId])) {
                    $backgroundPageOrder[$page->getId()] = $backgroundPageOrder[$pageLevel->parentPageId] + 1;
                }
            }

            // Get the current navigation setting for the matching page
            $pageNavigationSetting = $this->settingsFactory->getPageContentSetting("NAVIGATION_LOCATION", $matchingPage->getPageContentSettings(), $matchingPage->id);

            // Create an array of page content and page settings, so we can group these two sets of data together
            if ($matchingPage) {
                $pageData = new \stdClass();
                $pageData->pageContent = $matchingPage;
                $pageData->pageSettings = $pageNavigationSetting;
                $pageData->templateName = $page->template;
                $pageData->pageLevel = $pageLevel->level;
                $pageContents[] = $pageData;
            }
        }

        // Finalise ordering
        foreach ($pageContents as $pageContent) {
            $pageContent->backgroundNavLevel = $backgroundPageOrder[$pageContent->pageContent->getPageId()];
        }

        $this->view->website = $website;
        $this->view->pageArray = $pageContents;
        $this->view->languages = $languages;
        $this->view->selectedLanguage = $selectedLanguage;
    }

    private function populateLibraryView($website)
    {
        $bucket = $this->mongo->selectGridFSBucket();

        $commonLibrary = $website->getCommonLibrary();

        $images = array();
        $documents = array();

        foreach ($commonLibrary as $libraryItem) {
            $id = $libraryItem->object_id;
            $thumbnail_id = $libraryItem->thumbnail_object_id;
            $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId($id)));
            if (!is_null($bucketFile)) {
                $extension = pathinfo($bucketFile->filename)['extension'];
                $basename = pathinfo($bucketFile->filename)['basename'];
                if ($extension == "pdf") {
                    $documents[] = $bucketFile->jsonSerialize();
                } else {
                    $bucketFile->offsetSet("thumbnail_id", $thumbnail_id);
                    $images[] = $bucketFile->jsonSerialize();
                }
            }
        }

        // Sort the image array by file name in asc order
        usort($images, array($this, "compareFileNames"));

        $this->view->images = $images;
        $this->view->documents = $documents;
    }
	public function curl_post_async($url, $params)
	{
		//echo $url;exit;
		$post_params = [];
		foreach ($params as $key => &$val) {
		  if (is_array($val)) $val = implode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
		}
		$post_string = implode('&', $post_params);

		$parts=parse_url($url);

		$fp = fsockopen(str_replace("www.", "local.", $parts['host']),
			isset($parts['port'])?$parts['port']:80,
			$errno, $errstr, 30);
			
		//echo "Error no==".$errno."<br>Error String".$errstr;	exit;

		//pete_assert(($fp!=0), "Couldn't open a socket to ".$url." (".$errstr.")");

		$out = "POST ".$parts['path']." HTTP/1.1\r\n";
		$out.= "Host: ".$parts['host']."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Cookie: PHPSESSID=".$_COOKIE['PHPSESSID']."\r\n";
		
		$out.= "Content-Length: ".strlen($post_string)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		
		//echo $out; //exit;
		if (isset($post_string)) $out.= $post_string;

		fwrite($fp, $out);
		fclose($fp);
	}
	public function publishAction($websiteId, $returnLanguage = false)
    {

		ignore_user_abort(true);
		$display=array();
        $websiteId = $this->filter->sanitize($websiteId, 'int');
        $website = Website::findFirstById($websiteId);
		
		//print"<pre>";print_r($_COOKIE);print_r($_SESSION);print"</pre>";exit;

		//$phql = "UPDATE website SET publish_status = 'PUBLISHING' WHERE id =".$websiteId;
		//$this->db->query($phql);	
		//$this->flashSession->clear();
		//$this->flashSession->success($this->translator->_('cms.v3.admin.pages.pagepublishedprocessmessage'));
		
		$process_url=$this->config->general->baseUri.'backend/website/publishprocess/'.$websiteId.'/'.$returnLanguage;
		
		//Cron Process Integration started
		//$params['websiteId']=$websiteId;
		//$params['returnLanguage']=$returnLanguage;
		//file_get_contents($process_url);
		
		$this->curl_post_async($process_url,[]);
		/*
		file_put_contents( "script.php", "/15 * * * * /usr/local/bin/php -q /script.php" ); 
		exec( "crontab script.php" );
		*/
		
		//exec("wget -O /dev/null -o /dev/null " . $process_url . " --background");
		
		//$client = new \GuzzleHttp\Client($defaultOptions);
		
		/*$client = new \GuzzleHttp\Client();
		$request = new \GuzzleHttp\Psr7\Request('GET', $process_url);
		$promise = $client->sendAsync($request)->then(function ($response) {
			echo 'I completed! ' . $response->getBody();
		});
		$promise->wait();*/
		
		$display=array();
		$display['publish_status']="PUBLISHING";
		$display['publish_status_msg']=$this->translator->_("cms.v3.admin.footer.PUBLISHING");
		//echo 'PUBLISHING';
		echo json_encode($display);exit;
		//echo json_encode($display);exit;
		
	}
	public function publishstatusAction($websiteId, $returnLanguage = false)
    {
		$display=array();
		$query = "select publish_status,last_published,last_modified,created_on from website WHERE id =:websiteid";
		
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"websiteid" => $websiteId
			],
			[
				"websiteid" => Column::BIND_PARAM_INT,
			]
		);		
		$result    = $data->fetchAll();		
		$rs=$result[0];
		
		$userLocalisationValues=$this->getLocalisationHtmlAndTimezoneForUser();
		$userTimezone=$userLocalisationValues[1]; 
		
		$display['publish_status']=$rs['publish_status'];
		$display['publish_status_msg']=$this->translator->_("cms.v3.admin.footer.".$rs['publish_status']);
		$display['last_published']=$this->translationUtils->formatDate($rs['last_published'], $userTimezone);
		$display['last_modified']=$this->translationUtils->formatDate($rs['last_modified'], $userTimezone);
		$display['created_on']=$this->translationUtils->formatDate($rs['created_on'], $userTimezone);
		
		
		echo json_encode($display);exit;
	}
    public function publishprocessAction($websiteId, $returnLanguage = false)
    {
		
		//$this->logger->error("Could not get the page orientation for the user due to exception: ");
		ignore_user_abort(true);
        //Required for larger sites
        set_time_limit(0);
		$this->flashSession->clear();
		$display=array();$nolanguage=array();
        $websiteId = $this->filter->sanitize($websiteId, 'int');
        $website = Website::findFirstById($websiteId);

		$phql = "UPDATE website SET publish_status = 'PUBLISHING' WHERE id =".$websiteId;
		$this->db->query($phql);	
		
        // Has the site published successfully
        $published = true;

        if (!$this->hasUserGotPermissionToAccessWebsite($website)) {
            $published = false;
            $this->failAndRedirect('backend/error/permissionDenied');
        } else {

            try {

                // Make sure the user has got an enabled language.
                $lang_status=$this->websiteService->validateWebsiteHasEnabledLanguage($website->WebsiteLanguage);
				//exit;
				if($lang_status=="y"){
					// Create the physical directory structure for the new website
					
					$childrenSites = [];$skip_child=array();

					if (strtolower($website->getWebsiteType()->name) === "region") {
						$childrenSites = $website->getChildWebsites();
					}
					
					foreach($childrenSites as $k=>$childWebsite) {
						$child_lang_status=$this->websiteService->validateWebsiteHasEnabledLanguage($childWebsite->WebsiteLanguage);
						if($child_lang_status=="n"){
							$skip_child[]=$childWebsite->id;
							$missed_language[]=$childWebsite;
						}							
					}
					
					if(count($skip_child)>0){	
						$this->websiteService->createWebsiteBackup($missed_language);
					}
					$this->websiteService->createWebsiteDirectory($website);
						
					foreach ($childrenSites as $k=>$childWebsite) {
						if(!in_array($childWebsite->id, $skip_child)){	
							$this->websiteService->createWebsiteDirectory($childWebsite);
						}else{
							$newsiteChk=$this->websiteService->pasteOldWebsiteDirectory($childWebsite);
							if($newsiteChk=="n"):
								$nolanguage[]=$childWebsite->id;
							endif;	
						}							
					}					
				}else{
					 $published = false;	
				}

            } catch (LanguagePublishException $lpEx) {
                $this->flashSession->error($lpEx->getMessage());
                $published = false;
            } catch (WebsiteDirectoryException $wdEx) {
                $this->flashSession->error($wdEx->getMessage());
                $published = false;
            } catch (\Exception $ex) {
                $this->flashSession->error('Unable to publish website');
                $published = false;
            }
        }
		
		if(count($skip_child)==0){ 
			$this->websiteService->deleteWebsiteBackup($website);
		}	
		
        if (!$published) { 
			
			
			$phql = "UPDATE website SET publish_status = 'UNPUBLISHED' WHERE id =".$websiteId;
			$this->db->query($phql);
				
			$display['status']='nc';
			echo json_encode($display);exit;
            /*return $this->dispatcher->forward(
                [
                    'module' => 'backend',
                    'controller' => 'website',
                    'action' => 'view',
                    'params' => [1 => $websiteId, 2 => 1, 3 => false]
                ]
            );*/
        }
        if (strtolower($website->getWebsiteType()->name) === "chapter") {
            $this->renderService->renderStaticContent($website, $website->getParentWebsite());
        } else {
            $this->renderService->renderStaticContent($website);
        }
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $skip_child)){	
				$this->renderService->renderStaticContent($childWebsite, $website);
			}
        }

        $this->renderService->renderImageStaticContent($website);
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $skip_child)){	
				$this->renderService->renderImageStaticContent($childWebsite);
			}
        }

        $renderSitemap = $this->renderService->renderSitemap($website);
//        if(!$renderSitemap){
//            $this->flashSession->error('sitemap.xml could not be produced');
//        }
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $skip_child)){	
				$this->renderService->renderSitemap($childWebsite);
			}
        }

        $renderRobots = $this->renderService->renderRobots($website);
//        if(!$renderRobots){
//            $this->flashSession->error('robots.txt could not be produced');
//        }
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $skip_child)){		
				$this->renderService->renderRobots($childWebsite);
			}
        }

        $dateTimeNow = new \DateTime();

        $website->last_published = $dateTimeNow->format('Y-m-d H:i:s');

        if ($website->save() === false) {
			$display['status']='e';
			$display['msg']='Website settings could not be be updated:';
			echo json_encode($display);exit;
            //return $this->flashSession->error('Website settings could not be be updated:');
			
        }

        // Update the published date for each child website
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $nolanguage)){			
				$childWebsite->last_published = $dateTimeNow->format('Y-m-d H:i:s');
				if(!in_array($childWebsite->id, $skip_child)){
					$publish_status='PUBLISHED';
				}else{
					$publish_status='UNPUBLISHED';	
				}	
				$childWebsite->publish_status = $publish_status;
				if ($childWebsite->save() === false) {
					$childWebsite->rollback();
					$this->logger->error(
						'Problem updating child published sites last published timestamp for website id#' . $childWebsite->id
					);
				};
			}	
        }

        // Build a config file to be saved in the published folder, which is accessible side wide, rather than for one locale.
        $this->renderService->buildStaticContentConfigFile($website);
        foreach ($childrenSites as $childWebsite) {
			if(!in_array($childWebsite->id, $skip_child)){
				$this->renderService->buildStaticContentConfigFile($childWebsite);
			}	
        }


        $this->flashSession->success($this->translator->_('cms.v3.admin.pages.pagepublishedmessage'));
		
		$display['status']='s';
		$display['msg']=$this->translator->_('cms.v3.admin.pages.pagepublishedmessage');
		
		$phql = "UPDATE website SET publish_status = 'PUBLISHED' WHERE id =".$websiteId;
		$this->db->query($phql);
		
		$this->flashSession->clear();
		
		echo json_encode($display);exit;

        /*return $this->dispatcher->forward(
            [
                'module' => 'backend',
                'controller' => 'website',
                'action' => 'view',
                'params' =>
                    [
                        1 => $websiteId,
                        2 => ($returnLanguage === false ? 1 : $returnLanguage),
//                        3 => true,
                    ]
            ]
        );*/
    }

    public function testAction()
    {
        return "hi";
    }

    /**
     * We need a stick side navigation menu
     * The reason this is done in an ajax request
     * using php rather than pure javascript
     * is because the html will load with an open
     * side bar then read the javascript and close
     *
     * This means the user will see the nav close quickly
     * on each page load.
     *
     * At least this way when storing the session we can
     * read the session when rendering the page and render
     * the side bar closed or open without any jumpy stuff
     *
     * We also use an ajax request so that we can set
     * the session when the user clicks the toggle button
     * ready for the next reload
     * @param $state
     * @return Response
     */
    public function navmenuAction($state)
    {
        // Disable the view and set a manual response
        $this->view->disable();
        $response = new Response();
        $response->setHeader('Content-Type', 'application/json');

        // If we don't have an xhr request then bail
        if (!$this->request->isAjax()) {
            return $response->setStatusCode(400);
        }

        // If we're getting then we want the content
        if ($this->request->isGet()) {
            return $response
                ->setStatusCode(200)
                ->setContent(json_encode(array('nav-menu' => $this->session->get('nav-menu'))));
        }

        // If we're posting then we're saving that bad man
        if ($this->request->isPost()) {
            $this->session->set('nav-menu', (string)$state);
            return $response->setStatusCode(204);
        }

        $response->setStatusCode(204);
    }


    private function compareFileNames($a, $b)
    {
        return strcasecmp($a->filename, $b->filename);
    }

    /**
     * Build the back button navigation button$
     * href programmatically
     * @param $website \Multiple\Core\Models\Website
     * @return String
     */
    private function _buildBackNavigationLink(Website $website): string
    {
        if ($website->getTypeId() <> $this->constants->getCountryWebsiteTypeId()) {
            foreach ($website->getWebsiteOrg() as $val) {
                $parentOrgIds[] = $val->parent_org_id;
                $parentOrg = WebsiteOrg::findFirst("org_id = " . $val->parent_org_id);
            }
        }

        $websiteTypePath = '';
        switch ($website->getTypeId()) {
            case $this->constants->getCountryWebsiteTypeId() :
                $websiteTypePath = '/countries';
                break;
            case $this->constants->getRegionWebsiteTypeId() :
                $websiteTypePath = '/regions';
                break;
            case $this->constants->getChapterWebsiteTypeId() :
                $websiteTypePath = '/chapters';
                $grandParentOrgId = isset($parentOrg) ? $parentOrg->parent_org_id : '';
                break;
        }

        if ($website->is_default == "1") {
            $websiteTypePath = '/';
			
			$website_access=$this->authenticationService->getCountryAuthCheck($website->id);
			if($website_access=="y"):				
					$website = Website::findfirst(
						[
							'id = :ids:',
							'bind' => [
							'ids' => $website->id
							],
						]
					);
					if($website->id_country):
						$websiteTypePath = '/countrylevel/'.$website->id_country;
					else:
						$websiteTypePath = '/countrylevel';
					endif;	
				endif;
		
			
        }
        $defaultsiteurl = 'backend/website/list';

        return $this->tag->linkTo(array
        (
            $websiteTypePath == '/chapters' ? (isset($parentOrgIds) ? 'backend/website/list' . $websiteTypePath . '/' . $grandParentOrgId . '/' . $parentOrgIds[0] : $defaultsiteurl) :
                ($websiteTypePath == '/regions' ? (isset($parentOrgIds) ? 'backend/website/list' . $websiteTypePath . '/' . $parentOrgIds[0] : $defaultsiteurl) : 'backend/website/list' . $websiteTypePath),
            '<i class="fa fa-arrow-left fa-lg pull-right"></i>',
            '<i class="fa fa-trash-o fa-lg"></i>',
            "data-toggle" => "tooltip",
            "title" => $this->translator->_('cms.v3.admin.websitelist.backbutton'),
            "class" => "newPageModal btn btn-default"
        ));
    }
}
