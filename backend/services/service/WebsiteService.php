<?php

namespace Multiple\Backend\Services\Service;

use Exception;
use Multiple\Backend\Exceptions\LanguagePublishException;
use Multiple\Backend\Exceptions\WebsiteDirectoryException;
use Multiple\Backend\Validators\AddWebsiteValidator;
use Multiple\Core\Models\Page;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentAllowedWidgets;
use Multiple\Core\Models\PageContentSettings;
use Multiple\Core\Models\PageContentWidgets;
use Multiple\Core\Models\PageContentWidgetSettings;
use Multiple\Core\Models\PageHierarchy;
use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteLanguage;
use Multiple\Core\Models\WebsiteOrg;
use Multiple\Core\Models\WebsiteSettings;
use Phalcon\Mvc\User\Component;
use Phalcon\Validation\Validator\PresenceOf;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Phalcon\Db\Column;

class WebsiteService extends Component
{

    public function createWebsite($creator, $typeId)
    {
        $website = null;
        $siteName = null;
        try {
            if ($this->request->isPost()) {

                // If orgs are selected for this site - check their permissions, otherwise allow validations to send the validation message
                $requestedOrgs = $typeId == 1 ? $this->request->getPost("inputWebsiteCountryList") : $this->request->getPost("inputWebsiteRegionList");
                if($requestedOrgs != null && sizeof($requestedOrgs) > 0) {
                    // Validate that user has correct permissions for all requested orgs
                    if(!$this->authenticationService->checkOrgPermissions(
                        $this->constants->getWebsitePermission($typeId),
                        $requestedOrgs,
                        $this->session->get('session'))) throw new Exception("Invalid permissopms to create a new website for the given orgs");
                }
                $validation = new AddWebsiteValidator();

                if ($typeId == 2) {
                    $validation->add("inputWebsiteRegionList", new PresenceOf(
                        [
                            'message' => $this->translator->_('cms.v3.admin.websitecreation.newwebsiteregionrequiredvalidationmsg'),
                        ]
                    ));
                }

                $messages = $validation->validate($this->request->getPost());

                if (count($messages)) {
                    $errorMessage = "";
                    foreach ($messages as $message) {
                        $errorMessage .= "<li>" . $message . "</li>";
                    }
                    $this->flash->error("<p>".$this->translator->_('cms.v3.admin.websitecreation.newwebsitepleaseactionvalidationmsg')." <ul>" . $errorMessage . "</ul></p>");

                } else {
                    $parentOrgId = null;
                    if ($typeId == 1) {
                        $selectedOrgs = $this->request->getPost("inputWebsiteCountryList");
                    } else if ($typeId == 2) {
                        $selectedOrgs = $this->request->getPost("inputWebsiteRegionList");
                        foreach ($selectedOrgs as $region) {
                            $parentOrgId = $this->getRegionDetails($region)[1];
                        }
                    }

                    $sitedomain = $this->filter->sanitize($this->request->getPost('inputWebsiteDomain'), 'string');
                    $processRegionWebsite = $this->processWebsite($creator, $typeId, $selectedOrgs, $sitedomain, $parentOrgId);

                    if ($processRegionWebsite[0]) {

                        if ($typeId == 2) {
                            // While creating region website, the region Ids selected are iterated over to get the chapters within each region
                            $this->refreshChapterSites($selectedOrgs, $processRegionWebsite[1], $creator);
                        }
                        $this->flash->success($this->translator->_('cms.v3.admin.websitecreation.newwebsiteallwebsitesaddedsuccessmsg'));
                    } else {
                        throw new Exception("Unknown failure whilst trying to create a new website");
                    }
                }
            }

        } catch (Exception $ex) {
            $this->flash->error('Website could not be created at this time.Please try again later');
            $this->logger->debug($ex->getMessage());

            // Roll back
            $this->rollBack($website);
        }
    }

    private function refreshChapterSites($regions, $regionSite, $creator){

        $existingChapters = array();
        $newChapterIds = [];

        foreach($this->getDescendentWebsiteOrgs($regionSite) as $childWebsiteOrg){
            $existingChapters [$childWebsiteOrg->org_id] = $childWebsiteOrg;
        }

        foreach ($regions as $region) {
            $chaptersPerRegion = $this->getChapters($region);
            $regionName = $this->getRegionDetails($region)[0];

            if (count($chaptersPerRegion)) {
                foreach ($chaptersPerRegion as $chapter) {
                    $newChapterIds [] = $chapter->orgId;
                    if(!in_array($chapter->orgId, array_keys($existingChapters))) {
                        $parentOrgId = $chapter->parentOrgId;
                        $selectedOrgs = array($chapter->orgId);
                        $sitedomain = $this->buildChapterSiteDomain($regionName, $chapter->orgName, $regionSite->cleanDomain);
                        $typeId = 3;
                        $this->processWebsite($creator, $typeId, $selectedOrgs, $sitedomain, $parentOrgId);
                    }
                }
            }
        }

        // Any chapters in $existingChapters but not in $newChapters should have their websites removed.
        $chaptersToDelete = array_diff(array_keys($existingChapters), $newChapterIds);
        foreach($chaptersToDelete as $deleteChapter){
            $this->deleteWebsite($existingChapters[$deleteChapter]->Website);
        }
    }



    public function createAllPageContentAssociations($pageContent, $newPageContent)
    {
        $this->createPageContentSettings($pageContent, $newPageContent);
        $this->createPageContentAllowedWidgets($pageContent, $newPageContent);
        $this->createPageContentWidgets($pageContent, $newPageContent);
    }


    private function createNewWebsiteRecord($creator, $typeId, $sitedomain)
    {
        if ($this->request->isPost()) {
            $newWebsite = new Website();

            $newWebsite->name = $typeId == 3 ? $sitedomain[0] : $this->filter->sanitize($this->request->getPost('inputWebsiteName'), 'string');
            $newWebsite->domain = $typeId == 3 ? $sitedomain[1] : $sitedomain;
            $newWebsite->clean_domain = $typeId == 3 ? $sitedomain[1] : $sitedomain;
            $newWebsite->typeId = $typeId;
            $newWebsite->created_on = date("Y-m-d H:i:s");

            if ($typeId !== 3) {
                $input = trim($newWebsite->clean_domain, '/');
                if (!preg_match('#^http(s)?://#', $input)) {
                    $input = 'http://' . $input;
                }
                $urlParts = parse_url($input);
                $newWebsite->clean_domain = preg_replace('/^www\./', '', $urlParts['host']);
            } else {
                $newWebsite->clean_domain = $sitedomain;
            }
            $newWebsite->is_default = 0; // any created websites naturally wouldn't be a default website, of which we have 3 (id 1 = country, 2 = region, 3 = chapter)

            $dateTimeNow = new \DateTime();
            $newWebsite->lastModified = $dateTimeNow->format('Y-m-d H:i:s');
            $newWebsite->lastPublished = null;

            $newWebsite->creator = $creator;

            if ($newWebsite->save() === false) {
                $errorMessage = "";
                foreach ($newWebsite->getMessages() as $message) {
                    $errorMessage .= $message . "\n";
                }
                throw new Exception("Website record could not be created: " . $errorMessage);

            } else {
                return $newWebsite;
            }
        }
        return false;
    }


