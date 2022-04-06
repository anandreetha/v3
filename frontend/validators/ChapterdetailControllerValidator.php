<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class ChapterdetailControllerValidator extends CommonValidator
{
    public function initialize()
    {
        $this->add(
            'languageLocaleCode',
            new PresenceOf(
                [
                    'message' => self::LOCALE_ERROR_MESSAGE,
                ]
            )
        );
    }
}
