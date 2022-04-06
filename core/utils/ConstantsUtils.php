<?php
namespace Multiple\Core\Utils;

use Phalcon\Mvc\User\Component;

/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 27/11/2017
 * Time: 13:18
 */
class ConstantsUtils extends Component
{

    private $countryWebsiteTypeId = 1;
    private $regionWebsiteTypeId = 2;
    private $chapterWebsiteTypeId = 3;

    private $countryWebsitePermission = 396;
    private $regionWebsitePermission = 237;
    private $chapterWebsitePermission = 238;

    private $newsCountryWebsiteSettingId = 300;
    private $newsRegionWebsiteSettingId = 330;

    public function getCountryWebsiteTypeId(){
        return $this->countryWebsiteTypeId;
    }

    public function getRegionWebsiteTypeId(){
        return $this->regionWebsiteTypeId;
    }

    public function getChapterWebsiteTypeId(){
        return $this->chapterWebsiteTypeId;
    }

    public function getCountryWebsitePermission(): int
    {
        return $this->countryWebsitePermission;
    }

    public function getRegionWebsitePermission(): int
    {
        return $this->regionWebsitePermission;
    }

    public function getChapterWebsitePermission(): int
    {
        return $this->chapterWebsitePermission;
    }

    public function getWebsitePermission($typeId): int
    {
        switch ($typeId) {
            case 1 :
                return $this->countryWebsitePermission;
                break;
            case 2 :
                return $this->regionWebsitePermission;
                break;
            case 3 :
                return $this->chapterWebsitePermission;
                break;
        }
    }

    public function getNewsCountryWebsiteSettingId(){
        return $this->newsCountryWebsiteSettingId;
    }

    public function getNewsRegionWebsiteSettingId(){
        return $this->newsRegionWebsiteSettingId;
    }

    public function getNewsWebsiteSettingId($typeId): int
    {
        switch ($typeId) {
            case 1 :
                return $this->newsCountryWebsiteSettingId;
                break;
            case 2 :
                return $this->newsRegionWebsiteSettingId;
                break;
        }
    }

}