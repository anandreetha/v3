<?php

namespace Multiple\Backend\Services\Service;

use Phalcon\Mvc\User\Component;
use \Firebase\JWT\JWT;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentWidgets;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteLanguage;

class AuthenticationService extends Component
{
    public function getCountries() {
        $countries = $this->redisCache->get("org-countries");

        if (is_null($countries)) {
            $this->buildOverallOrgList();
        }

        return $this->redisCache->get("org-countries");
    }

    public function getCountriesMap() {
        $countryMap = array();

        foreach($this->getCountries() as $key => $countryDetail) {
            $countryMap[$countryDetail->orgId] = $countryDetail;
        }

        return $countryMap;
    }

    public function getCountryNames() {
        $countryIdToName = array();

        foreach($this->getCountries() as $key => $countryDetail) {
            $countryIdToName[$countryDetail->orgId] = $countryDetail->orgName;
        }

        return $countryIdToName;
    }

    public function getCountryIds() {
        $countryIds = array();

        foreach($this->getCountries() as $key => $countryDetail) {
            $countryIds[] = $countryDetail->orgId;
        }

        return $countryIds;
    }

    public function getRegions() {
        $regions = $this->redisCache->get("org-regions");

        if (is_null($regions)) {
            $this->buildOverallOrgList();
        }

        return $this->redisCache->get("org-regions");
    }

    public function getRegionsMap() {
        $regionMap = array();

        foreach($this->getRegions() as $key => $regionDetail) {
            $regionMap[$regionDetail->orgId] = $regionDetail;
        }

        return $regionMap;
    }

    public function getRegionNames($parentOrgId = null) {
        $regionIdToName = array();

        foreach($this->getRegions() as $key => $regionDetail) {
            if($parentOrgId == null || ($parentOrgId == $regionDetail->parentOrgId)) {
                $regionIdToName[$regionDetail->orgId] = $regionDetail->orgName;
            }
        }

        return $regionIdToName;
    }

    public function getRegionIds(){
        $regionIds = array();

        foreach($this->getRegions() as $key => $regionDetail) {
            $regionIds[] = $regionDetail->orgId;
        }

        return $regionIds;
    }

    public function getChapters() {
        $chapters = $this->redisCache->get("org-chapters");

        if (is_null($chapters)) {
            $this->buildOverallOrgList();
        }

        return $this->redisCache->get("org-chapters");
    }

    public function getChaptersMap() {
        $chaptersMap = array();

        foreach($this->getChapters() as $key => $chapterDetail) {
            $chaptersMap[$chapterDetail->orgId] = $chapterDetail;
        }

        return $chaptersMap;
    }


    public function getChapterNames() {
        $chapterIdToName = array();

        foreach($this->getChapters() as $key => $chapterDetail) {
            if( property_exists($chapterDetail, 'orgName')) $chapterIdToName[$chapterDetail->orgId] = $chapterDetail->orgName;
        }

        return $chapterIdToName;
    }

    public function getChapterIds(){
        $chapterIds = array();

        foreach($this->getChapters() as $key => $chapterDetail) {
            $chapterIds[] = $chapterDetail->orgId;
        }

        return $chapterIds;
    }

    /**
     * Gets the updated org list from coreApi, and sorts it by org type. Stores the result in Redis with a short-lived timeout (60 seconds),
     * so that we frequently call this method - i.e. to ensure our data is fairly up to date.
     */
    private function buildOverallOrgList()
    {
        try {
            $overallRequest = $this->client->request('GET', 'internal/orgs', [
                'base_uri' => $this->config->bniApi->internalCoreApiUrl
            ]);
            $stringBody = $overallRequest->getBody()->getContents();
        
            $jsonList = json_decode($stringBody);

            $countries = array();
            $regions = array();
            $chapters = array();

            foreach ($jsonList as $key => $value) {
                foreach ($value as $x) {
                    if ($x->orgType == "orgtype.national") {
                        $countries[] =  $x;
                    } else if ($x->orgType == "orgtype.region") {
                        $regions[] =  $x;
                    } else {
                        $chapters[] =  $x;
                    }
                }
            };

            // Only cache for 1 minute, so this method will be naturally called every 1+ minutes if needed
            $this->redisCache->save("org-countries", $countries, 60);
            $this->redisCache->save("org-regions", $regions, 60);
            $this->redisCache->save("org-chapters", $chapters, 60);
        } catch (Exception $ex){
            $this->logger->error("AuthenticationService: " . $ex->getMessage());
            throw $ex;
        }
    }

    public function getAccessToken_UserSpecificOrgs($encrypted){

        $accessToken = $this->securityUtils->decrypt($encrypted);
        $token = JWT::decode($accessToken, $this->config->general->jwtSigningKey, array("HS256"));
        
        try {
        
            $apiRequest = $this->client->request('GET', 'session-service-node/document/get/' . $token->key, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken . ''
                ]
            ]);
			
