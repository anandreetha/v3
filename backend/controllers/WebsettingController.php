<?php

namespace Multiple\Backend\Controllers;

use Multiple\Core\Models\Website;
use Multiple\Core\Models\WebsiteSettings;
use Phalcon\Mvc\Controller;
use Phalcon\Escaper;
use Multiple\Backend\Validators\EditWebsiteSettingsValidator;

class WebsettingController extends BaseController
{
    public function editAction($websiteId)
    {
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

        $fail = false;
        $messages = array();

        if ($this->request->isPost()) {
            // Validation disabled for default sites
            if ($website->is_default != '1') {
                $validation = new EditWebsiteSettingsValidator();

                switch ($website->type_id) {
                    case '1':
                        $validation->addCountryValidators($website);
                        break;
                    case '2':
                        $validation->setFilters("fixedSettingInputLocation", "trim");
                        $validation->setFilters("fixedSettingInputExecutiveDirector", "trim");
                        $validation->addRegionValidators($website);
                        break;
                }
                $messages = $validation->validate($this->request->getPost());
            }

            if (count($messages)) {
                $errorMessage = "";
                foreach ($messages as $message) {
                    $errorMessage .= "<li>" . $message . "</li>";
                }
                $this->flashSession->error(
                    "<p>" . $this->translator->_('cms.v3.admin.settings.pleaseaction') . "<ul>" . $errorMessage .
                    "</ul></p>"
                );
                $fail = true;
            } else {
                foreach ($this->request->getPost() as $key => $value) {
                    if (0 === strpos($key, 'fixedSetting')) {
                        $settingsIds = array(
                            "fixedSettingInputLocation" => 331,
                            "fixedSettingInputExecutiveDirector" => 332,
                            "fixedSettingInputContactTelephone" => 333,
                            "fixedSettingInputContactEmail" => 334
                        );

                        switch ($key) {
                            case 'fixedSettingInputWebsiteName':
                                $website->name = $this->request->getPost($key);
                                if ($website->save() === false) {
                                    $this->flashSession->error('Setting cannot be updated');
                                    $fail = true;
                                    break;
                                }
                                break;
                            case 'fixedSettingSelectCountries':
                            case 'fixedSettingSelectRegions':
                                $websiteOrg = $this->websiteService->updateOrgs(
                                    $website,
                                    $this->request->getPost($key)
                                );
                                break;
                            case 'fixedSettingInputLocation':
                            case 'fixedSettingInputExecutiveDirector':
                            case 'fixedSettingInputContactTelephone':
                            case 'fixedSettingInputContactEmail':
                                $settingId = $settingsIds[$key];
                                $setting = WebsiteSettings::findFirst(
                                    [
                                        'website_id = :website_id: AND settings_id = :settings_id:',
                                        'bind' => [
                                            'settings_id' => $settingId,
                                            'website_id' => $websiteId
                                        ],

                                    ]

                                );
                                if ($setting != false) {
                                    $setting->value = $this->request->getPost($key);
                                    if ($setting->save() === false) {
                                        $this->flashSession->error('Website setting cannot be updated');
                                        $fail = true;
                                        break;
                                    }
                                }
                                break;
                            case 'fixedSettingSelectNewsCountries':
                            case 'fixedSettingSelectNewsRegions':
                                $websetting = $this->websiteService->getWebsiteSetting(
                                    $website,
                                    $this->constants->getNewsWebsiteSettingId($website->type_id)
                                );
                                if ($this->saveWebsiteSettingOrgList($websetting, $this->request->getPost($key))
                                    === false) {
                                    $this->flashSession->error('Setting cannot be updated');
                                    $fail = true;
                                    break;
                                }
                                break;
                        }

                        if ($fail) {
                            break;
                        }

                    } else {
                        $id = explode("/", $key);
                        $websiteSettingId = $id[1];
                        $websiteSettingId = $this->filter->sanitize($websiteSettingId, 'int');
                        $websetting = WebsiteSettings::findfirst(
                            [
                                'id = :id:',
                                'bind' => [
                                    'id' => $websiteSettingId
                                ],

                            ]

                        );

                        if ($websiteId != $websetting->getWebsite()->id) {
                            array_push($messages, "website setting does not belong to this website");
                            break;
                        }
                        if (count($messages)) {
                            foreach ($messages as $message) {
                                $this->flashSession->error($message);
                                $fail = true;
                            }
                        } else {
                            // This website setting is the javascript snippet setting.
                            // The $this->request->getPost($key, 'string') is removing the script tags
                            // So we want to check for this id, keep the script tags and sanitize javascript properly

                            switch ($websetting->getSettingsId()) {
                                case '123':
                                case '4':
                                    $escaper = new Escaper();
                                    $websetting->value = $escaper->escapeJs(
                                        $this->request->getPost("settingValue/{$websiteSettingId}")
                                    );
                                    break;
                                case '230':
                                case '6':
                                    $escaper = new Escaper();
                                    $websetting->value = $escaper->escapeHtml(
                                        $this->request->getPost("settingValue/{$websiteSettingId}")
                                    );
                                    break;
                                case '357':
                                    if (!empty(trim($this->request->getPost("settingValue/{$websiteSettingId}")))) {
                                        // Get associated website
                                        $website = $websetting->getWebsite();

                                        // And the parent website
                                        $parentWebsite = $websetting->getWebsite()->getParentWebsite();

                                        // Get the proposed custom name
                                        $fullNewFolderName = $parentWebsite->getCleanDomain() . "/" .
                                            $this->purifyFilename(
                                                strtolower(
                                                    $this->request->getPost("settingValue/{$websiteSettingId}")
                                                )
                                            );

                                        $fullNewFolderName = $this->websiteHelper->strip_accents($fullNewFolderName);

                                        // Check if the folder name string exists for any other websites
                                        $preExisting = Website::find([
                                            'clean_domain = :cleanDomain: and id != :websiteId:',
                                            'bind' => [
                                                'cleanDomain' => $fullNewFolderName,
                                                'websiteId' => $websetting->getWebsiteId()
                                            ],
                                        ]);
										
										if($website->type_id=="3"):
											$existing_website=$this->ChapterCustomFolder($website,$fullNewFolderName);
										endif;

                                        // So long as the folder name doesnt exist
                                        if (count($preExisting) <= 0) {
                                            // Carry out updates
                                            $website->setCleanDomain($fullNewFolderName);
                                            $website->setDomain($fullNewFolderName);
                                            $website->save();

                                            $websetting->value = $this->purifyFilename(
                                                strtolower(
                                                    $this->request->getPost("settingValue/{$websiteSettingId}")
                                                )
                                            );
                                        } else {
                                            $fail = true;
                                            $this->flashSession->error($this->translator->_(
                                                'cms.v3.admin.settings.customchapterfoldernameexistsmsg'
                                            ));
                                        }
                                    } else {
                                        $websiteOrg = $websetting->getWebsite()->getWebsiteOrg()->getFirst();
                                        $parentWebsite = $websetting->getWebsite()->getParentWebsite();
                                        $parentWebsiteOrg = $parentWebsite->getWebsiteOrg()->getFirst();
										
										if(isset($websiteOrg->parent_org_id)&&($websiteOrg->parent_org_id!="")):
											$parentWebsiteOrg->org_id=$websiteOrg->parent_org_id;
										endif;


                                        $orgRegion = $this->getRegionDetails(
                                            $parentWebsiteOrg->org_id
                                        );

                                        $orgRegion = reset($orgRegion);


                                        $orgChapter = $this->getChapterDetails(
                                            $parentWebsiteOrg->org_id,
                                            $websiteOrg->org_id
                                        );



                                        $orgChapter = reset($orgChapter);

                                        $defaultDomain = $this->buildChapterSiteDomain(
                                            $orgRegion,
                                            $orgChapter->orgName,
                                            $parentWebsite->getCleanDomain()
                                        );
										
										if($website->type_id=="3"):	
											$existing_website=$this->ChapterCustomFolder($website,$defaultDomain);
										endif;

                                        $website->setCleanDomain($defaultDomain);
                                        $website->setDomain($defaultDomain);
                                        $website->save();

                                        $websetting->value = "";

                                    }
                                    break;
                                default:
                                    $websetting->value = $this->request->getPost($key, 'string');
                            }

                            $dateTimeNow = new \DateTime();
                            $website = $websetting->getWebsite();
                            $website->lastModified = $dateTimeNow->format('Y-m-d H:i:s');
                            $websetting->website = $website;
                            if ($websetting->save() === false) {
                                $this->flashSession->error('Website setting cannot be updated');
                                $fail = true;
                                break;
                            }else{
								if(($websetting->setting->name=="Allow web visitors to email members")&&($website->type_id=="2")):
									$chaptersetting_value=$this->request->getPost($key, 'string');
									$chapter_id=$this->getChapterSiteList($websiteId,$chaptersetting_value,$websetting->setting->id);
								elseif(($websetting->setting->name=="Cookiebot Group ID")&&($website->type_id=="2")):	
									$chaptersetting_value=$this->request->getPost($key, 'string');
									$chapter_id=$this->getChapterSiteList($websiteId,$chaptersetting_value,$websetting->setting->id);
								elseif(($websetting->setting->name=="Allow Online Applications via Website")&&($website->type_id=="2")):	
									$chaptersetting_value=$this->request->getPost($key, 'string');									
									$chapter_id=$this->getChapterSiteList($websiteId,$chaptersetting_value,$websetting->setting->id);		
								elseif(($websetting->setting->name=="Control Chapter Leadership Section")&&($website->type_id=="2")):	
									$chaptersetting_value=$this->request->getPost($key, 'string');									
									$chapter_id=$this->getChapterSiteList($websiteId,$chaptersetting_value,$websetting->setting->id);		
								elseif(($websetting->setting->name=="Control Member List")&&($website->type_id=="2")):	
									$chaptersetting_value=$this->request->getPost($key, 'string');									
									$chapter_id=$this->getChapterSiteList($websiteId,$chaptersetting_value,$websetting->setting->id,$websetting->setting->name);		
								endif;
							}
                        }
                    }
                }
            }
        }
        if (!$fail) {
            $this->view->submitSuccessfully = true;
            $this->flashSession->success($this->translator->_('cms.v3.admin.settings.settingsupdatesuccessmsg'));
        }

        // Forward flow to the view action as this will now be aware of the post parameters and
        // retain them on validation error
        $this->dispatcher->forward(
            [
                'controller' => 'website',
                'action' => 'view',
            ]
        );
    }
	public function ChapterCustomFolder($website, $domain)
	{
		$clean_domain=$website->clean_domain;
		if($clean_domain!=$domain):
			$urlParts = explode('/',$domain);
			$oldurlParts = explode('/',$clean_domain);
			
			$olddomain = str_replace(".", "-", $urlParts[0]);			
			$tmpPath = realpath("../published/tmp") . "/chapter_custom/{$olddomain}";
			
			$publishedPath = realpath("../published/region") . "/{$olddomain}/chapters/{$oldurlParts[1]}";
			$txtPath=$tmpPath."/remove_chapter.txt";
			if(!file_exists($tmpPath)):
				mkdir($tmpPath, 0777, true);
			endif;	
			if (!file_exists($txtPath)):
				$myfile = fopen($txtPath, "w");
			endif;
			
			if(file_exists($publishedPath)):
				if($oldurlParts[1]!=""):
					$txtarr=$oldurlParts[1];
				endif;
				
				if((isset($txtarr))&&($txtarr!="")):
					$finalarr=array();
					$myfile = fopen($txtPath, "r");
					$finalarr=json_decode(fgets($myfile),true);
					$finalarr[$website->id]=$txtarr;
					
					file_put_contents($txtPath, "");
					
					$txtdata=json_encode($finalarr);
					$file = fopen($txtPath, "a");
					fwrite($file,$txtdata);
					fclose($file);	
				endif;	
			endif;
		endif;
				
	}
	private function getChapterSiteList($websiteId,$chaptersetting_value,$setting_id,$setting_name='')
	{
		$chapter_id=array();
		$websiteChapters = Website::getChapterWebsitesForRegion($websiteId);
		foreach ($websiteChapters as $chapter):
			$chapter_id[]=$chapter->getId();
		endforeach;
		if(count($chapter_id)>0):
			$chapter_ids=sprintf("'%s'", implode("', '", $chapter_id));
			
			$chapter_settings = "UPDATE website_settings SET  value= '".$chaptersetting_value."' WHERE website_id IN (".$chapter_ids.") AND settings_id=".$setting_id;
			$this->db->query($chapter_settings);
			
			if($setting_name=="Control Member List"):
				$template_set='1';
				if($chaptersetting_value=="off"): $template_set='0'; endif;
				
				$page_content_settings="UPDATE page_content_settings set value='".$template_set."' WHERE setting_id=10 AND page_content_id IN (SELECT pc.id FROM page p, page_content pc WHERE p.id=pc.page_id AND p.template='find-a-member-list' AND p.website_id IN (".$chapter_ids."))";
				$this->db->query($page_content_settings);			
			endif;
		endif;
	}
    private function saveWebsiteSettingOrgList($websiteSetting, $orgs)
    {
        $newValue = '';
        foreach ($orgs as $value) {
            $newValue = $newValue . (strlen($newValue) > 0 ? ',' : '') . $this->filter->sanitize($value, 'int');
        }
        $websiteSetting->value = $newValue;

        return $websiteSetting->save();
    }

