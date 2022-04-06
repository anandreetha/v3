<?php

namespace Multiple\Frontend\Controllers;

use Embed\Embed;
use Multiple\Frontend\Validators\RegionlistControllerValidator;
use Phalcon\Db\Column;
use Exception;

/**
 * Controller to display region list widget.
 * Class EventdetailController
 * @package Multiple\Frontend\Controllers
 */
class RegionlistController extends BaseController
{

    public function displayAction()
    {
        if ($this->request->isPost()) {
            try {
                $orgIds = $this->request->getPost("orgIds");
                $validation = new RegionlistControllerValidator();
                $messages = $validation->validate($this->request->getPost());
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $this->logger->error("Region list controller: " . $message);
                    }
                    throw  new Exception($message);
                } else {
                    $orgIdsString = implode(",", $orgIds);

                    $sql = " SELECT 
                            DISTINCT
                            w.name,
                            w.clean_domain,
                            COALESCE(loc.value, '') AS location,
                            COALESCE(dir.value, '') AS director,
                            COALESCE(tel.value, '') AS phoneno,
                            COALESCE( email.value, '') AS emailaddress
                              FROM cms_v3.website AS w
                              JOIN cms_v3.website_org AS wo ON w.id = wo.website_id 
                              LEFT JOIN cms_v3.website_settings loc ON w.id = loc.website_id AND loc.settings_id = 331
                              LEFT JOIN cms_v3.website_settings dir ON loc.website_id = dir.website_id AND dir.settings_id = 332
                              LEFT JOIN cms_v3.website_settings tel ON loc.website_id = tel.website_id AND tel.settings_id = 333
                              LEFT JOIN cms_v3.website_settings email ON loc.website_id = email.website_id AND email.settings_id = 334 WHERE FIND_IN_SET(wo.parent_org_id,:orgIds)
                              AND w.last_published IS NOT NULL ";


                    $getList = $this->db->prepare($sql);
                    $res = $this->db->executePrepared(
                        $getList,
                        [
                            "orgIds" => $orgIdsString,
                        ],
                        [
                            "orgIds" => Column::BIND_PARAM_STR,
                        ]
                    );
                    $output = array();
                    foreach ($res as $val) {
                        $val["emailaddress"] = isset($val["emailaddress"]) ?
                            $this->securityUtils->encryptUrlEncoded($val["emailaddress"]) : '';
                        $output[] = $val;
                    }
                    $this->view->jsonResponse = $output;
                }
            } catch (Exception $exception) {
                $this->view->errorMessage = $this->getErrorMessage($this->request->getLanguages()[0]["language"]);
            }
        }
        $mappedWidgetSettingsJson = json_decode($this->request->getPost("mappedWidgetSettings"));
        $this->view->mappedWidgetSettings=$this->convertToSettingMapper($mappedWidgetSettingsJson);
        $this->view->pick("regionlist/display");
    }
}
