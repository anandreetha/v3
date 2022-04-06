<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class ChapterlistControllerValidator extends CommonValidator
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
            'cmsv3',
            new PresenceOf(
                [
                    'message' => self::HIDE_FILE_EXTENSION_ERROR_MESSAGE,
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
