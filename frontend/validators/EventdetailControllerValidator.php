<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class EventdetailControllerValidator extends CommonValidator
{
    public function initialize()
    {
        $this->add(
            'languages',
            new PresenceOf(
                [
                    'message' => self::LANGUAGES_ERROR_MESSAGE,
                ]
            )
        );
    }
}
