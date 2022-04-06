<?php

namespace Multiple\Backend\Validators;

use Multiple\Core\Models\Website;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Uniqueness;

class AddWebsiteValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'inputWebsiteName',
            new PresenceOf(
                [
                    'message' => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitenamerequiredvalidationmsg'
                    ),
                ]
            )
        );

        $this->add(
            "inputWebsiteName",
            new Uniqueness(
                [
                    "model" => new Website(),
                    "attribute" => "name",
                    'message' => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitenamexistsvalidationmsg'
                    ),
                ]
            )
        );

        $this->add(
            'inputWebsiteDomain',
            new PresenceOf(
                [
                    'message' => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitedomainrequiredvalidationmsg'
                    ),
                ]
            )
        );

        $this->add(
            "inputWebsiteDomain",
            new Uniqueness(
                [
                    "model" => new Website(),
                    "attribute" => "clean_domain",
                    'message' => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitedomainnamexistsvalidationmsg'
                    ),
                ]
            )
        );

        $this->add(
            'inputWebsiteCountryList',
            new PresenceOf(
                [
                    'message' => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitecountryrequiredvalidationmsg'
                    ),
                ]
            )
        );

        $this->add(
            "inputWebsiteDomain",
            new Regex(
                [
                    "pattern" => "/\b((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}\b/",
                    "message" => $this->translator->_(
                        'cms.v3.admin.websitecreation.newwebsitewebsitedomaininvalidvalidationmsg'
                    ),
                ]
            )
        );

        // Trim whitespace on website name input
        $this->setFilters("inputWebsiteName", "trim");
    }
}
