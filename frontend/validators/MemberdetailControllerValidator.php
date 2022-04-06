<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class MemberdetailControllerValidator extends CommonValidator
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

        $this->add(
            'parameters',
            new PresenceOf(
                [
                    'message' => self::PARAMETERS_ERROR_MESSAGE,
                ]
            )
        );
    }
}
