<?php

namespace Multiple\Frontend\Validators;

use Phalcon\Validation\Validator\PresenceOf;

class ChapterdetailFooterControllerValidator extends CommonValidator
{
    public function initialize()
    {
        $this->add(
            'chapterId',
            new PresenceOf(
                [
                    'message' => self::CHAPTER_ID_ERROR_MESSAGE,
                ]
            )
        );
        $this->add(
            'regionId',
            new PresenceOf(
                [
                    'message' => self::REGION_IDS_ERROR_MESSAGE,
                ]
            )
        );
    }
}
