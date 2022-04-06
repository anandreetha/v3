<?php

use Phalcon\Mvc\Controller;

const LOCALE_REGEX = '/([a-zA-Z]{2})(?:-|_)?(?:[a-zA-Z]{2})?/';

class IndexController extends Controller
{

    public function renderAction()
    {
        // Load the config file
        $configPath = realpath('../' . '/config.json');
        $configFile = $this->_loadConfigFile($configPath);

        $page = null;

        // Get the param values, see if we can match any path and page with the given params
        $normalizedLocaleCode = $this->dispatcher->getParam('locale');
        $params = $this->_processParameter($normalizedLocaleCode);

        // Check if the user is trying to goto a site that is published but not in our config, if it's not in the config then they can't go there (its disabled)
        $isDisabledSite = false;
        if (isset($params[0])) {
            $isDisabledSite = $this->_matchLocaleToConfig([$params[0]], $configFile) === null;
        }

        // Check if the full path exists, if it does then we can just return what we've been asked
        if (!$isDisabledSite && $this->_doesViewPathExist($this->view->getViewsDir() . $normalizedLocaleCode)) {
            $this->view->setVars(
                [
                    'websiteTypeId' => $configFile[0]->type
                ]
            );
            return $this->view->pick(substr($normalizedLocaleCode, 1));
        }

        // Make sure we've got a path set, otherwise default it to the value in the config
        if (isset($params[1]) && strlen($params[1]) <= 0 || !isset($params[1])) {

            // Check to see if we've got a locale code in the address bar, if we do then we want to use that over everything else
            $foundLocale = $this->_matchLocaleToConfig($params, $configFile);

            // If we've found any locale in the address bar then we want to use that
            if ($foundLocale !== null || !empty($foundLocale)) {
                // Check if we've had a page passed in on the param, otherwise default to index we've found in the config
                $page = isset($params[1]) ? $params[1] : $foundLocale->index;
            }
        }

        // If we have url params but we don't match a locale in the config then we'll drop down here

        // The locales found from the accept language header
        $headerLocales = $this->_getPreferredLanguageFromHeader($this->request->getHeader('Accept-Language'));

        $foundLocale = $this->_matchLocaleToConfig($headerLocales, $configFile);

        // We can't match anything in the config so default to the first language option in the config
        if ($foundLocale === null) {
            $foundLocale = $configFile[0];
        }

        // If page is still not set, then use the index from the default locale we found
        if (is_null($page)) {
            $page = $foundLocale->index;
        }

        // The physical path on the server where we should find the phtml file
        $viewFilePath = realpath('../') . "/application/views/{$foundLocale->locale}/{$page}";

        // If this path isn't correct, then we'll 404
        if (!$this->_doesViewPathExist($viewFilePath)) {
            $this->response->setStatusCode(404);
            return $this->response->send();
        }

        if ($foundLocale->type == 3) {
            return $this->response->redirect('http://' . $foundLocale->domain . '/' . $foundLocale->locale . "/" . $page, false, 301);
        } else {
            return $this->response->redirect($foundLocale->locale . "/" . $page, false, 301);
        }

    }

    public function maintenanceAction(){
    }

    /**
     * Sort out the string returned from the params
     * into an consumable array
     * @param $param
     * @return array
     */
    private function _processParameter($param)
    {
        $paramParts = explode('/', $param);
        unset($paramParts[0]);
        return $paramParts = array_values($paramParts);
    }


    /**
     * Check if the locale and page name
     * exist as a file path
     * @param $path
     * @return bool
     */
    private function _doesViewPathExist($path) {

        $renderPath = str_replace('//', '/',$path);

        // Check if we need to pop .phtml on the end to check the path
        if (strpos($renderPath, '.phtml') === false) {
            $renderPath .= '.phtml';
        }

        return file_exists($renderPath);
    }

    /**
     * Load a config file
     * @param $configPath
     * @return array
     * @throws Exception
     */
    function _loadConfigFile($configPath): array
    {
        // Get the JSON file and convert it to an object for easier handling
        $configFile = null;
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath));
        }

        throw new \Exception('This website is down for maintenance; please try again later');
    }

    /**
     * See if we have any matching values from the locale header
     * @param $headerLocales
     * @param $configLocales
     * @return string|null
     */
    private function _matchLocaleToConfig($headerLocales, $configLocales)
    {
        // Loop through the found locales from the header and see if we match any from the config.ini file.
        // If we have multiple locales from the header then the first one is more specific.
        // But if we don't match on the first we'll keep loop through until we match something

        // Loop through the header locales
        foreach($headerLocales as $headerLocale) {
            // Loop through all the config file locales and see if we have a match
            foreach($configLocales as $configLocale) {
                if ($configLocale->locale === $headerLocale) {
                    return $configLocale;
                }
            }
        }
    }

    /**
     * Get an array of locales
     * @param $header
     * @return array
     */
    private function _getPreferredLanguageFromHeader($header) : array
    {
        $headerParts = explode(',', $header);

        $foundLocale = [];

        foreach($headerParts as $headerPart) {
            // Regex using optional groups for second group locale value and -
            // So we can match locale values such as (en) or (en-GB)

            preg_match_all(LOCALE_REGEX, $headerPart, $matches, PREG_SET_ORDER, 0);

            // Preg match will return us an array of arrays (matching arrays with bits of regex)
            // Locale header values have dashes, but the config locale value has underscore, so we'll replace dash for underscore to match the config
            // Force everything to lower, we do this when building the config file as well so we can make everything uniform
            if (!empty($matches)) {
                $foundLocale[] = $matches[0][0];
            }
        }

        return $foundLocale;

    }
}