    private function getRegionDetails($regionId)
    {
        $region = array_filter(
            $this->authenticationService->getRegions(),
            function ($org) use ($regionId) {
                return ($org->orgId == $regionId);
            }
        );
        if ($region!=null) {
            foreach ($region as $r) {
                return array($r->orgName, $r->parentOrgId);
            }
        }
    }

    private function getChapterDetails($regionId, $chapterOrgId = null)
    {
        return array_filter(
            $this->authenticationService->getChapters(),
            function ($org) use ($regionId, $chapterOrgId) {
                if (!is_null($chapterOrgId)) {
                    return ((($org->parentOrgId == $regionId  && $org->orgId==$chapterOrgId) || ($org->orgId==$chapterOrgId)) && $org->status == 1);
                } else {
                    return ($org->parentOrgId == $regionId && $org->status == 1);
                }
            }
        );
    }

    private function buildChapterSiteDomain($regionName, $chapterName, $websiteDomain)
    {
        $name = $this->purifyFilename(strtolower($regionName . '-' . $chapterName));
        $name = $this->websiteHelper->strip_accents($name);
        $url = mb_strtolower($websiteDomain);
        return $url . '/' . $name;
    }

    private function purifyFilename($str)
    {
        $str = preg_replace("/\r|\n|\t/", "",$str);
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
}
