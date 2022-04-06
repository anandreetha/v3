<?php
/**
 * Created by PhpStorm.
 * User: shabnam.sidhik
 * Date: 02/11/2017
 * Time: 10:57
 */

namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteOrg;
use Phalcon\Exception;

class ManagechaptersController extends Controller
{

    public function callchaptersAction($username, $regionId, $chapterId, $chapterName)
    {
        if ($this->request->isPost()) {
            try {
                $chapterWebsite = $this->websiteService->launchCMSOrgFromCoreGroup(
                    $username,
                    $regionId,
                    $chapterId,
                    $chapterName
                );
            } catch (Exception $e) {
                $this->logger->error(
                    'Could not launch core group ,' . $e->getMessage()
                );
            }
            $this->response->setContentType('application/json', 'UTF-8');
            $this->response->setContent(json_encode($chapterWebsite));
            return $this->response;
        }
        return false;
    }

    public function dropChapterAction($countryId, $regionId, $chapterId)
    {
        $website = Website::getFilteredWebsites(3, array($chapterId));
        $this->websiteService->deleteWebsite($website[0]);
        return false;
    }
	public function editchaptersAction()
	{
		$this->response->setContentType('application/json', 'UTF-8');
		$param=$this->request->getPost();
		$chapterId=$this->request->getPost('chapterId');
		$regionId=$this->request->getPost('regionId');
		if ($chapterId||$regionId) {
			try {
                $jsonString=$this->websiteService->editChapterSite($param);
			} catch (Exception $e) {
				$jsonString=array("error");
                $this->logger->error(
                    'Could not rename chapter, ' . $chapterId . ' : ' . $e->getMessage()
                );
            }
		}
		
		$jsonString=json_encode($jsonString);
		$this->response->setContent($jsonString);
		return $this->response;
	}
    public function movechaptersAction($username, $countryId, $regionId, $chapterId, $previousRegionId, $newRegionName)
    {
        if ($this->request->isPost()) {
            try {
                $this->websiteService->moveChapterSite(
                    $username,
                    $countryId,
                    $regionId,
                    $chapterId,
                    $previousRegionId,
                    $newRegionName
                );
            } catch (Exception $e) {
                $this->logger->error(
                    'Could not move chapter, ' . $chapterId . ' : ' . $e->getMessage()
                );
            }
        }
        return false;
    }

    public function getregionwebsitebuttonAction($regionId)
    {

        $this->view->disable();
        $this->response->setContentType('application/json', 'UTF-8');

        $websiteOrg = $this->getWebsiteOrg($regionId);

        if ($websiteOrg != false) {
            $website = $websiteOrg->getWebsite();
            $siteUrl = $website->last_published != null ? $website->clean_domain : false;
            $jsonString = json_encode((array('siteUrl' => $siteUrl)));
            $this->response->setContent($jsonString);
        } else {
            $this->response->setContent(json_encode((array('siteUrl' => false))));
        }
        return $this->response;
    }

    public function getWebsiteDetailsAction($chapterId, $countryId)
    {

        $this->view->disable();
        $this->response->setContentType('application/json', 'UTF-8');

        // Firstly get the country URL (if it exists and is published)
        $countryWebsiteUrl = false;
        $countryLanguageArray = array();

        $countryWebsiteOrg = $this->getWebsiteOrg($countryId);

        if ($countryWebsiteOrg != false) {
            $countryWebsite = $countryWebsiteOrg->getWebsite();
            $countryLanguageArray = $this->getWebsiteLanguages($countryWebsite);
            $countryWebsiteUrl = ($countryWebsite->last_published != null && $countryWebsite->apache_entry_added == "Y") ? $countryWebsite->clean_domain : false;
        }

        // Also look up the chapter URL (if it exists)
        $chapterWebsiteOrg = $this->getWebsiteOrg($chapterId);
        if ($chapterWebsiteOrg != false) {
            // We have a chapter site, hence will have a region site
            $regionWebsiteOrg = $this->getWebsiteOrg($chapterWebsiteOrg->getParentOrgId());
            $regionWebsite = $regionWebsiteOrg->getWebsite();
            $regionSiteUrl = ($regionWebsite->last_published != null && $regionWebsite->apache_entry_added == "Y") ? $regionWebsite->clean_domain : false;

            $regionLanguageArray = $this->getWebsiteLanguages($regionWebsite);

            $jsonString = json_encode((array(
                'siteUrl' => $regionSiteUrl ? $regionSiteUrl : $countryWebsiteUrl, // use the country website URL if the region one isn't available for any reason (e.g. if it's unpublished)
                'defaultLocale' => $regionSiteUrl ? $regionLanguageArray[0] : $countryLanguageArray[0],
                'availableLocales' => $regionSiteUrl ? $regionLanguageArray : $countryLanguageArray)));
            $this->response->setContent($jsonString);
        } else {
                $countryWebsiteUrl = ($countryWebsite->last_published != null && $countryWebsite->apache_entry_added == "Y") ? $countryWebsite->clean_domain : false;

                $jsonString = json_encode((array(
                    'siteUrl' => $countryWebsiteUrl,
                    'defaultLocale' => $countryLanguageArray[0],
                    'availableLocales' => $countryLanguageArray)));
                $this->response->setContent($jsonString);
        }
        return $this->response;
    }

    private function getWebsiteLanguages($website) {
        $languageArray = array();
        $websiteLanguages = $website->getWebsiteLanguage();

        foreach ($websiteLanguages as $websiteLanguage) {
            if ($websiteLanguage->status == 1) {
                array_push($languageArray, $websiteLanguage->getLanguage()->locale_code);
            }
        }

        return $languageArray;
    }

    /**
     * @param $orgId
     * @return \Multiple\Core\Models\WebsiteLanguage|\Phalcon\Mvc\Model\ResultInterface
     */
    private function getWebsiteOrg($orgId)
    {
        $websiteOrg = WebsiteOrg::findFirst(
            [
                'org_id = :org_id:',
                'bind' => [
                    'org_id' => $orgId
                ],
            ]
        );
        return $websiteOrg;
    }

}
