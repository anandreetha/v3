<?php

namespace Multiple\Core\Widgets\Widget\Country;

use Multiple\Core\Models\PageContent;
use Multiple\Core\Models\PageContentSettings;
use Multiple\Core\Models\Website;

class BniUWidget extends BaseWidget
{
    private $bniuUrl = "http://bniuniversity.com/";
    private $bniuCustomImageUrl = "";
	private $bniuTitle="BNI University";

    public function getContent()
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');

        $this->view->bniuURL = $this->getBniUUrl();

        if ($this->isRenderStaticContent()) {
            $this->view->bniuCustomImageUrl = $this->convertWidgetImagePathToStaticUrl(
                $this->getBniUCustomImageUrl()
            );
        } else {
            $this->view->bniuCustomImageUrl = $this->getBniUCustomImageUrl();
        }
		$this->view->bniuTitle = $this->getBniUTitle();
		$this->view->bniuAlt = $this->getBniUAlt();
        // Render a view and return its contents as a string
	
		/*if($_REQUEST['debug']=="1"):
			$pageContentHomeId="79340";	
			$pageContentHomeId = $this->filter->sanitize($pageContentHomeId, 'int');

			$homepageContent = PageContent::findFirst($pageContentHomeId);
			$contentSettings = array();

			foreach ($homepageContent->getPageContentSettings() as $contentSetting) {
				$contentSettings[$contentSetting->Setting->name] = $contentSetting->value;
			}
			echo $websiteId;
			$websiteModel = Website::findFirstById($websiteId);
			print"<pre>";print_r($contentSettings);print"</pre>";
		endif;*/
	
        return $this->view->render('bniu-widget');
    }

    /**
     * @return string
     */
    public function getBniUUrl(): string
    {
        return $this->bniuUrl;
    }

    /**
     * @param string $bniuUrl
     */
    public function setBniUUrl(string $bniuUrl): void
    {
        $this->bniuUrl = $bniuUrl;
    }

    /**
     * @return string
     */
    public function getBniUCustomImageUrl(): string
    {
        return $this->bniuCustomImageUrl;
    }

    /**
     * @param string $bniuTitle
     */
    public function setBniUCustomImageUrl(string $bniuCustomImageUrl): void
    {
        $this->bniuCustomImageUrl = $bniuCustomImageUrl;
    }
	public function getBniUTitle(): string
    {
        return $this->bniuTitle;
    }
	public function setBniUTitle(string $bniuTitle): void
    {
		$this->bniuTitle = $bniuTitle;
    }
	public function getBniUAlt(): string
    {
        return $this->bniuAlt;
    }
	public function setBniUAlt(string $bniuAlt): void
    {
		$this->bniuAlt = $bniuAlt;
    }
}