    /**
     * Do a clean publish
     * Which means deleting any existing content
     * in the published folder and then
     * re-publishing
     * @param $newWebsite
     * @return mixed
     * @throws WebsiteDirectoryException
     */
	public function createWebsiteBackup($childrenSites){
		if ($newWebsite === false) {
            return $newWebsite; // Should return false
        }
		foreach($childrenSites as $k=>$childWebsite) {
			$type = strtolower($childWebsite->getWebsiteType()->name);
			$domain = str_replace(".", "-", $childWebsite->clean_domain);
			
			$urlParts = explode('/',$domain);
			$publishedPath = realpath("../published") . "/region/{$urlParts[0]}/chapters/{$this->websiteHelper->strip_accents($urlParts[1])}";
			$tmpPath = realpath("../published/tmp") . "/region/{$urlParts[0]}/chapters/{$this->websiteHelper->strip_accents($urlParts[1])}";
			
			if(file_exists($publishedPath)):
				mkdir($tmpPath, 0777, true);
				$this->xcopy($publishedPath, $tmpPath);
			endif;	
		}
		
	}	
	public function deleteWebsiteBackup($newWebsite){
		if ($newWebsite === false) {
            return $newWebsite; // Should return false
        }

        $type = strtolower($newWebsite->getWebsiteType()->name);
        $domain = str_replace(".", "-", $newWebsite->clean_domain);
		if ($type != 'chapter') {
            $tmpPath = realpath("../published/tmp") . '/' . $type . "/" . $domain;
			if (file_exists($tmpPath)) {
				try {
					$this->_gentleDelete($tmpPath);
				} catch(\Exception $ex) {
					return new WebsiteDirectoryException('Unable to delete directory');
				}
			}
		}
	}
	public function pasteOldWebsiteDirectory($newWebsite){
		
		if ($newWebsite === false) {
            return $newWebsite; // Should return false
        }

        $type = strtolower($newWebsite->getWebsiteType()->name);
        $domain = str_replace(".", "-", $newWebsite->clean_domain);
		
		$urlParts = explode('/',$domain);
        $tmpPath = realpath("../published/tmp/") . "/region/{$urlParts[0]}/chapters/{$this->websiteHelper->strip_accents($urlParts[1])}";
		$publishedPath = realpath("../published/") . "/region/{$urlParts[0]}/chapters/{$this->websiteHelper->strip_accents($urlParts[1])}";
		if (file_exists($tmpPath)):
			$response='y';
			mkdir($publishedPath, 0777, true);
			$this->xcopy($tmpPath, $publishedPath);
		else:
			$response='n';
		endif;
		return $response;	
	} 
    public function createWebsiteDirectory($newWebsite)
    {
        if ($newWebsite === false) {
            return $newWebsite; // Should return false
        }

        $type = strtolower($newWebsite->getWebsiteType()->name);
        $domain = str_replace(".", "-", $newWebsite->clean_domain);

        if ($type === 'chapter') {
            $urlParts = explode('/',$domain);
            $publishedPath = realpath("../published/") . "/region/{$urlParts[0]}/chapters/{$this->websiteHelper->strip_accents($urlParts[1])}";
			
			$olddomain = str_replace(".", "-", $urlParts[0]);
			$txtPath=realpath("../published/tmp") . "/chapter_custom/{$olddomain}/remove_chapter.txt";
			$rename_file = fopen($txtPath, "r");
			$renamearr=json_decode(fgets($rename_file),true);
			
			
			if(count($renamearr)>0):
				$oldchapter_folder=$renamearr[$newWebsite->id];
				if($oldchapter_folder!=""):
					$publishednewPath = realpath("../published/") . '/region/' . $olddomain."/chapters/".$oldchapter_folder;
					
					if (file_exists($publishednewPath)):
						$this->_gentleDelete($publishednewPath);
						unset($renamearr[$newWebsite->id]);
						
						
						file_put_contents($txtPath, "");					
						$txtdata=json_encode($renamearr);
						$file = fopen($txtPath, "a");
						fwrite($file,$txtdata);
						fclose($file);
						
					endif;
				endif;	
			endif;
        } else {
            $publishedPath = realpath("../published/") . '/' . $type . "/" . $domain;
        }

        if(file_exists($publishedPath . '/public/chapters')) {
            if(is_link($publishedPath . '/public/chapters')) {
                unlink($publishedPath . '/public/chapters');
            }
        }

        // If any existing published data exists, then delete the contents of the published folder
        // Then delete the published path, so we can create everything from scratch ( clean publish)
        if (file_exists($publishedPath)) {
            try {
                $this->_gentleDelete($publishedPath);
            } catch(\Exception $ex) {
                return new WebsiteDirectoryException('Unable to delete directory');
            }
        }

        // Now create the publish directory recursively
        mkdir($publishedPath, 0775, true);

        // If the folder path does not exist, then we can't carry on, so we'll throw an exception
        if (!file_exists($publishedPath)) {
            throw new WebsiteDirectoryException("Website directory could not be created");
        }

        // Copy the template-structure template into the new website directory
        // We're doing this so we can update each template via cms rather than patch manually
        // Moved from initial website create, helps protect against idle sites taking space on the server
        $this->xcopy("../template-structure", $publishedPath);

        if ($type === 'region') {
            symlink($publishedPath . '/chapters', $publishedPath . '/public/chapters');
        }
    }

