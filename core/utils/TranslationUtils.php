<?php
/**
 * Created by PhpStorm.
 * User: shabnam.sidhik
 * Date: 23/11/2017
 * Time: 14:39
 */

namespace Multiple\Core\Utils;

use Phalcon\Mvc\User\Component;

class TranslationUtils extends Component
{
    /**
     * The function accepts a date in java formatted mode and returns the equivalent php date
     * @param $date
     * @return mixed
     */
    public function formatDate($date, $userTimezone = "Europe/London")
    {
        if ($date == null) {
            return "";
        } else {
            // The server timezone is UK time
            $dateToModify = new \DateTime($date, new \DateTimeZone("Europe/London"));
            $dateToModify->setTimezone(new \DateTimeZone($userTimezone));

            $currentFormatter = $this->translator->_('common.dateformat.shortdatetimealt');
            $currentFormatter = str_replace('dd', 'd', $currentFormatter);
            $currentFormatter = str_replace('MM', 'm', $currentFormatter);
            $currentFormatter = str_replace('yyyy', 'Y', $currentFormatter);
            $currentFormatter = str_replace('HH', 'H', $currentFormatter);
            $currentFormatter = str_replace('mm', 'i', $currentFormatter);

            return $dateToModify->format($currentFormatter);
        }
    }

    /**
     * The function sorts the languages in alphabetical order
     * @param $languagesNav
     * @param $getLanguageDescription
     * @return mixed
     */
    public function sortLanguages($languagesNav, $getLanguageDescription)
    {
        uasort($languagesNav, function ($a, $b) use ($getLanguageDescription) {
            $at = iconv('UTF-8', 'ASCII//TRANSLIT', $getLanguageDescription($a));
            $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $getLanguageDescription($b));
            return strcmp($at, $bt);
        });
        return $languagesNav;
    }

    /**
     * Changes the locale code to either be cc_NN form or cc-NN form (it'll convert into the underscore form if the
     * second param is true)
     * @param $locale
     * @param $isGoingIntoTheDatabase
     * @return String the locale code in either cc_NN form or cc-NN form
     */
    public function normalizeLocaleCode($locale, $isGoingIntoTheDatabase)
    {
        if ($isGoingIntoTheDatabase) {
            return str_replace('-', '_', $locale);
        }

        return str_replace('_', '-', $locale);
    }
}
