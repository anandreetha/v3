<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Core\Models\Website;
use Multiple\Core\Widgets\Widget\Country\BaseWidget;
use Multiple\Frontend\Validators\EventdetailControllerValidator;
use Exception;

/**
 * Controller to display event detail form.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class CookiebotdeclarationController extends BaseController
{
    const LIVE_SITE = "Live_Site";

    const PAGE_PREVIEW = "Preview";

    public function displayAction()
    {
        $pageMode = $this->request->getPost("pageMode");
		 
		$this->view->cookie_group_id = $this->request->getPost(cookie_group_id); 	
		$this->view->languages = $this->request->getPost("languages");	

        $this->view->hideUrls = ($pageMode == self::PAGE_PREVIEW && $this->request->getPost("eventId") == null);

        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings = $this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->pick("cookiebotdeclaration/display");
    }


    /**
     * Method to retrive method details. It check the page mode and based on the input it use real json data or mock one.
     * @param $languages
     * @return mixed
     * @throws \Exception
     */
   

}
