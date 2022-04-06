<?php
namespace Multiple\Frontend\Validators;

use Phalcon\Validation;

class CommonValidator extends Validation
{
    const LANGUAGES_ERROR_MESSAGE = 'The languages parameter is empty';
    const ORGIDS_ERROR_MESSAGE = 'The orgIds parameter are empty';
    const PARAMETERS_ERROR_MESSAGE = 'Incoming parameters are empty';
    const HIDE_FILE_EXTENSION_ERROR_MESSAGE = "hide file extension parameter is empty";
    const WEBSITE_ERROR_MESSAGE = "There is no incoming website";
    const REGION_IDS_ERROR_MESSAGE = "The regionIds are empty";
    const LOCALE_ERROR_MESSAGE = "The locale parameter is empty";
    const CHAPTER_ID_ERROR_MESSAGE = "The chapter id is empty";
}
