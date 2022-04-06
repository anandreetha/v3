<?php

namespace Multiple\Core\Widgets\Widget\Country;
use Phalcon\Db\Column;

class CookiebotmenuWidget extends BaseWidget
{
    private $foundationUrl= "http://www.bnifoundation.org/";
    private $foundationCustomImageUrl= "";
	private $foundationTitle= "Foundation";

    public function getContent($website_id,$active_language)
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/');
		
		
		$query="SELECT pc.title,pc.nav_name FROM page_content pc, page p WHERE pc.page_id=p.id AND pc.language_id=:LanguageId AND p.template='cookiebot-declaration' AND p.website_id=:websiteid";
		
		$getList = $this->db->prepare($query);
		$data = $this->db->executePrepared(
			$getList,
			[
				"websiteid" => $website_id,
				"LanguageId" => $active_language->id
			],
			[
				"websiteid" => Column::BIND_PARAM_INT,
				"LanguageId" => Column::BIND_PARAM_INT,
			]
		);
		
		$result    = $data->fetchAll();
		if(count($result)>0):
			$rs=$result[0];
			
			$this->view->menuName=$rs['title'];
			$this->view->nav_name=$rs['nav_name'];
			// Render a view and return its contents as a string
			return $this->view->render('cookiebot-menu-widget');
		endif;
    }

}
