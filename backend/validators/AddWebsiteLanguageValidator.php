<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class AddWebsiteLanguageValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'languageValue',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.languages.languagerequiredmsg'),

                ]
            )
        );

        $this->add(
            'statusValue',
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.languages.languagestatusrequiredmsg'),
                ]
            )
        );
    }
}
