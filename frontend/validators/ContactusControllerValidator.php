<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class ContactusControllerValidator extends CommonValidator
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
                    'message' => self::ORGIDS_ERROR_MESSAGE,
                ]
            )
        );
        $this->add(
            'website',
            new PresenceOf(
                [
                    'message' => self::WEBSITE_ERROR_MESSAGE,
                ]
            )
        );
    }
}
