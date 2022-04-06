<?php

namespace Multiple\Backend\Controllers;

use Multiple\Backend\Exceptions\AddLanguageDatabaseException;
use Multiple\Backend\Validators\AddWebsiteLanguageValidator;
use Multiple\Backend\Validators\EditWebsiteLanguageValidator;
use Multiple\Core\Models\Language;
use Multiple\Core\Models\Page;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageSettings;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteLanguage;
use Exception;

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

use Multiple\Core\Models\WebsiteOrg;


class WeblanguageController extends BackendBaseController
{

    const CUSTOM_PAGE_TEMPLATE = 'custom-page';

    public function indexAction()
    {

    }

    // TODO Temp copy and pasted from RenderController, move to central place after RBAC work more fleshed out
    private function getWebsiteOrgIds($websiteOrgs)
    {
        $weborgs = array();

        foreach ($websiteOrgs as $org) {
            $weborgs[] = $org->orgId;
        }

        return $weborgs;
    }

    public function addAction($websiteId)
    {
        $websiteId = $this->filter->sanitize($websiteId, 'int');

        $website = Website::findfirst(
            [
                'id = :id:',
                'bind' => [
                    'id' => $websiteId
                ],
            ]
        );

        if ($website == false || !$this->hasUserGotPermissionToAccessWebsite($website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        // Get a list of available languages for this website (i.e. based on it's selected country IDs)
        $websiteCountryIds = $this->getWebsiteOrgIds($website->getWebsiteOrg());
		
		$allow_access=$this->authenticationService->getCountryAuthCheck($website->id);
		if((count($websiteCountryIds)==0)&&($allow_access=="y")):
			$websiteCountryIds[]=$website->id_country;
		endif;


        try {
            $overallRequest = $this->client->request('GET', 'internal/languages?org-ids=' . implode(',', $websiteCountryIds), [
                'base_uri' => $this->config->bniApi->internalCoreApiUrl
            ]);
            $stringBody = $overallRequest->getBody()->getContents();

            $languageIdTokenList = json_decode($stringBody)->content;

        } catch (Exception $ex) {
            $this->logger->error("WeblanguageController: " . $ex->getMessage());
            throw $ex;
        }

        // Get existing languages for this website
        if (count($website->WebsiteLanguage) > 0) {
            $existingLanguages = array();
            foreach ($website->WebsiteLanguage as $websiteLanguage) {
                $existingLanguages[] = $websiteLanguage->language_id;
            }

            // Filter out any existing
            $availableLanguages = array();

            foreach ($languageIdTokenList as $language) {
                if (!in_array($language->id, $existingLanguages)) {
                    $language->token= $this->translator->_($language->token);

                    $availableLanguages[] = $language;
                }
            }

        } else {
            $availableLanguages = $languageIdTokenList;
        }

     //BNIDEV-4996 : In cases where the only language available to add is unselected from bni connect, available languages becomes zero leaving no choice to add that language hence validate only if count of $availableLanguages<>0
        if ($this->request->isPost() && count($availableLanguages)<>0) {

            $validation = new AddWebsiteLanguageValidator();

            $messages = $validation->validate($this->request->getPost());
            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->flash->error($message);
                }
            } else {
                $selectedLanguages = $this->request->getPost("languageValue");
                $isError = false;

                // Add website_language and then page_content* entries into the database for each selected language (or roll back all inserted data if an error occurs).
                try {
					$type_id=$website->type_id;
					$website_org_res=$website->getWebsiteOrg();
					$country_id=$this->GetCurrentCountryId($website_org_res,$type_id);
					
                    foreach ($selectedLanguages as $selectedLanguage) {
                        $selectedLanguageId = $this->filter->sanitize($selectedLanguage, 'int');

                        $websiteLanguage = new WebsiteLanguage();
                        $websiteLanguage->website_id = $websiteId;
                        $websiteLanguage->language_id = $selectedLanguageId;
                        $websiteLanguage->status = $this->request->getPost("statusValue", 'int');

                        if ($websiteLanguage->save() === false) {
                            Throw new AddLanguageDatabaseException($websiteLanguage->Language->locale_code . ' language could not be added for cms_v3.website.id '. $websiteId);
                        }

                        $pagesForWebsite = $website->getPage();
                        foreach ($pagesForWebsite as $websitePage) {

                            if ($websitePage->getTemplate() == self::CUSTOM_PAGE_TEMPLATE) {
                                $pageContantForPage = $websitePage->getPageContent();
                                $existingPageContentTemplates = PageContent::getPageContentForWebsiteAndLanguage($websitePage->getWebsiteId(),$pageContantForPage[0]->language_id,$type_id,$country_id);
                                foreach ($existingPageContentTemplates as $existingPageContentTemplate) {
                                    if($existingPageContentTemplate->Page->navOrder !== $websitePage->getNavOrder()){
                                        continue;
                                    }

                                    $this->createNewPageContent($websitePage,$existingPageContentTemplate,$selectedLanguageId,$websiteId);
                                }

                            } else {
                                $existingPageContentTemplates = PageContent::getPageContentForWebsiteAndLanguage($website->getTypeId(), $selectedLanguageId,$type_id,$country_id);

                                foreach ($existingPageContentTemplates as $existingPageContentTemplate) {

                                    if ($existingPageContentTemplate->Page->template !== $websitePage->getTemplate()) {
                                        continue;
                                    }
                                    $this->createNewPageContent($websitePage,$existingPageContentTemplate,$selectedLanguageId,$websiteId);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->critical('Something crashed when adding a new language to a website, due to exception: ' . $e->getMessage());
                    $this->flash->error($this->translator->_("cms.v3.admin.languages.erroroccurred"));

                    $this->rollbackAddLanguageTransation($websiteId, $selectedLanguages);
                    $isError = true;
                }

                if (!$isError) {
                    $this->flash->success($this->translator->_("cms.v3.admin.languages.savedsuccessfully"));
                }
            }
        }

        $this->view->availableLanguages = $this->translationUtils->sortLanguages($availableLanguages,
            function ($a){return $a->token;
        });
        $this->view->setTemplateAfter('ajax-layout');

    }
	public function GetParentOrgId($websiteOrgs)
	{
		$weborgs = array(); 
        foreach ($websiteOrgs as $org) { 
			$weborgs[] = $org->parent_org_id;
        }
		$weborgs=array_unique($weborgs);	
		return $weborgs;
	}
	public function GetCurrentCountryId($websiteOrgs,$type_id)
	{
		$country_id='';
		$weborgs=$this->GetParentOrgId($websiteOrgs);
		if(isset($weborgs[0])):
			if($type_id=="2"): $country_id=$weborgs[0]; endif;
			if($type_id=="3"):
			
				$websiteOrgnew = WebsiteOrg::findFirst(
					[
						'org_id = :org_id:',
						'bind' => [
							'org_id' => $weborgs[0]
						],
					]
				);
				$country_id=$websiteOrgnew->parent_org_id;
			endif;
		endif;	
		return $country_id;
	}
    /**
     * Create new page content for a new language based on websitePage incoming value.
     *
     * @param $websitePage
     * @param $existingPageContentTemplate
     * @param $selectedLanguageId
     * @param $websiteId
     * @throws AddLanguageDatabaseException
     */
    private function createNewPageContent($websitePage,$existingPageContentTemplate,$selectedLanguageId,$websiteId) {

        $newPageContent = new PageContent();
        $newPageContent->setPageId($websitePage->getId());
        $newPageContent->setTitle($existingPageContentTemplate->getTitle());
        $newPageContent->setNavName($existingPageContentTemplate->getNavName());
        $newPageContent->setLanguageId($selectedLanguageId);

        // Save the new page content
        if ($newPageContent->save() === false) {
            Throw new AddLanguageDatabaseException($newPageContent->title . ' content could not be created for cms_v3.website.id ' . $websiteId);
        }

        $this->websiteService->createAllPageContentAssociations($existingPageContentTemplate, $newPageContent);


    }

    private function rollbackAddLanguageTransation($websiteId, $selectedLanguageIds) {
        foreach ($selectedLanguageIds as $languageId) {
            $websiteLanguage = WebsiteLanguage::findFirst(
                [
                    'website_id = :websiteId: AND language_id = :languageId:',
                    'bind' => [
                        'websiteId' => $websiteId,
                        'languageId' => $languageId,
                    ],
                ]
            );

            if ($websiteLanguage && $websiteLanguage->delete() === false) {
                $this->logger->error('Could not delete cms_v3.website_language ID ' . $websiteLanguage->id . ' when trying to recover from an add language failure');
            }

            $matchingPageContent = PageContent::getPageContentForWebsiteAndLanguage($websiteId, $languageId);

            foreach($matchingPageContent as $pageContent) {
                if ($pageContent->delete() === false) {
                    $this->logger->error('Could not delete cms_v3.page_content ID ' . $pageContent->id . ' when trying to recover from an add language failure');
                }
            }
        }
    }

    public function editAction($websiteLanguageId)
    {
        $websiteLanguageId = $this->filter->sanitize($websiteLanguageId, 'int');
        $websiteLanguage = WebsiteLanguage::findfirst(
            [
                'id = :id:',
                'bind' => [
                    'id' => $websiteLanguageId
                ],
            ]
        );

        if ($websiteLanguage == false || !$this->hasUserGotPermissionToAccessWebsite($websiteLanguage->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }
        $websiteSettingSaved = false;

        if ($this->request->isPost()) {

            $validation = new EditWebsiteLanguageValidator();

            $messages = $validation->validate($this->request->getPost());
            if (count($messages)) {
                foreach ($messages as $message) {
                    $this->flash->error($message);
                }
            } else {
                $websiteLanguage->status = $this->request->getPost("statusValue", 'int');
                $dateTimeNow = new \DateTime();
                $website = $websiteLanguage->getWebsite();
                $website->lastModified = $dateTimeNow->format('Y-m-d H:i:s');
                $websiteLanguage->website = $website;

                $websiteSettingSaved = $websiteLanguage->save();
                if ($websiteSettingSaved === false) {
                    $this->flash->error($websiteLanguage->Language->locale_code . ' could not be updated');
                } else {
                    $this->flash->success($websiteLanguage->Language->locale_code . " status updated");
                }
            }
        }

        // If we have an xhr request then we need to respond differently to the request
        if ($this->request->isAjax()) {

            $response = new Response();
            $response->setHeader('Content-Type', 'application/json');

            if ($websiteSettingSaved === false) {
                $response->setStatusCode(400, "Bad Request");
                $response->setContent(json_encode(array('error' => 'Setting cannot be added')));
            } else {
                $response->setContent(json_encode(array('complete' => true)));
            }

            return $response;

        }
    }

}