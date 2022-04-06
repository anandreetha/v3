<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Callback;

class EditWebsiteSettingsValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'fixedSettingInputWebsiteName',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.settings.websitenamerequired'),
                ]
            )
        );

        // Trim whitespace on website name input
        $this->setFilters("fixedSettingInputWebsiteName", "trim");
    }

    public function addCountryValidators($website)
    {
        $this->add(
            "fixedSettingInputWebsiteName",
            new Uniqueness(
                [
                    "model" => $website,
                    "attribute" => "name",
                    'message' => $this->translator->_('cms.v3.admin.settings.websitenamealreadyexists'),
                ]
            )
        );

        $this->add(
            'fixedSettingSelectCountries',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.settings.countryrequired'),
                ]
            )
        );

        $this->add(
            'fixedSettingSelectNewsCountries',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.settings.newscountryrequired'),
                ]
            )
        );
    }

    public function addRegionValidators($website)
    {

        $this->add(
            "fixedSettingInputWebsiteName",
            new Uniqueness(
                [
                    "model" => $website,
                    "attribute" => "name",
                    'message' => $this->translator->_('cms.v3.admin.settings.websitenamealreadyexists'),
                ]
            )
        );

        $this->add(
            'fixedSettingSelectRegions',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.settings.regionrequired'),
                ]
            )
        );

        $this->add(
            'fixedSettingSelectNewsRegions',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.settings.newsregionrequired'),
                ]
            )
        );

        $this->add(
            'fixedSettingInputLocation',
            new PresenceOf(
                [
                    'message' =>  $this->translator->_("cms.v3.admin.settings.locationerror"),
                ]
            )
        );

        $this->add(
            'fixedSettingInputExecutiveDirector',
            new PresenceOf(
                [
                    'message' =>  $this->translator->_("cms.v3.admin.settings.directorerror"),
                ]
            )
        );

        $this->add(
            "fixedSettingInputContactEmail",
            new Callback(
                [
                    "message" => $this->translator->_("cms.v3.admin.settings.emailerror"),
                    "callback" => function ($data) {
                        $patt = '/^\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/';
                        $resp = preg_match($patt, $data['fixedSettingInputContactEmail']);

                        if ($resp ==1) {
                            return true;
                        }

                        return false;
                    }
                ]
            )
        );

        $this->add(
            "fixedSettingInputContactTelephone",
            new Callback(
                [
                    "message" =>$this->translator->_("cms.v3.admin.settings.telephoneerror"),
                    "callback" => function ($data) {
                        $patt = "/^(\d|\s|\+|\(|\)|\+|\-|\/)+$/";
                        $resp = preg_match($patt, $data['fixedSettingInputContactTelephone']);

                        if ($resp == 1) {
                            return true;
                        }

                        return false;
                    }
                ]
            )
        );
    }
}
