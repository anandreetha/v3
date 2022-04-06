<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class AddWebsiteSettingValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'settingValue',
            new PresenceOf(
                [
                    'message' => 'A value is required.',
                ]
            )
        );
    }
}
