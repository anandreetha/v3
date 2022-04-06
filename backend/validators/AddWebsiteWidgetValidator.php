<?php
namespace Multiple\Backend\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class AddWebsiteWidgetValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            'newWebsiteWidget',
            new PresenceOf(
                [
                    'message' => 'A language is required.',
                ]
            )
        );
    }
}
