<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 19/09/2017
 * Time: 15:36
 */


namespace Multiple\Backend\Controllers;

use MongoDB\Driver\Query;
use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentSettings;
use Multiple\Core\Validators\Custom\ImageDimensionValidator;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;

class PagecontentController extends BackendBaseController

{

    public function editAction()
    {
        // We only want the view for this action rendered
        $this->view->setRenderLevel(
            View::LEVEL_ACTION_VIEW
        );
    }

    public function editChapterBoxAction($pageContentId)
    {

        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $currentSettings = $pageContent->getPageContentSettings();
		
		
		$chapterButtonTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_GETINVITED_BUTTON_TEXT", $currentSettings, $pageContent->id);
		$chapterHeadTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_GETINVITED_HEADING_TEXT", $currentSettings, $pageContent->id);
		$chapterStep1TextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_GETINVITED_STEP1_TEXT", $currentSettings, $pageContent->id);
		$chapterStep2TextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_GETINVITED_STEP2_TEXT", $currentSettings, $pageContent->id);
		$chapterStep3TextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_GETINVITED_STEP3_TEXT", $currentSettings, $pageContent->id);
		
        $chapterHeadingTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_HEADING_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
       // $chapterSubHeadingTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_SUB_HEADING_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        //$chapterContentTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_CONTENT_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $chapterInputTextSetting = $this->settingsFactory->getPageContentSetting("CHAPTER_INPUT_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);

        if ($this->request->isPost()) {

            //todo some extra validation here
            $savedsuccesfully = true;
            $chapterHeadingTextSetting->setValue(trim($this->request->getPost('chapterHeadingText', 'string')));
            if (!$chapterHeadingTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.enterchapterheading") . $this->request->getPost('chapterHeadingText', 'string'));
            }
			
			/*
            $chapterSubHeadingTextSetting->setValue(trim($this->request->getPost('chapterSubHeadingText', 'string')));
            if (!$chapterSubHeadingTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.enterchaptersubheading") . $this->request->getPost('chapterSubHeadingText', 'string'));
            }

            $chapterContentTextSetting->setValue(trim($this->request->getPost('chapterContentText', 'string')));
            if (!$chapterContentTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.enterchaptercontent") . $this->request->getPost('chapterContentText', 'string'));
            }
			*/
            $chapterInputTextSetting->setValue(trim($this->request->getPost('chapterInputText', 'string')));
            if (!$chapterInputTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.enterchapterinput") . $this->request->getPost('chapterInputText', 'string'));
            }
			
			
			$chapterButtonTextSetting->setValue(trim($this->request->getPost('chapterButtonText', 'string')));
            if (!$chapterButtonTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.fagetinvitedbuttontext") . $this->request->getPost('chapterButtonText', 'string'));
            }
			$chapterHeadTextSetting->setValue(trim($this->request->getPost('chapterHeadText', 'string')));
            if (!$chapterHeadTextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.fagetinvitedheadingtext") . $this->request->getPost('chapterHeadText', 'string'));
            }
			$chapterStep1TextSetting->setValue(trim($this->request->getPost('chapterStep1Text', 'string')));
            if (!$chapterStep1TextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.fagetinvitedstep1text") . $this->request->getPost('chapterStep1Text', 'string'));
            }
			$chapterStep2TextSetting->setValue(trim($this->request->getPost('chapterStep2Text', 'string')));
            if (!$chapterStep2TextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.fagetinvitedstep2text") . $this->request->getPost('chapterStep2Text', 'string'));
            }
			$chapterStep3TextSetting->setValue(trim($this->request->getPost('chapterStep3Text', 'string')));
            if (!$chapterStep3TextSetting->save()) {
                $savedsuccesfully = false;
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.fagetinvitedstep3text") . $this->request->getPost('chapterStep3Text', 'string'));
            }
			
			
			
			
            if ($savedsuccesfully == true) {

                // Set the update time
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.savedsuccessfully"));
            }

        }

        $this->view->chapterHeadingText = $chapterHeadingTextSetting->getValue();
        //$this->view->chapterSubHeadingText = $chapterSubHeadingTextSetting->getValue();
        //$this->view->chapterContentText = $chapterContentTextSetting->getValue();
        $this->view->chapterInputText = $chapterInputTextSetting->getValue();
		
		$this->view->chapterButtonText = $chapterButtonTextSetting->getValue();
		$this->view->chapterHeadText = $chapterHeadTextSetting->getValue();
		$this->view->chapterStep1Text = $chapterStep1TextSetting->getValue();
		$this->view->chapterStep2Text = $chapterStep2TextSetting->getValue();
		$this->view->chapterStep3Text = $chapterStep3TextSetting->getValue();

        // We only want the view for this action rendered
        $this->view->setTemplateAfter('ajax-layout');
    }