    private function allowedFilename($str)
    {
        $str = str_replace(' ', '-', $str);
        $str = str_replace('?', '-', $str);
        $str = str_replace('.', '-', $str);
        $str = str_replace('>', '-', $str);
        $str = str_replace('<', '-', $str);
        $str = str_replace('|', '-', $str);
        $str = str_replace('*', '-', $str);
        $str = str_replace(':', '-', $str);
        $str = str_replace(';', '-', $str);
        $str = str_replace('%', '-', $str);
        $str = str_replace('#', '-', $str);

        $str = str_replace('"', '-', $str);
        $str = str_replace("'", "-", $str);

        $str = str_replace('/', '-', $str);
        $str = str_replace('\\', '-', $str);

        return $str;
    }


    /**
     * Recursively delete a folder directory
     * By deleting each file
     * and then the empty folder
     * @param $dir
     
    private function _gentleDelete($dir) {

        // Keep an eye ball out for .htaccess files
        if (file_exists($dir . '/.htaccess')) {
            unlink($dir . '/.htaccess');
        }

        // Loop through all files in the directory
        foreach(glob($dir . '/*') as $file) {

            // If we have the . or .. pointers then we want to skip to the next iteration.
            if ($file === '.' || $file === '..') {
                continue;
            }

            if(is_dir($file)) {
                // If we have a directory then we want to Recursively come back into this function
                $this->_gentleDelete($file);
            } else {

                // If we don't have a directory then we want to delete the file we come across
                unlink($file);
            }
        }

        // Delete an empty directory
        rmdir($dir);
    }
	*/
	private function _gentleDelete($dir) {
		$it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
		$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
		foreach($it as $file) {
			if ($file->isDir()) rmdir($file->getPathname());
			else unlink($file->getPathname());
		}
		rmdir($dir);
	}

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string $source Source path
     * @param       string $dest Destination path
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    private function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    private function createNewWebsiteOrgRecord($newWebsite, $selectedOrgs,$parentOrgId)
    {
        if ($this->request->isPost() && $newWebsite != false) {
            $newWebsiteId = $newWebsite->getId();

            foreach ($selectedOrgs as $selectedOrg) {
                $websiteOrg = new WebsiteOrg();
                $websiteOrg->website_id = $newWebsiteId;
                $websiteOrg->parent_org_id = $parentOrgId;
                $websiteOrg->org_id = $this->filter->sanitize($selectedOrg, 'int');

                if ($websiteOrg->save() === false) {
                    $errorMessage = "";
                    foreach ($websiteOrg->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Website Org Record could not be created: " . $errorMessage);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Create language records for each
     * of the languages selected by the user
     * @param $newWebsite
     * @param $selectedOrgs
     * @return bool
     * @throws Exception
     */
    private function createWebsiteLanguagesRecords($newWebsite, $selectedOrgs, $all_language='')
    {
        if ($this->request->isPost() && $newWebsite != false) {

            // Website Languages - pull these from the default languages of the selected countries (i.e via the API)
			if($all_language=="y"):
				$overallRequest = $this->client->request('GET', 'internal/languages?org-ids=' . implode(',', $selectedOrgs) . '&default-languages-only=false', [
					'base_uri' => $this->config->bniApi->internalCoreApiUrl
				]);
			else:
				$overallRequest = $this->client->request('GET', 'internal/languages?org-ids=' . implode(',', $selectedOrgs) . '&default-languages-only=true', [
					'base_uri' => $this->config->bniApi->internalCoreApiUrl
				]);
			endif;
            $stringBody = $overallRequest->getBody()->getContents();

            $languageIdTokenList = json_decode($stringBody)->content;

            foreach ($languageIdTokenList as $languageIdToken) {
                $newLanguage = new WebsiteLanguage();
                $newLanguage->setWebsiteId($newWebsite->id);
                $newLanguage->setLanguageId($languageIdToken->id);
                $newLanguage->setStatus(1);// Enable the default language on site creation

                if ($newLanguage->save() === false) {
                    $errorMessage = "";
                    foreach ($newLanguage->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Website Language Record could not be created: " . $errorMessage);
                }

            }
            return true;

        }

        return false;
    }

    private function copyDefaultWebsiteSettings($defaultWebsite, $newWebsite)
    {

        if ($this->request->isPost() && $defaultWebsite != false && $newWebsite != false) {

            //Website settings
            foreach ($defaultWebsite->getWebsiteSettings() as $websiteSetting) {
                $newWebsiteSetting = new WebsiteSettings();
                $newWebsiteSetting->setWebsiteId($newWebsite->id);
                $newWebsiteSetting->setSettingsId($websiteSetting->getSettingsId());
                $newWebsiteSetting->setValue($websiteSetting->getValue());

                if ($newWebsiteSetting->save() === false) {
                    $errorMessage = "";
                    foreach ($newWebsiteSetting->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying Website Settings Record could not be created: " . $errorMessage);
                }
            }

            return true;

        }

        return false;
    }

    private function copyDefaultWebsitePagesAndContent($defaultWebsite, $newWebsite)
    {
        if ($this->request->isPost() && $defaultWebsite != false && $newWebsite != false) {

            foreach ($defaultWebsite->getPage(['enabled = 1']) as $page) {
                $newPage = new Page();
                $newPage->setWebsiteId($newWebsite->id);
                $newPage->setLastModified(date('Y-m-d H:i:s'));
                $newPage->setNavOrder($page->getNavOrder());
                $newPage->setTemplate($page->getTemplate());

                if ($newPage->save() === false) {
                    $errorMessage = "";
                    foreach ($newPage->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying Website Pages Record could not be created: " . $errorMessage);
                } else {
                    $this->copyPageContents($newWebsite, $newPage, $page);
                }

            }

            $this->createPageHierarchyAfterPageCreation($newWebsite);

            return true;

        }
        return false;
    }


    /**
     * Creates a page hierarchy for the new website, i.e. saying that 'chapter list' is a child of 'find a chapter'
     *
     * This is directly driven by pageIds, so we'll 'hardcode' this here instead of
     * doing a mapping of what's in the default content's page hierarchy
     *
     * @param $newWebsite
     */
    private function createPageHierarchyAfterPageCreation($newWebsite)
    {
        $pages = Page::find([
            'enabled = 1',
            'website_id = :website_id:',
            'bind' => [
                'website_id' => $newWebsite->getId()
            ],
        ]);

        // Get a map of template to page ID, needed for the hierarchy
        $templateToPageId = array();
        foreach ($pages as $page) {
            $templateToPageId[$page->getTemplate()] = $page->getId();
        }
        if($newWebsite->getTypeId() == 1 ) {
            $this->createCountryPageHierarchy($templateToPageId);
        } else if ($newWebsite->getTypeId() ==2){
            $this->createRegionPageHierarchy($templateToPageId);
        } else {
            $this->createChapterPageHierarchy($templateToPageId);
        }
    }

    private function createCountryPageHierarchy($templateToPageId){
        $this->createPageHierarchy($templateToPageId["advanced-chapter-search"], $templateToPageId["advanced-chapter-search-list"]);
        $this->createPageHierarchy($templateToPageId["advanced-chapter-search-list"], $templateToPageId["chapter-detail"]);
        $this->createPageHierarchy($templateToPageId["chapter-detail"], $templateToPageId["visitor-registration"]);
        $this->createPageHierarchy($templateToPageId["chapter-detail"], $templateToPageId["application-registration"]);
        $this->createPageHierarchy($templateToPageId["find-a-chapter"], $templateToPageId["core-group-details"]);
        $this->createPageHierarchy($templateToPageId["find-a-member"], $templateToPageId["find-a-member-list"]);
        $this->createPageHierarchy($templateToPageId["find-a-member-list"], $templateToPageId["find-a-member-detail"]);
        $this->createPageHierarchy($templateToPageId["event-calendar"], $templateToPageId["event-detail"]);
        $this->createPageHierarchy($templateToPageId["event-calendar"], $templateToPageId["event-registration"]);
        $this->createPageHierarchy($templateToPageId["contact"], $templateToPageId["send-message"]);
    }

    private function createRegionPageHierarchy($templateToPageId){
        $this->createPageHierarchy($templateToPageId["advanced-chapter-search"], $templateToPageId["advanced-chapter-search-list"]);
        $this->createPageHierarchy($templateToPageId["advanced-chapter-search-list"], $templateToPageId["chapter-detail"]);
        $this->createPageHierarchy($templateToPageId["chapter-detail"], $templateToPageId["visitor-registration"]);
        $this->createPageHierarchy($templateToPageId["chapter-detail"], $templateToPageId["application-registration"]);
        $this->createPageHierarchy($templateToPageId["find-a-chapter"], $templateToPageId["core-group-details"]);
        $this->createPageHierarchy($templateToPageId["find-a-member"], $templateToPageId["find-a-member-list"]);
        $this->createPageHierarchy($templateToPageId["find-a-member-list"], $templateToPageId["find-a-member-detail"]);
        $this->createPageHierarchy($templateToPageId["event-calendar"], $templateToPageId["event-detail"]);
        $this->createPageHierarchy($templateToPageId["event-calendar"], $templateToPageId["event-registration"]);
        $this->createPageHierarchy($templateToPageId["contact"], $templateToPageId["send-message"]);
    }

    private function createChapterPageHierarchy($templateToPageId){
        $this->createPageHierarchy($templateToPageId["find-a-member-list"], $templateToPageId["find-a-member-detail"]);
        $this->createPageHierarchy($templateToPageId["find-a-member-list"], $templateToPageId["send-message"]);
        $this->createPageHierarchy($templateToPageId["home"], $templateToPageId["visitor-registration"]);
        $this->createPageHierarchy($templateToPageId["home"], $templateToPageId["application-registration"]);
    }
    private function createPageHierarchy($parentPageId, $childPageId)
    {
        $pageHierarchy = new PageHierarchy();
        $pageHierarchy->setParentPageId($parentPageId);
        $pageHierarchy->setChildPageId($childPageId);

        if ($pageHierarchy->save() === false) {
            $errorMessage = "";
            foreach ($pageHierarchy->getMessages() as $message) {
                $errorMessage .= $message . "\n";
            }
            throw new Exception("Page hierarchy record could not be created: " . $errorMessage);
        }
    }


    private function copyPageContents($newWebsite, $newPage, $defaultPage)
    {
        if ($this->request->isPost() && $newWebsite != false && $newPage != false && $defaultPage != false) {

            $websiteLanguages = $newWebsite->getWebsiteLanguage();

            //Page Content
            foreach ($websiteLanguages as $websiteLanguage) {

                $matchingLanguageDefaultContent = $defaultPage->getPageContent([
                    'language_id = :langid:',
                    'bind' => [
                        'langid' => $websiteLanguage->language_id
                    ],
                ]);

                foreach ($matchingLanguageDefaultContent as $pageContent) {
                    $newPageContent = new PageContent();
                    $newPageContent->setPageId($newPage->getId());
                    $newPageContent->setTitle($pageContent->getTitle());
                    $newPageContent->setNavName($pageContent->getNavName());
                    $newPageContent->setLanguageId($pageContent->getLanguageId());

                    if ($newPageContent->save() === false) {
                        $errorMessage = "";
                        foreach ($newPageContent->getMessages() as $message) {
                            $errorMessage .= $message . "\n";
                        }
                        throw new Exception("Copying page contents from default over to new website failed : " . $errorMessage);
                    } else {
                        $this->createAllPageContentAssociations($pageContent, $newPageContent);
                    }
                }
            }

            return true;
        }

        return false;

    }


    private function createPageContentSettings($pageContent, $newPageContent)
    {

        if ($this->request->isPost() && $pageContent != false && $newPageContent != false) {

            foreach ($pageContent->getPageContentSettings() as $defaultWebsitePageContentSetting) {
                $newPageContentSetting = new PageContentSettings();
                $newPageContentSetting->setPageContentId($newPageContent->getId());
                $newPageContentSetting->setSettingId($defaultWebsitePageContentSetting->getSettingId());
                $newPageContentSetting->setValue($defaultWebsitePageContentSetting->getValue());

                if ($newPageContentSetting->save() === false) {
                    $errorMessage = "";
                    foreach ($newPageContentSetting->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying page contents settings from default over to new website failed : " . $errorMessage);
                }

            }

            return true;
        }

        return false;

    }


    private function createPageContentAllowedWidgets($pageContent, $newPageContent)
    {
        if ($this->request->isPost() && $pageContent != false && $newPageContent != false) {

            foreach ($pageContent->getAllowedWidgets() as $defaultAllowedWidget) {
                $newPageContentAllowedWidget = new PageContentAllowedWidgets();
                $newPageContentAllowedWidget->setPageContentId($newPageContent->getId());
                $newPageContentAllowedWidget->setWidgetId($defaultAllowedWidget->getWidgetId());
                $newPageContentAllowedWidget->setNoAllowedInstances($defaultAllowedWidget->getNoAllowedInstances());

                if ($newPageContentAllowedWidget->save() === false) {
                    $errorMessage = "";
                    foreach ($newPageContentAllowedWidget->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying page contents allowed widgets from default over to new website failed : " . $errorMessage);
                }

            }

            return true;
        }

        return false;
    }

    private function createPageContentWidgets($pageContent, $newPageContent)
    {
        if ($this->request->isPost() && $pageContent != false && $newPageContent != false) {

            foreach ($pageContent->getWidgets() as $defaultWidget) {
                $newPageContentWidget = new PageContentWidgets();
                $newPageContentWidget->setPageContentId($newPageContent->getId());
                $newPageContentWidget->setWidgetId($defaultWidget->getWidgetId());
                $newPageContentWidget->setWidgetOrder($defaultWidget->getWidgetOrder());

                if ($newPageContentWidget->save() === false) {
                    $errorMessage = "";
                    foreach ($newPageContentWidget->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying page content widgets from default over to new website failed : " . $errorMessage);
                } else {
                    $this->createPageContentWidgetSettings($defaultWidget, $newPageContentWidget);

                    //In the future we may need to also do the same for page-content-widget-items
                }

            }

            return true;
        }

        return false;
    }


    private function createPageContentWidgetSettings($defaultWidget, $newPageContentWidget)
    {
        if ($this->request->isPost() && $defaultWidget != false && $newPageContentWidget != false) {

            foreach ($defaultWidget->getPageContentWidgetSettings() as $defaultWidgetSetting) {
                $newPageContentWidgetSetting = new PageContentWidgetSettings();
                $newPageContentWidgetSetting->setPageContentWidgetId($newPageContentWidget->getId());
                $newPageContentWidgetSetting->setSettingId($defaultWidgetSetting->getSettingId());
                $newPageContentWidgetSetting->setValue($defaultWidgetSetting->getValue());

                if ($newPageContentWidgetSetting->save() === false) {
                    $errorMessage = "";
                    foreach ($newPageContentWidgetSetting->getMessages() as $message) {
                        $errorMessage .= $message . "\n";
                    }
                    throw new Exception("Copying page content widgets settings from default over to new website failed : " . $errorMessage);
                }

            }

            return true;
        }

        return false;
    }


    private function rollBack($website)
    {

        if ($website != false) {
            $type = strtolower($website->getWebsiteType()->name);
            $domain = $website->clean_domain;
            $path = "../published/" . $type . "/" . $domain;

            $this->cleanupDirectory($path);

            $website->delete();
        }
    }

    private function cleanupDirectory($path)
    {
        if (file_exists($path)) {
            if (!rmdir($path)) {
                return false;
            };

            return true;
        }

        return false;

    }


    public static function operationSuccessIndicator($response)
    {
        if ($response != false) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * The method accepts a region Id for the region selected from the region drop down list and returns an array of all chapters within that region
     * @param $regionId
     * @return array
     */
    private function getChapters($regionId)
    {
        return array_filter(
            $this->authenticationService->getChapters(),
            function ($e) use ($regionId) {
                return ($e->parentOrgId == $regionId && $e->status == 1);
            }
        );
    }

    /**
     * The method return the region name for a given region id, used to build the site url for chapter websites
     * @param $regionId
     * @return mixed
     */
    private function getRegionDetails($regionId)
    {
        $region = array_filter(
            $this->authenticationService->getRegions(),
            function ($e) use ($regionId) {
                return ($e->orgId == $regionId);
            }
        );
     if($region!=null) {
         foreach ($region as $r) {
             return array($r->orgName, $r->parentOrgId);
         }
     }
    }

    /**
     * The method return the chapter details for given chapter Id
     * @param $chapterId
     * @return mixed
     */
    private function getChapterDetails($chapterId)
    {
        $chapter = array_filter(
            $this->authenticationService->getChapters(),
            function ($e) use ($chapterId) {
                return ($e->orgId == $chapterId);
            }
        );
        if($chapter!=null) {
            foreach ($chapter as $c) {
                return array($c->orgName, $c->parentOrgId);
            }
        }
    }

    /**
     * @param $creator
     * @param $typeId
     * @param $selectedOrgs - array of: country IDs, region IDs or chapterID
     * @param $parentOrgId - integer, of either country ID or region ID
     * @return array
     */
    private function processWebsite($creator, $typeId, $selectedOrgs, $sitedomain, $parentOrgId)
    {

        $countrytemplate='';
		if(($typeId=="2")||($typeId=="3")):
			
			if($typeId=="2") $country_id=$parentOrgId;
			if($typeId=="3"):
				$websiteOrgnew = WebsiteOrg::findFirst(
					[
						'org_id = :org_id:',
						'bind' => [
							'org_id' => $parentOrgId
						],
					]
				);
				$country_id=$websiteOrgnew->parent_org_id;
			endif;
			
			$countrytemplate = Website::findfirst([
                'id_country = :idcountry: and type_id=:typeid:',
                'bind' => [
                    'idcountry' => $country_id,
					'typeid'=>$typeId
                ],
            ]);			
		endif;
		
		if($countrytemplate):
			$defaultWebsite=$countrytemplate;
		else:		
			$defaultWebsite = Website::findFirstById($typeId);
		endif;	
        $processIndicator = false;

        // Create the initial website record first
        $website = $this->createNewWebsiteRecord($creator, $typeId, $sitedomain);
        $processIndicator = $this->operationSuccessIndicator($website);

        // Create a new websiteOrg entry
        $websiteOrg = $this->createNewWebsiteOrgRecord($website, $selectedOrgs,$parentOrgId);
        $processIndicator = $this->operationSuccessIndicator($websiteOrg);

        // Create a new website languages entry for each selected language
        $websiteLanguages = $this->createWebsiteLanguagesRecords($website, $selectedOrgs);
        $processIndicator = $this->operationSuccessIndicator($websiteLanguages);

        // Copy over the default website settings to the newly created website
        $websiteSettings = $this->copyDefaultWebsiteSettings($defaultWebsite, $website);
        $processIndicator = $this->operationSuccessIndicator($websiteSettings);

        if($typeId != 3) {
            $newsWebsiteSetting = $this->getWebsiteSetting($website, $this->constants->getNewsWebsiteSettingId($typeId));
            $processIndicator = $this->operationSuccessIndicator($websiteSettings);
            $this->saveWebsiteSettingList($newsWebsiteSetting, $selectedOrgs);
        }

        // Copy over the default website pages to the newly created website
        $websitePages = $this->copyDefaultWebsitePagesAndContent($defaultWebsite, $website);
        $processIndicator = $this->operationSuccessIndicator($websitePages);

        return array($processIndicator, $website);
    }

    private function saveWebsiteSettingList($websiteSetting, $orgs){
        if($websiteSetting !== false){
            $websiteSetting->value = implode(',', $orgs);
            if ($websiteSetting->save() === false) {
                $errorMessage = "";
                foreach ($websiteSetting->getMessages() as $message) {
                    $errorMessage .= $message . "\n";
                }
                throw new Exception("Website record could not be created: " . $errorMessage);

            }
        }
    }

    /**
     * This method builds the chapter site name in domain/regionname - chaptername format
     * @param $regionName
     * @param $chapterName
     * @param $websiteDomain
     */
    public function buildChapterSiteDomain($regionName, $chapterName, $websiteDomain)
    {
        $name =$this->allowedFilename(strtolower($regionName . '-' . $chapterName));
        $url = mb_strtolower($websiteDomain);

        $this->sitedomain = $url . '/' . $name;
        return array($chapterName,$this->sitedomain);
    }

    public function launchCMSOrgFromCoreGroup($username,$regionId, $chapterId, $chapterName)
    {
		$default_region_id=2;	
	    $default_chapter_id=3;
		
        $regionName = $this->getRegionDetails($regionId)[0];
        $parentId = $this->getRegionDetails($regionId)[1];
        $websites = Website::getFilteredWebsites($default_region_id, array($regionId),$parentId);
        $website = null;
        if (count($websites)) {//chapter sites are launched only for already existing region websites
            $this->logger->debug(
                'Region website is present for the core group with region id : '.$regionId
            );
            $website = $websites[0];
			$sitedomain = $this->buildChapterSiteDomain($regionName, $chapterName, $website->cleanDomain);
            $processChapterWebsite = $this->processWebsite($username, $default_chapter_id, array($chapterId), $sitedomain,$regionId);
			
			$newchapterwebsites = Website::getFilteredWebsites($default_chapter_id, array($chapterId), $regionId);
			$this->ChapterEmailSettings($website,$newchapterwebsites[0]);
			
            if($processChapterWebsite){
                $this->logger->debug(
                    'Chapter website created for core group with id : '.$chapterId.', name : '.$chapterName
                );
            }
        }
        return $processChapterWebsite[1];
    }
	public function editChapterSite($param)
	{
		extract($param);
		//Edit Chapter name
		if(($chapterId!="")&&($chapterName!="")):
			$chapter           = $this->getChapterDetails($chapterId);
			if($regionId==""): $regionId=$chapter[1]; endif;
			$region=$this->getRegionDetails($regionId);
			$country_id=$region[1];
			
			$website=Website::getWebsitebyOrgId($regionId);			
			$RegionName=$website->name;
			
			$chapterwebsite=Website::getWebsitebyOrgId($chapterId);
			$sitedomain = $this->buildChapterSiteDomain($RegionName, $chapterName, $website->cleanDomain);
			$chapterwebsite->name = $sitedomain[0];
			$chapterwebsite->domain = $sitedomain[1];
			$chapterwebsite->clean_domain = $sitedomain[1];
			$chapterwebsite->last_modified = date('Y-m-d H:i:s');
			$chapterwebsite->save();
		endif;
		
		//Edit Region name
		if(($regionId!="")&&($regionName!="")): 
			$region=$this->getRegionDetails($regionId);
			if($countryId==""): $countryId=$region[1]; endif;
			
			$regionwebsite=Website::getWebsitebyOrgId($regionId);					
			$regionwebsite->name = $regionName;
			$regionwebsite->last_modified = date('Y-m-d H:i:s');
			$regionwebsite->save();
			
			$chaptersPerRegion = $this->getChapters($regionId);
			 
			//Edit Chapter domain url based on new region name
			if (count($chaptersPerRegion)):
                foreach ($chaptersPerRegion as $chapter):
					//return Website::getFilteredWebsites(3,$chaptersPerRegion,$regionId);
                    $sitedomain = $this->buildChapterSiteDomain($regionName, $chapter->orgName, $regionwebsite->cleanDomain);
                    if($sitedomain[1]!=""):
						$chapterwebsite=Website::getWebsitebyOrgId($chapter->orgId);
						$chapterwebsite->domain = $sitedomain[1];
						$chapterwebsite->clean_domain = $sitedomain[1];
						$chapterwebsite->last_modified = date('Y-m-d H:i:s');
						$chapterwebsite->save();
					endif;
                endforeach;
            endif;
		endif;		
	}
	public function moveChapterSite($username,$countryId,$regionId,$chapterId,$previousRegionId,$newRegionName){

	   
	   $default_region_id=2;	
	   $default_chapter_id=3;

       $chapter           = $this->getChapterDetails($chapterId);
       $fromregionwebsites = Website::getFilteredWebsites($default_region_id, array($previousRegionId),$countryId);
       $toregionwebsites  = Website::getFilteredWebsites($default_region_id, array($regionId),$countryId);
        if (count($fromregionwebsites)) { // if Region website is already present for the 'from region'

            $chapterwebsites = Website::getFilteredWebsites($default_chapter_id, array($chapterId), $previousRegionId); //get the chapter in the 'source region' that has to be moved

            if (count($toregionwebsites)){
                $this->moveChapterToAnotherRegion($regionId, $chapterId, $previousRegionId, $newRegionName, $toregionwebsites, $chapter);
				
				$newchapterwebsites = Website::getFilteredWebsites($default_chapter_id, array($chapterId), $regionId);
				$this->ChapterEmailSettings($toregionwebsites[0],$newchapterwebsites[0]);  
            }else{
                $this->deleteWebsite($chapterwebsites[0]);
            }
			
        }
         else{
            // no region website for the 'from region', so create chapter directly into the 'to region'
             $this->launchCMSOrgFromCoreGroup($username,$regionId,$chapterId,$chapter[0]);
         }
		
    }
	public function ChapterEmailSettings($toregionwebsites,$chapter)
	{
		$regionwebsites=json_decode(json_encode($toregionwebsites));	
		$chapterwebsites=json_decode(json_encode($chapter));
		
		$regionId=$toregionwebsites->id;
		$chapterId=$chapterwebsites->id;
		
		$query="SELECT ws.VALUE AS websitesetting,ws.settings_id,s.name as setting_name FROM website_settings ws, setting s WHERE s.id=ws.settings_id AND s.name IN ('Allow web visitors to email members','Allow Online Applications via Website','Control Chapter Leadership Section','Control Member List') AND ws.website_id=:websiteid";
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"websiteid" => $regionId
			],
			[
				"websiteid" => Column::BIND_PARAM_INT,
			]
		);		
		$result    = $data->fetchAll();
		
		foreach($result as $rs):
			$settings_id=$rs['settings_id'];
			$websitesetting=$rs['websitesetting'];
			$setting_name =$rs['setting_name'];
						
			$chapter_query = "UPDATE website_settings SET  value= '".$websitesetting."' WHERE website_id=".$chapterId." AND settings_id=".$settings_id;
			$this->db->query($chapter_query);
			
			if($setting_name=="Control Member List"):
				$template_set='1';
				if($websitesetting=="off"): $template_set='0'; endif;
				
				$page_content_settings="UPDATE page_content_settings set value='".$template_set."' WHERE setting_id=10 AND page_content_id IN (SELECT pc.id FROM page p, page_content pc WHERE p.id=pc.page_id AND p.template='find-a-member-list' AND p.website_id IN (".$chapterId."))";
				$this->db->query($page_content_settings);	
				
			endif;
			
		endforeach;		
	}
	
    public function deleteWebsite($website)
    {
        if ($website) {

            $path = "../" . $this->websiteHelper->getWebsiteDirectory($website);
            $website->name = $this->purifier->purify($website->name);

            $cleanupService = new ImageCleanupService();
            $cleanupService->deleteImagesFromWebsite($website->id);
            if ($website->delete() === false) {
             $message = $this->translator->_('cms.v3.admin.websitelist.websitecannotbedeleted');
             $deleted =false;
                return array($deleted,$message);
            } else {

                if (file_exists($path)) {
                    $this->delete_files($path);
                }

                $message= $website->name . ' ' . $this->translator->_('cms.v3.admin.websitelist.hasbeendeleted');
                $deleted= true;
                return array($deleted,$message);

            }
        } else {
           $message = $this->translator->_('cms.v3.admin.websitelist.websitedontexist');
           $deleted = false;
           return array($deleted,$message);
        }

    }

    private function delete_files($dir) {
        $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $file) {
            if (!is_link($file->getPathname()) && $file->isDir()) rmdir($file->getPathname() );
            else unlink($file->getPathname());
        }
        rmdir($dir);
    }

    /**
     * Count how many enabled and disabled languages we have
     * if we have 0 languages enabled then throw an exception
     *
     * We're currently counting the amount of enabled and disabled
     * languages to populate the error message, but this might be pointless
     * see how it goes.
     *
     * @param $websiteLanguages
     * @throws LanguagePublishException
     */
    public function validateWebsiteHasEnabledLanguage($websiteLanguages)
    {
        $statusArray = [
            'enabled' => 0,
            'disabled' => 0
        ];

        // Count how many occurrences of enabled or disabled languages we have
        foreach($websiteLanguages as $websiteLanguage) {

            if ($websiteLanguage->status == 1) {
                $statusArray['enabled'] = $statusArray['enabled'] + 1;
            } else if ($websiteLanguage->status == 2) {
                $statusArray['disabled'] = $statusArray['disabled'] + 1;
            }

        }
		$lang_status='y';
        // If there are no enabled sites then kick off
        if ($statusArray['enabled'] === 0) {
            $token = $this->translator->_('cms.v3.admin.pages.youmustenableatleastonelanguage');
            $token =  str_replace("{0}"," ".$statusArray['enabled'],$token);
            $token = str_replace("{1}",$statusArray['disabled'],$token);
            //Throw new LanguagePublishException($token);
			$lang_status='n';
        }
		return $lang_status;
    }

    public function getWebsiteSetting($website, $settingId){
        $websetting = WebsiteSettings::findFirst(
            [
                'website_id = :website_id: AND settings_id = :settings_id:',
                'bind' => [
                    'website_id' => $website->id,
                    'settings_id' => $settingId
                ],

            ]

        );

        return $websetting;
    }

    public function getWebsiteCountries($website){
        $countryList = array();

        $allCountries = $this->authenticationService->getCountryNames();

        // Get all countries restricted by user session
        $countryList = array_filter(
            array_keys($allCountries),
            function ($e) {
                return in_array($e, $this->session->get('countries'));
            }
        );

        // Restricted further to those available to be added to a website, i.e. not on another website
        $websiteOrgList = WebsiteOrg::getWebsiteOrgPerType($website->typeId);
        $orgsIdList = array();
        foreach ($websiteOrgList as $org) {
            if($website->id != $org->website_id) array_push($orgsIdList, $org->org_id);
        }
        $countryList = array_filter(
            $countryList,
            function ($e) use ($orgsIdList) {
                return !in_array($e, $orgsIdList);
            }
        );

        foreach($website->getWebsiteOrg() as $org) {
            $websiteList [] = $org->orgId;
        }

        foreach($countryList as $org) {
            $websiteCountryList [] =  $this->buildSelectableValue($org, $allCountries[$org], in_array($org, $websiteList));
        }
        return $websiteCountryList;
    }

    public function getWebsiteRegions($website){
        $regionList = array();

        foreach($website->getWebsiteOrg() as $org) {
            $websiteList [] = $org->orgId;
            $countryOrgId = $org->parent_org_id;
        }

        $allRegions = $this->authenticationService->getRegionNames($countryOrgId);

        // Get all countries restricted by user session
        $regionList = array_filter(
            array_keys($allRegions),
            function ($e) {
                return in_array($e, $this->session->get('regions'));
            }
        );

        // Restricted further to those available to be added to a website, i.e. not on another website
        $websiteOrgList = WebsiteOrg::getWebsiteOrgPerType($website->typeId);
        $orgsIdList = array();
        foreach ($websiteOrgList as $org) {
            if($website->id != $org->website_id) array_push($orgsIdList, $org->org_id);
        }
        $regionList = array_filter(
            $regionList,
            function ($e) use ($orgsIdList) {
                return !in_array($e, $orgsIdList);
            }
        );

        foreach($website->getWebsiteOrg() as $org) {
            $websiteList [] = $org->orgId;
        }

        foreach($regionList as $org) {
            $websiteRegionList [] =  $this->buildSelectableValue($org, $allRegions[$org], in_array($org, $websiteList));
        }
        return $websiteRegionList;
    }

    public function buildSelectableValue($value, $display, $selected){
        $selectable_value = new \stdClass();
        $selectable_value-> value = $value;
        $selectable_value-> display = $display;
        $selectable_value-> selected = $selected;

        return $selectable_value;
    }

    public function buildHeaderStrings($website){
        $headerStrings = new \stdClass();
        $headerStrings->name = $website->name;
        $headerStrings->tagline = 'Local Business - Global Network ®';
        return $headerStrings;
    }

    public function updateOrgs($website, $orgs){

        $orgsCopy = $orgs + [];

        // Load current website orgs
        $currentOrgs = $website->WebsiteOrg;

        // Remove current orgs NOT in new list
        foreach($currentOrgs as $org){
            $parentOrgId = $org->parent_org_id;
            if(!in_array($org->org_id, $orgs)){
                if($org->delete() === false) {
                    $this->flashSession->error('Setting cannot be updated');
                    return false;
                }
            } else {
                // Remove processed org from new org list - remaining orgs should be created below
                $orgs = array_diff($orgs, array($org->org_id));
            }
        }

        // Create any new orgs
        foreach($orgs as $org) {
            $websiteOrg = new WebsiteOrg();
            $websiteOrg->org_id = $org;
            $websiteOrg->parent_org_id = $parentOrgId;
            $websiteOrg->website_id = $website->id;

            if ($websiteOrg->save() === false) {
                $this->flashSession->error('Setting cannot be updated');
                return false;
            }
        }

        if($website->type_id == 2) {
            $this->refreshChapterSites($orgsCopy, $website, $website->creator);
        }

        return true;
    }

    /**
     * @param $regionId
     * @param $chapterId
     * @param $previousRegionId
     * @param $newRegionName
     * @param $fromregionwebsites
     * @param $chapter
     */
    public function moveChapterToAnotherRegion($regionId, $chapterId, $previousRegionId, $newRegionName, $toregionwebsites, $chapter): void
    {
        $this->logger->debug(
            'Region website is present for the region having region id : ' . $regionId
        );
        $website = $toregionwebsites[0];
        $chapterwebsites = Website::getFilteredWebsites(3, array($chapterId), $previousRegionId); //get the chapter in the 'source region' that has to be moved

        $chapterwebsite = $chapterwebsites[0];
        $sitedomain = $this->buildChapterSiteDomain($newRegionName, $chapter[0], $website->cleanDomain);
        $chapterwebsite->name = $sitedomain[0];
        $chapterwebsite->domain = $sitedomain[1];
        $chapterwebsite->clean_domain = $sitedomain[1];
        $chapterwebsite->last_modified = date('Y-m-d H:i:s');
        $chapterwebsite->save();

        $websiteorg = $chapterwebsite->getWebsiteOrg();
        $websiteorg[0]->parent_org_id = $regionId;
        $websiteorg[0]->save();
    }

    public function getDescendentWebsiteOrgs($website)
    {
        $websiteOrgList = [];
        foreach($website->WebsiteOrg as $parentWebsiteOrg) {
            $descendents = WebsiteOrg::find("parent_org_id = " . $parentWebsiteOrg->org_id);
            if(count($descendents)) {
                foreach ($descendents as $descendent) {
                    $websiteOrgList[] = $descendent;
                }
            }
        }

        return $websiteOrgList;
    }

    public function getWebsiteFromDomain($domain){
        $website = Website::findFirst(
            [
                'clean_domain = :domain:',
                'bind' => [
                    'domain' => $domain
                ],

            ]

        );

        return $website;
    }
	public function processCountryLevelWebsite($creator, $typeId,$countryid)
	{
		$cond='';$selectedOrgs=array();
		$defaultWebsite = Website::findFirstById($typeId);
        $processIndicator = false;
		
		$countryname=strtolower($_POST['countryname']);
		$countryname=strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1',htmlentities(preg_replace('/[&]/', ' and ', $countryname), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
		$default_domain=explode('.',$defaultWebsite->domain);		
		$country_domain=$default_domain[0].".".$countryname;
		
		$default_clean_domain=explode('.',$defaultWebsite->clean_domain);
		$country_clean_domain=$default_clean_domain[0].".".$countryname;
		
		$_POST['inputWebsiteName']=$defaultWebsite->name;
		
		 // Create the initial website record first
        $website = $this->createNewWebsiteRecord($creator, $typeId, $country_domain);
        $processIndicator = $this->operationSuccessIndicator($website);
		
		$cond=",name='".$defaultWebsite->name."',domain='".$country_domain."',clean_domain='".$country_clean_domain."'";
		
		
		$phql = "UPDATE website SET id_country = '".$countryid."',is_default=1,publish_status='off' ".$cond." WHERE id =".$website->id;
		$this->db->query($phql);
		
		//$this->CopyDefaultWebsiteLanguage($defaultWebsite, $website);
		$selectedOrgs[] = $countryid;
		$websiteLanguages = $this->createWebsiteLanguagesRecords($website, $selectedOrgs,'y');
		$processIndicator = $this->operationSuccessIndicator($websiteLanguages);
		
		 // Copy over the default website settings to the newly created website
        $websiteSettings = $this->copyDefaultWebsiteSettings($defaultWebsite, $website);
        $processIndicator = $this->operationSuccessIndicator($websiteSettings);
		
		// Copy over the default website pages to the newly created website
        $websitePages = $this->copyDefaultWebsitePagesAndContent($defaultWebsite, $website);
        $processIndicator = $this->operationSuccessIndicator($websitePages);
		
		return array($processIndicator, $defaultWebsite);
	}
	
	function CopyDefaultWebsiteLanguage($defaultWebsite, $newWebsite)
	{
		$query="SELECT language_id,status FROM website_language l WHERE l.website_id=:websiteid";
		
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"websiteid" => $defaultWebsite->id
			],
			[
				"websiteid" => Column::BIND_PARAM_INT,
			]
		);
		
		$result    = $data->fetchAll();
		
		foreach($result as $rs):		
			$languageId=$rs['language_id'];
			$status=$rs['status'];
			$newLanguage = new WebsiteLanguage();
			$newLanguage->setWebsiteId($newWebsite->id);
			$newLanguage->setLanguageId($languageId);
			$newLanguage->setStatus($status);// Enable the default language on site creation

			if ($newLanguage->save() === false) {
				$errorMessage = "";
				foreach ($newLanguage->getMessages() as $message) {
					$errorMessage .= $message . "\n";
				}
				throw new Exception("Website Language Record could not be created: " . $errorMessage);
			}				
		endforeach;	
		
	}
}