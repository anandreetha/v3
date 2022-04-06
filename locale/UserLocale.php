<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 06/11/2017
 * Time: 12:50
 */

namespace Multiple\Locale;

use Phalcon\Exception;
use Phalcon\Mvc\User\Component;

class UserLocale extends Component
{

    private $locale;

    private $messages = [];
    const PROPERTY_FILTER = array("cms.v3", "language.", "common.header", "common.dateformat",
        "admin.chapter.editattributes", "common.daynames", "datatablesparameters");

    /**
     * Locale constructor.
     * @param $locale
     */
    public function __construct($locale)
    {
        $this->locale = str_replace('-', "_", $locale);
        $this->messages = $this->loadLocalePropertiesFile();
    }

    private function loadLocalePropertiesFile()
    {
        $cacheKey = 'locale_' . $this->getLocale() . '_.cache';

        // Try to get cached records first
        $strings = $this->redisCache->get($cacheKey);

        if ($strings === null) {
            $strings = $this->compareAndMergeLocales();
            $this->redisCache->save($cacheKey, $strings, $this->config->general->localeCacheLifetime);
        }

        return $strings;
    }

    private function compareAndMergeLocales()
    {
        //Get default locale
        $defaultLocale = $this->loadDefaultLocalePropertiesFile();

        //Get the users locale
        $userLocale = $this->loadUsersLocalePropertiesFile();

        //Merge the contents together
        $mergedLocales = array_merge($defaultLocale, $userLocale);

        //Sort by key
        ksort($mergedLocales);

        //Loop through and strip any new lines or carriage returns
        foreach ($mergedLocales as $key => $mergedLocale) {
            $mergedLocale = str_replace("\:", ":", $mergedLocale);

            $mergedLocales[$key] = stripcslashes(str_replace(array("\r", "\n"), '', $mergedLocale));
        }

        return $mergedLocales;
    }

    private function loadDefaultLocalePropertiesFile()
    {
        // Initialise
        $strings = array();

        // Required properties file path
        $filename = $this->config->general->localePath . "global_en_US.properties";

        // A list of properties to filter on
        $propertyFilter = self::PROPERTY_FILTER;

        // So long as the file exists
        if (file_exists($filename)) {

            // Open the properties file
            $file = fopen($filename, "r");

            // Handle if locale file cannot be opened i.e incorrect permissions
            if ($file === false) {
                throw new Exception('There was a problem trying to load locales.');
            }

            // Loop through every line until the end of the file
            while (!feof($file)) {

                // Get the line contents
                $line = fgets($file);

                // So long as the line does not begin with a comment and limit down to filtered tokens only
                if (substr($line, 0, 1) != "#" &&
                    $this->lineContainsAllowedProperty($line, $propertyFilter) !== false) {
                    // Explode the line by delimiter so we get the key and value parts
                    $line = explode("=", $line);

                    $key = array_shift($line);
                    $value = implode("=", $line);

                    $strings[$key] = $value;
                }
            }

            // Close the file
            fclose($file);
        }


        return $strings;
    }

    private function loadUsersLocalePropertiesFile()
    {
        // Initialise
        $strings = array();

        $locale = $this->getLocale();

        // Required properties file path
        $filename = $this->config->general->localePath . "global_" . $locale . ".properties";

        // A list of properties to filter on
        $propertyFilter = self::PROPERTY_FILTER;

        //Fall back to default locale if the requested file does not exist
        if (!file_exists($filename)) {
            $filename = $this->config->general->localePath . "global_en_US.properties";
        }

        // So long as the file exists
        if (file_exists($filename)) {
            // Open the properties file
            $file = fopen($filename, "r");

            // Handle if locale file cannot be opened i.e incorrect permissions
            if ($file === false) {
                throw new Exception('There was a problem trying to load locales.');
            }

            // Loop through every line until the end of the file
            while (!feof($file)) {
                // Get the line contents
                $line = fgets($file);

                // So long as the line does not begin with a comment and limit down to filtered tokens only
                if (substr($line, 0, 1) != "#" &&
                    $this->lineContainsAllowedProperty($line, $propertyFilter) !== false) {
                    // Explode the line by delimiter so we get the key and value parts
                    $line = explode("=", $line);

                    $key = array_shift($line);
                    $value = implode("=", $line);

                    $strings[$key] = $value;
                }
            }

            // Close the file
            fclose($file);
        }

        return $strings;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param mixed $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    private function lineContainsAllowedProperty($line, array $allowedProperties)
    {
        //  Loop through each property
        foreach ($allowedProperties as $allowedProperty) {
            // if the line contains an allowed property then return true
            if (stripos($line, $allowedProperty) !== false) {
                return true;
            }
        }

        // No match
        return false;
    }
}