			$userRequest = $this->client->request('GET', 'session-service-node/storage/user_permissions/'.$token->id);
			
        } catch (Exception $ex){
            $this->logger->error("AuthenticationService getAccessToken_UserSpecificOrgs: " . $ex->getMessage());
            throw $ex;
        }

        $possibleOrgs = $apiRequest->getBody()->getContents();
		$possibleOrgsJson = json_decode($possibleOrgs);
		
		$userList = $userRequest->getBody()->getContents();
		$userobj=json_decode($userList);
		
        foreach ($possibleOrgsJson as $key => $value) {
            if ($key == "data") {
                $userId=$value->document->jsonData->userId;
                $orgLists[] = $userobj->data->payload->orgLists;
            }
        }

        $finalIds = [];
        foreach ($orgLists as $key => $val) {
            foreach($val as $item => $value){
                foreach($value as $a => $b){
                    $finalIds[] = $b;}
            }
        }

        $orgIds = array_unique($finalIds);

        $accessibleCountries = array_intersect($orgIds, $this->getCountryIds());
        $accessibleRegions = array_intersect($orgIds, $this->getRegionIds());
        $accessibleChapters = array_intersect($orgIds, $this->getChapterIds());

        return array($accessToken, $accessibleCountries, $accessibleRegions, $accessibleChapters, $token->id, $token->user_name, $userobj,$possibleOrgsJson);
    }

    public function hasAnyOrgPermissions($permission, $session){
        $permissionOrgs = $this->getPermissionOrgs($permission, $session);
        return sizeof($permissionOrgs);
    }
	public function checkOrgPermissions($permission, $requestedOrgs, $session,$website=''){
		
		error_reporting(0);	
		
        // Admin user - always return true
        $orgsession=$this->session->get("orgsession");
        if($orgsession->data->document->jsonData->userId == 1) return true;
		
		//$allow_access=$this->getCountryLevelTemplateAuthorization();
		$allow_access=$this->getCountryAuthCheck($website->id);
		if($allow_access=="y") return true;

        // If no requested orgs - always return false as something must be wrong or this is a default site that only admin can access
        if($requestedOrgs == null || sizeof($requestedOrgs) == 0) return false;

        $permissionOrgs = $this->getPermissionOrgs($permission, $session);
        $missingOrgs = array_diff($requestedOrgs, $permissionOrgs);

        return sizeof($missingOrgs) == 0;
    }

    public function getPermissionOrgs($permission, $session){
        $permissionOrgs = [];

        if(property_exists($session->data->payload->menuPermissions, $permission)){
            $orgLists = $session->data->payload->menuPermissions->{$permission};
            foreach($orgLists as $orgList){
                $permissionOrgs = array_unique(array_merge($permissionOrgs,$session->data->payload->orgLists->{$orgList}), SORT_REGULAR);
            }
        }
        return $permissionOrgs;
    }

    /*
     *  Returns a map of countries associated with any webmaster permission for the given session data
     */
    public function getParentCountries($session){

        $sessionCountries = array();

        $allCountriesMap = $this->getCountriesMap();

        $countryIdList = $this->getPermissionOrgs($this->constants->getCountryWebsitePermission(), $session);
        foreach($countryIdList as $countryId) {
            $sessionCountries[$countryId] = $allCountriesMap[$countryId];
        }

        $allRegionsMap = $this->getRegionsMap();
        $regionIdList = $this->getPermissionOrgs($this->constants->getRegionWebsitePermission(), $session);
        foreach($regionIdList as $regionId){
            $sessionCountries[$allRegionsMap[$regionId]->parentOrgId] = $allCountriesMap[$allRegionsMap[$regionId]->parentOrgId];
        }

        $allChaptersMap = $this->getChaptersMap();
        $chapterIdList = $this->getPermissionOrgs($this->constants->getChapterWebsitePermission(), $session);
        foreach($chapterIdList as $chapterId){
            // TODO : I deeply apologise for the the almost unreadable code here - it's all good and works - but no time to make it more readable!  Maybe later!
            $sessionCountries[$allRegionsMap[$allChaptersMap[$chapterId]->parentOrgId]->parentOrgId] = $allCountriesMap[$allRegionsMap[$allChaptersMap[$chapterId]->parentOrgId]->parentOrgId];
        }

        return $sessionCountries;
    }

    /*
     *  Returns a list of regions associated with any webmaster permission for the given session data
     */
    public function getParentRegions($session){
        $sessionRegions = array();

        $allRegionsMap = $this->getRegionsMap();

        $regionIdList = $this->getPermissionOrgs($this->constants->getRegionWebsitePermission(), $session);
        foreach($regionIdList as $regionId){
            $sessionRegions[$regionId] = $allRegionsMap[$regionId];
        }

        $allChaptersMap = $this->getChaptersMap();
        $chapterIdList = $this->getPermissionOrgs($this->constants->getChapterWebsitePermission(), $session);
        foreach($chapterIdList as $chapterId){
            $sessionRegions[$allChaptersMap[$chapterId]->parentOrgId] = $allRegionsMap[$allChaptersMap[$chapterId]->parentOrgId];
        }

        return $sessionRegions;
    }
	public function getCountryAuthCheck($website_id)
	{
		$allow_access='n';
		$clevel_website=$this->session->get('countrylevel_website');
		if($clevel_website!=""):
			if(in_array($website_id,$clevel_website)): $allow_access='y'; endif;
		endif;	
		
		return $allow_access;	
		
    }

}