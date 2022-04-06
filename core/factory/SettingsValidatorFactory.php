<?php

namespace Multiple\Core\Factory;

use Multiple\Core\Validators\Settings\SliderSettingsValidator;
use Multiple\Core\Validators\Settings\TestimonialSettingsValidator;
use Multiple\Core\Validators\Settings\DefaultSettingValidator;

use Phalcon\Mvc\User\Component;

class SettingsValidatorFactory extends Component
{

    public function getValidator($validator = "")
    {
        switch (strtolower($validator)) {

            case 'testimonial':
                return new TestimonialSettingsValidator();
                break;

            case 'slider':
                return new SliderSettingsValidator();
                break;
            case '':
            default:
                return new DefaultSettingValidator();
        }
    }


}