    public function editHeaderTopAction($pageContentId)
    {

        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $websiteType = $pageContent->getPage()->getWebsite()->getWebsiteType()->getId();
        // Get the page content settings, then get the account login text, bni international text and bni connect text
        $currentSettings = $pageContent->getPageContentSettings();
        $accountLoginTextSetting = $this->settingsFactory->getPageContentSetting("HEADER_LOGIN_TEXT", $currentSettings, $pageContent->id);
        $bniInternationalTextSetting = $this->settingsFactory->getPageContentSetting("HEADER_INTERNATIONAL_TEXT", $currentSettings, $pageContent->id);
        $bniConnectTextSetting = $this->settingsFactory->getPageContentSetting("HEADER_CONNECT_TEXT", $currentSettings, $pageContent->id);
		$bniBusinessbuilderTextSetting = $this->settingsFactory->getPageContentSetting("HEADER_BUSINESSBUILDER_TEXT", $currentSettings, $pageContent->id);
		
        if ($websiteType == '3') {
            $regionWebsiteTextSetting = $this->settingsFactory->getPageContentSetting("HEADER_REGION_WEBSITE_TEXT", $currentSettings, $pageContent->id);
        }

        $accountLoginPostText = $this->request->getPost('accountLoginInput', 'string');
        $bniInternationalPostText = $this->request->getPost('bniInternationalInput', 'string');
        $bniConnectInputPostText = $this->request->getPost('bniConnectInput', 'string');
        $bniBusinessbuilderPostText = $this->request->getPost('bniBusinessbuilderInput', 'string');
		if ($websiteType == '3') {
            $regionWebsiteInputPostText = $this->request->getPost('regionWebsiteInput', 'string');
        }
		
        if ($this->request->isPost()) {
            try {

                /** @var $pageData \Multiple\Core\Models\Page */
                $pageData = $pageContent->getPage();

                // Update the account login text in all pages ( It's a hack while we don't have a single website wide setting)
                $this->_updateAllRows($accountLoginPostText, $accountLoginTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($bniInternationalPostText, $bniInternationalTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($bniConnectInputPostText, $bniConnectTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
				$this->_updateAllRows($bniBusinessbuilderPostText, $bniBusinessbuilderTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
				
                if ($websiteType == '3') {
                    $this->_updateAllRows($regionWebsiteInputPostText, $regionWebsiteTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                }
                // Set the time the page was last updated
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));

            } catch (\Exception $ex) {
                $this->flash->error($ex->getMessage());
            }
        }

        $this->view->accountLoginText = ($accountLoginPostText === null ? $accountLoginTextSetting->getValue() : $accountLoginPostText);
        $this->view->bniInternationalText = ($bniInternationalPostText === null ? $bniInternationalTextSetting->getValue() : $bniInternationalPostText);
        $this->view->bniConnectText = ($bniConnectInputPostText === null ? $bniConnectTextSetting->getValue() : $bniConnectInputPostText);
		$this->view->bniBusinessbuilderText = ($bniBusinessbuilderPostText === null ? $bniBusinessbuilderTextSetting->getValue() : $bniBusinessbuilderPostText);
        if ($websiteType == '3') {
            $this->view->regionWebsiteText = ($regionWebsiteInputPostText === null ? $regionWebsiteTextSetting->getValue() : $regionWebsiteInputPostText);
        }

        $this->view->setTemplateAfter('ajax-layout');
    }

    public function editHeadingAction($pageContentId)
    {

        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $currentSettings = $pageContent->getPageContentSettings();
        $headingTextSetting = $this->settingsFactory->getPageContentSetting("HEADING_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);

        if ($this->request->isPost()) {

            //todo some extra validation here

            $headingTextSetting->setValue(trim($this->request->getPost('headingText', 'string')));
            if ($headingTextSetting->save()) {

                // Set the update time
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.savedsuccessfully"));
            } else {
                $this->flash->error($this->translator->_("cms.v3.admin.editpage.enterpageheading") . $this->request->getPost('headingText', 'string'));
            }

        }

        $headingText = $headingTextSetting->getValue();
		if(($pageContent->Page->template=="cookiebot-declaration")&&($headingText=="")):
			$headingText=$pageContent->title;
		endif;
        $this->view->headingText = $headingText;

        // We only want the view for this action rendered
        $this->view->setTemplateAfter('ajax-layout');
    }


    public function editCookiePolicyAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $currentSettings = $pageContent->getPageContentSettings();

        $cookiePolicyMsgTxtSetting = $this->settingsFactory->getPageContentSetting("COOKIEPOLICY_MSG_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $cookiePolicyLinkTxtSetting = $this->settingsFactory->getPageContentSetting("COOKIEPOLICY_LINK_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $cookiePolicyBtnTxtSetting = $this->settingsFactory->getPageContentSetting("COOKIEPOLICY_BTN_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);

        $cookiePolicyMsgTxtPostValue = $this->request->getPost('cookiePolicyMsgTxt', 'string');
        $cookiePolicyLinkTxtPostValue = $this->request->getPost('cookiePolicyLinkTxt', 'string');
        $cookiePolicyBtnTxtPostValue = $this->request->getPost('cookiePolicyBtnTxt', 'string');

        if ($this->request->isPost()) {

            //todo some extra validation here

            try {

                $pageData = $pageContent->getPage();

                // Update link text and value separately
                $this->_updateAllRows($cookiePolicyMsgTxtPostValue, $cookiePolicyMsgTxtSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($cookiePolicyLinkTxtPostValue, $cookiePolicyLinkTxtSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($cookiePolicyBtnTxtPostValue, $cookiePolicyBtnTxtSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));

            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {
                $this->view->cookiePolicyMsgTxt = $cookiePolicyMsgTxtPostValue;
                $this->view->cookiePolicyLinkTxt = $cookiePolicyLinkTxtPostValue;
                $this->view->cookiePolicyBtnTxt = $cookiePolicyBtnTxtPostValue;

                // We only want the view for this action rendered
                $this->view->setTemplateAfter('ajax-layout');
            }
        }

        $this->view->cookiePolicyMsgTxt = ($cookiePolicyMsgTxtPostValue === null ? $cookiePolicyMsgTxtSetting->getValue() : $cookiePolicyMsgTxtPostValue);
        $this->view->cookiePolicyLinkTxt = ($cookiePolicyLinkTxtPostValue === null ? $cookiePolicyLinkTxtSetting->getValue() : $cookiePolicyLinkTxtPostValue);
        $this->view->cookiePolicyBtnTxt = ($cookiePolicyBtnTxtPostValue === null ? $cookiePolicyBtnTxtSetting->getValue() : $cookiePolicyBtnTxtPostValue);

        // We only want the view for this action rendered
        $this->view->setTemplateAfter('ajax-layout');
    }


    public function editFeedAction($pageContentId)
    {

        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $currentSettings = $pageContent->getPageContentSettings();
        $latestTextSetting = $this->settingsFactory->getPageContentSetting("FEED_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $latestLinkTextSetting = $this->settingsFactory->getPageContentSetting("FEED_TEXT_LINK_SETTING_VALUE", $currentSettings, $pageContent->id);
        $visibilitySetting = $this->settingsFactory->getPageContentSetting("FEED_DISPLAY_SETTING_VALUE", $currentSettings, $pageContent->id);

        $latestTextPostValue = $this->request->getPost('latestText', 'string');
        $latestLinkTextPostValue = $this->request->getPost('latestLinkText', 'string');
        $rssVisibilityPostValue = $this->request->getPost('rssVisibilityInput', 'int');

        // TODO: some extra validation here
        if ($this->request->isPost()) {

            try {

                $pageData = $pageContent->getPage();

                // Update link text and value separately
                $this->_updateAllRows($latestTextPostValue, $latestTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($latestLinkTextPostValue, $latestLinkTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($rssVisibilityPostValue, $visibilitySetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));

            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {

                $this->view->latestText = $latestTextPostValue;
                $this->view->latestLinkText = $latestLinkTextPostValue;
                $this->view->rssVisible = $rssVisibilityPostValue;

                // We only want the view for this action rendered
                $this->view->setTemplateAfter('ajax-layout');
            }
        }

        // Checking this means we don't show previous database data on a post update
        $this->view->latestText = ($latestTextPostValue === null ? $latestTextSetting->getValue() : $latestTextPostValue);
        $this->view->latestLinkText = ($latestLinkTextPostValue === null ? $latestLinkTextSetting->getValue() : $latestLinkTextPostValue);
        $this->view->rssVisible = ($rssVisibilityPostValue === null ? $visibilitySetting->getValue() : $rssVisibilityPostValue);

        $this->view->setTemplateAfter('ajax-layout');
    }


    public function editNewsletterAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

        $currentSettings = $pageContent->getPageContentSettings();
		
		

		$newsletterTitleSetting = $this->settingsFactory->getPageContentSetting("NEWSLETTER_TITLE_SETTING_VALUE", $currentSettings, $pageContent->id);
		$newsletterAltSetting = $this->settingsFactory->getPageContentSetting("NEWSLETTER_ALT_SETTING_VALUE", $currentSettings, $pageContent->id);
		$newsletterTextSetting = $this->settingsFactory->getPageContentSetting("NEWSLETTER_TEXT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $newsletterInputTextSetting = $this->settingsFactory->getPageContentSetting("NEWSLETTER_TEXT_INPUT_SETTING_VALUE", $currentSettings, $pageContent->id);
		
		//print"<pre>";print_r($currentSettings);print"</pre>";exit;

		$newsletterTitlePostValue = $this->request->getPost('newsletterTitle', 'string');
        $newsletterAltPostValue = $this->request->getPost('newsletterAlt', 'string');
		$newsletterTextPostValue = $this->request->getPost('newsletterText', 'string');
        $newsletterInputTextPostValue = $this->request->getPost('newsletterInputText', 'string');

        if ($this->request->isPost()) {

            //todo some extra validation here

            try {

                $pageData = $pageContent->getPage();

                // Update link text and value separately
				$this->_updateAllRows($newsletterTitlePostValue, $newsletterTitleSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($newsletterAltPostValue, $newsletterAltSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
				$this->_updateAllRows($newsletterTextPostValue, $newsletterTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $this->_updateAllRows($newsletterInputTextPostValue, $newsletterInputTextSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                $pageContent->save();

                $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));

            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {

				$this->view->latestTitle = $newsletterTitlePostValue;
                $this->view->latestText = $newsletterTextPostValue;
                $this->view->latestLinkText = $newsletterInputTextPostValue;

                // We only want the view for this action rendered
                $this->view->setTemplateAfter('ajax-layout');
            }
        }

		$this->view->newsletterTitle = ($newsletterTitlePostValue === null ? $newsletterTitleSetting->getValue() : $newsletterTitlePostValue);
        $this->view->newsletterAlt = ($newsletterAltPostValue === null ? $newsletterAltSetting->getValue() : $newsletterAltPostValue);
		$this->view->newsletterText = ($newsletterTextPostValue === null ? $newsletterTextSetting->getValue() : $newsletterTextPostValue);
        $this->view->newsletterInputText = ($newsletterInputTextPostValue === null ? $newsletterInputTextSetting->getValue() : $newsletterInputTextPostValue);

        // We only want the view for this action rendered
        $this->view->setTemplateAfter('ajax-layout');

    }

    public function editPodcastAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

		$currentSettings = $pageContent->getPageContentSettings();
        $podcastTitleSetting = $this->settingsFactory->getPageContentSetting("PODCAST_CONTENT_TITLE_SETTING_VALUE", $currentSettings, $pageContent->id);
        $podcastTitlePostValue = $this->request->getPost('contentTitle', 'string');

		$podcastAltSetting = $this->settingsFactory->getPageContentSetting("PODCAST_CONTENT_ALT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $podcastAltPostValue = $this->request->getPost('contentAlt', 'string');
		
        $currentSettings = $pageContent->getPageContentSettings();
        $podcastUrlSetting = $this->settingsFactory->getPageContentSetting("PODCAST_CONTENT_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $podcastUrlPostValue = $this->request->getPost('contentUrl', 'string');

        $podcastImgUrlSetting = $this->settingsFactory->getPageContentSetting("PODCAST_CONTENT_IMG_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $podcastImgUrlPostValue = $this->request->getPost('customimgpath', 'string');

        if ($this->request->isPost()) {

            try {
                $imgDimensionValidator = new ImageDimensionValidator(421,652); //old 604,695

                if(empty(trim($podcastUrlPostValue)) || empty(trim($podcastImgUrlPostValue))){
                    $this->flash->error($this->translator->_("cms.v3.admin.editpage.valuesforallfieds"));
                }else {

                    if ($imgDimensionValidator->validateImageDimensions($podcastImgUrlPostValue)) {

                        $pageData = $pageContent->getPage();
						$this->_updateAllRows($podcastTitlePostValue, $podcastTitleSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
						$this->_updateAllRows($podcastAltPostValue, $podcastAltSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
						$this->_updateAllRows($podcastUrlPostValue, $podcastUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $this->_updateAllRows($podcastImgUrlPostValue, $podcastImgUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                        $pageContent->save();

                        $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));
                    } else {
						$imgdimensionsvalidationmsgnew=$this->translator->_("cms.v3.admin.editpage.invalidImageDimensionsnew");
						$imgmsg=str_replace("{0}","652",$imgdimensionsvalidationmsgnew);										
						$imgmsg=str_replace("{1}","421",$imgmsg);	
						
                        $this->flash->error($imgmsg);
                    }
                }

            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {
				$this->view->customAlt = $podcastAltPostValue;
				$this->view->customTitle = $podcastTitlePostValue;
                $this->view->contentUrl = $podcastUrlPostValue;
                $this->view->customimgpath = $podcastImgUrlPostValue;
            }
        }

        // Checking this means we don't show previous database data on a post update
		$this->view->customAlt = ($podcastAltPostValue === null ? $podcastAltSetting->getValue() : $podcastAltPostValue);
		$this->view->customTitle = ($podcastTitlePostValue === null ? $podcastTitleSetting->getValue() : $podcastTitlePostValue);
        $this->view->contentUrl = ($podcastUrlPostValue === null ? $podcastUrlSetting->getValue() : $podcastUrlPostValue);
        $this->view->customimgpath = ($podcastImgUrlPostValue === null ? $podcastImgUrlSetting->getValue() : $podcastImgUrlPostValue);
        $this->view->contentTitle = $this->translator->_('cms.v3.admin.editpage.editpodcastwidgetheading');
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.editpage.subheading');
        $redirectToUrl = $this->tag->linkTo(
            array(
                "backend/page/editPage/" . $pageContentId,
                '<i class="fa fa-arrow-left fa-lg pull-right"></i>',
                "class" => "newPageModal btn btn-default",
                "title" => $this->translator->_('cms.v3.admin.pages.backpage')
            )
        );
        $this->view->websiteId = $pageContent->getPage()->website_id;
        $this->view->redirectToUrl = $redirectToUrl;

    }

    public function editFoundationAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

		
        $currentSettings = $pageContent->getPageContentSettings();
        $foundationTitleSetting = $this->settingsFactory->getPageContentSetting("FOUNDATION_CONTENT_TITLE_SETTING_VALUE", $currentSettings, $pageContent->id);
        $foundationTitlePostValue = $this->request->getPost('contentTitle', 'string');
		
		$foundationAltSetting = $this->settingsFactory->getPageContentSetting("FOUNDATION_CONTENT_ALT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $foundationAltPostValue = $this->request->getPost('contentAlt', 'string');

        $currentSettings = $pageContent->getPageContentSettings();
        $foundationUrlSetting = $this->settingsFactory->getPageContentSetting("FOUNDATION_CONTENT_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $foundationUrlPostValue = $this->request->getPost('contentUrl', 'string');

        $foundationImgUrlSetting = $this->settingsFactory->getPageContentSetting("FOUNDATION_CONTENT_IMG_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $foundationImgUrlPostValue = $this->request->getPost('customimgpath', 'string');

        if ($this->request->isPost()) {
            try {

                if(empty(trim($foundationUrlPostValue)) || empty(trim($foundationImgUrlPostValue))){
                    $this->flash->error($this->translator->_("cms.v3.admin.editpage.valuesforallfieds"));
                }

                    $imgDimensionValidator = new ImageDimensionValidator(421, 652);

                    if ($imgDimensionValidator->validateImageDimensions($foundationImgUrlPostValue)) {
                        $pageData = $pageContent->getPage();
						$this->_updateAllRows($foundationTitlePostValue, $foundationTitleSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $this->_updateAllRows($foundationAltPostValue, $foundationAltSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
						$this->_updateAllRows($foundationUrlPostValue, $foundationUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $this->_updateAllRows($foundationImgUrlPostValue, $foundationImgUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                        if ($pageContent->save() !== false) {
                            $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));
                        }
                    } else {
						$imgdimensionsvalidationmsgnew=$this->translator->_("cms.v3.admin.editpage.invalidImageDimensionsnew");
						$imgmsg=str_replace("{0}","652",$imgdimensionsvalidationmsgnew);										
						$imgmsg=str_replace("{1}","421",$imgmsg);
						
                        $this->flash->error($imgmsg);
                    }


            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {

				$this->view->customTitle = $foundationTitlePostValue;
				$this->view->customAlt = $foundationAltPostValue;
                $this->view->contentUrl = $foundationUrlPostValue;
                $this->view->customimgpath = $foundationImgUrlPostValue;
            }
        }

        // Checking this means we don't show previous database data on a post update
		$this->view->customTitle = ($foundationTitlePostValue === null ? $foundationTitleSetting->getValue() : $foundationTitlePostValue);
        $this->view->customAlt = ($foundationAltPostValue === null ? $foundationAltSetting->getValue() : $foundationAltPostValue);
		$this->view->contentUrl = ($foundationUrlPostValue === null ? $foundationUrlSetting->getValue() : $foundationUrlPostValue);
        $this->view->customimgpath = ($foundationImgUrlPostValue === null ? $foundationImgUrlSetting->getValue() : $foundationImgUrlPostValue);
        $this->view->contentTitle = $this->translator->_('cms.v3.admin.editpage.editfoundationwidgetheading');
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.editpage.subheading');
        $redirectToUrl = $this->tag->linkTo(
            array(
                "backend/page/editPage/" . $pageContentId,
                '<i class="fa fa-arrow-left fa-lg pull-right"></i>',
                "class" => "newPageModal btn btn-default",
                "title" => $this->translator->_('cms.v3.admin.pages.backpage')
            )
        );
        $this->view->websiteId = $pageContent->getPage()->website_id;
        $this->view->redirectToUrl = $redirectToUrl;
    }
	
	public function editBniUAction($pageContentId)
    {
        $pageContentId = $this->filter->sanitize($pageContentId, 'int');

        $pageContent = PageContent::findFirst($pageContentId);
        if ($pageContent == false || !$this->hasUserGotPermissionToAccessWebsite($pageContent->Page->Website)) {
            $this->failAndRedirect('backend/error/permissionDenied');
            return;
        }

		
        $currentSettings = $pageContent->getPageContentSettings();
        $bniuTitleSetting = $this->settingsFactory->getPageContentSetting("BNIU_CONTENT_TITLE_SETTING_VALUE", $currentSettings, $pageContent->id);
        $bniuTitlePostValue = $this->request->getPost('contentTitle', 'string');

		$bniuAltSetting = $this->settingsFactory->getPageContentSetting("BNIU_CONTENT_ALT_SETTING_VALUE", $currentSettings, $pageContent->id);
        $bniuAltPostValue = $this->request->getPost('contentAlt', 'string');
		
        $currentSettings = $pageContent->getPageContentSettings();
        $bniuUrlSetting = $this->settingsFactory->getPageContentSetting("BNIU_CONTENT_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $bniuUrlPostValue = $this->request->getPost('contentUrl', 'string');

        $bniuImgUrlSetting = $this->settingsFactory->getPageContentSetting("BNIU_CONTENT_IMG_URL_SETTING_VALUE", $currentSettings, $pageContent->id);
        $bniuImgUrlPostValue = $this->request->getPost('customimgpath', 'string');

        if ($this->request->isPost()) {
            try {

                if(empty(trim($bniuUrlPostValue)) || empty(trim($bniuImgUrlPostValue))){
                    $this->flash->error($this->translator->_("cms.v3.admin.editpage.valuesforallfieds"));
                }

                    $imgDimensionValidator = new ImageDimensionValidator(421, 652);

                    if ($imgDimensionValidator->validateImageDimensions($bniuImgUrlPostValue)) {
                        $pageData = $pageContent->getPage();
						$this->_updateAllRows($bniuTitlePostValue, $bniuTitleSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $this->_updateAllRows($bniuAltPostValue, $bniuAltSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
						$this->_updateAllRows($bniuUrlPostValue, $bniuUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $this->_updateAllRows($bniuImgUrlPostValue, $bniuImgUrlSetting->getSettingId(), $pageContent->getLanguageId(), $pageData->getWebsiteId());
                        $pageContent->Page->last_modified = date('Y-m-d H:i:s');
                        if ($pageContent->save() !== false) {
                            $this->flash->success($this->translator->_("cms.v3.admin.editpage.updatedsuccessfully"));
                        }
                    } else {
						$imgdimensionsvalidationmsgnew=$this->translator->_("cms.v3.admin.editpage.invalidImageDimensionsnew");
						$imgmsg=str_replace("{0}","652",$imgdimensionsvalidationmsgnew);										
						$imgmsg=str_replace("{1}","421",$imgmsg);
						
                        $this->flash->error($imgmsg);
                    }


            } catch (\Exception $exception) {
                $this->flash->error($exception->getMessage());
            } finally {
				$this->view->customAlt = $bniuAltPostValue;
				$this->view->customTitle = $bniuTitlePostValue;
                $this->view->contentUrl = $bniuUrlPostValue;
                $this->view->customimgpath = $bniuImgUrlPostValue;
            }
        }

        // Checking this means we don't show previous database data on a post update
		$this->view->customAlt = ($bniuAltPostValue === null ? $bniuAltSetting->getValue() : $bniuAltPostValue);
		$this->view->customTitle = ($bniuTitlePostValue === null ? $bniuTitleSetting->getValue() : $bniuTitlePostValue);
        $this->view->contentUrl = ($bniuUrlPostValue === null ? $bniuUrlSetting->getValue() : $bniuUrlPostValue);
        $this->view->customimgpath = ($bniuImgUrlPostValue === null ? $bniuImgUrlSetting->getValue() : $bniuImgUrlPostValue);
        $this->view->contentTitle = $this->translator->_('cms.v3.admin.widget.bniuwidget');
        $this->view->contentSubTitle = $this->translator->_('cms.v3.admin.editpage.subheading');
        $redirectToUrl = $this->tag->linkTo(
            array(
                "backend/page/editPage/" . $pageContentId,
                '<i class="fa fa-arrow-left fa-lg pull-right"></i>',
                "class" => "newPageModal btn btn-default",
                "title" => $this->translator->_('cms.v3.admin.pages.backpage')
            )
        );
        $this->view->websiteId = $pageContent->getPage()->website_id;
        $this->view->redirectToUrl = $redirectToUrl;
    }
    /**
     * @param $updateValue
     * @param $settingId
     * @param $languageId
     * @param $websiteId
     * @throws \Exception
     */
    private function _updateAllRows($updateValue, $settingId, $languageId, $websiteId)
    {
        $pageContentSettingsRows = PageContentSettings::getPageContentSettingsFromJoin($settingId, $languageId, $websiteId);

        foreach ($pageContentSettingsRows as $contentSettingsRow) {
            $contentSettingsRow->setValue(trim($updateValue));
            if ($contentSettingsRow->save() !== true) {
                Throw new \Exception($this->translator->_("cms.v3.admin.editpage.valuesforallfieds"));
            }
        }
    }
}