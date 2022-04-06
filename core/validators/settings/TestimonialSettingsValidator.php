<?php
namespace Multiple\Core\Validators\Settings;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;

class TestimonialSettingsValidator extends Validation
{
    public function initialize()
    {
        $this->add(
            "edit_19", // the number is the settings id
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.testimonialwidget.contentrequiredmsg'),
                ]
            )
        );

        $this->add(
            "edit_20",
            new PresenceOf(
                [
                    'message' => $this->translator->_('cms.v3.admin.testimonialwidget.namerequiredmsg'),
                ]
            )
        );
    }

}



