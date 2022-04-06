<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Regex;

class AddPageValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'pageOrder',
            new PresenceOf(
                [
                    'message' =>  $this->translator->_('cms.v3.admin.custompage.pageordernumberrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            "pageOrder",
            new Numericality(
                [
                    "message" => $this->translator->_('cms.v3.admin.custompage.pageordernumbernotnumericvalidationmsg'),
                ]
            )
        );

        $this->add(
            "pageOrder",
            new Callback(
                [
                    "message" => $this->translator->_('cms.v3.admin.custompage.pageordernumberonlyvalidationmsg'),
                    "callback" => function ($data) {
                        if ($data['pageOrder'] < 1) {
                            return false;
                        }
                        return true;
                    }
                ]
            )
        );

        $this->add(
            "pageOrder",
            new Callback(
                [
                    "message" => $this->translator->_(
                        'cms.v3.admin.custompage.pageordernumbernotwholenumvalidationmsg'
                    ),
                    "callback" => function ($data) {
                        $patt = '/^\d+\.{1}\d+$/';
                        $resp = preg_match($patt, $data['pageOrder']);

                        if ($resp ==1) {
                            return false;
                        }

                        return true;
                    }
                ]
            )
        );

        $this->add(
            'pageTitle',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.custompage.pagetitlerequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            "navName",
            new PresenceOf(
                [
                    "message" => $this->translator->_('cms.v3.admin.custompage.navigationnamerequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            "navName",
            new Regex(
                [
                    "pattern" => "/^[^\"><|?\\:\/*. ;%'#&\\\]*$/",
                    "message" => $this->translator->_('cms.v3.admin.custompage.navnameinvalidcharacters'),
                ]
            )
        );
    }
}
