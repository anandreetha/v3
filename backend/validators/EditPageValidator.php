<?php

namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;

class EditPageValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'inputPageTitle',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagetitlerequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputPageLink',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagelinkrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            "inputPageLink",
            new Regex(
                [
                    "pattern" => "/^[^\"><|?\\:\/*. ;%'#&\\\]*$/",
                    "message" => $this->translator->_('cms.v3.admin.editpage.navnameinvalidcharacters'),
                ]
            )
        );

        $this->add(
            'inputMetaTitle',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagemetatitlerequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputMetaKeywords',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagemetakeywordsrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputMetaDescription',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagemetadescriptionrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputMetaAuthor',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagemetauthorrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputMetaRobots',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.editpage.pagemetrobotsrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            'inputPageOrder',
            new PresenceOf(
                [
                    'message' =>  $this->translator->_('cms.v3.admin.editpage.pageordernumberrequiredvalidationmsg'),
                ]
            )
        );

        $this->add(
            "inputPageOrder",
            new Numericality(
                [
                    "message" => $this->translator->_('cms.v3.admin.editpage.pageordernumbernotnumericvalidationmsg'),
                ]
            )
        );

        $this->add(
            "inputPageOrder",
            new Callback(
                [
                    "message" => $this->translator->_('cms.v3.admin.editpage.pageordernumbernotwholenumvalidationmsg'),
                    "callback" => function ($data) {
                        $patt = '/^\d+\.{1}\d+$/';
                        $resp = preg_match($patt, $data['inputPageOrder']);

                        if ($resp ==1) {
                            return false;
                        }

                        return true;
                    }
                ]
            )
        );
    }
}
