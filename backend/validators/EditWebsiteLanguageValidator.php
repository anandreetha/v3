<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class EditWebsiteLanguageValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'statusValue',
            new PresenceOf(
                [
                    'message' => 'A value is required.',
                ]
            )
        );
    }
}
