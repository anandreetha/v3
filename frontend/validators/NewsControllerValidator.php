<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class NewsControllerValidator extends CommonValidator
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
            'orgIds',
            new PresenceOf(
                [
                    'message' => self::LANGUAGES_ERROR_MESSAGE,
                ]
            )
        );
    }
}
