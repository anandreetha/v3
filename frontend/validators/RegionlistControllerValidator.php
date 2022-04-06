<?php
namespace Multiple\Frontend\Validators;


use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use \Phalcon\Validation\Validator\Callback;

class RegionlistControllerValidator extends CommonValidator
{
    public function initialize()
    {
        $this->add(
            'orgIds',
            new PresenceOf(
                [
                    'message' => self::ORGIDS_ERROR_MESSAGE,
                ]
            )
        );
    }
}