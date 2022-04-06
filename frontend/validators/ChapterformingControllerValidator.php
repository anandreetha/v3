<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class ChapterformingControllerValidator extends CommonValidator
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
            'regionIds',
            new PresenceOf(
                [
                    'message' => self::REGION_IDS_ERROR_MESSAGE,
                ]
            )
        );
    }
}
