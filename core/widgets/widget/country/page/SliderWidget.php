<?php

namespace Multiple\Core\Widgets\Widget\Country\Page;

use Multiple\Core\Widgets\Widget\Country\BaseWidget;

class SliderWidget extends BaseWidget
{
    public function getContent($contentSettings='')
    {
        // A trailing directory separator is required
        $this->view->setViewsDir('../core/widgets/views/country/page/');

        $widgetSettings = $this->getWidgetSettings();

        if ($this->isRenderStaticContent() && property_exists($widgetSettings[0], 'setting_id')) {
            $staticContent = $this->convertToStaticReferences($widgetSettings);
            $this->view->staticContent = $staticContent;
            $this->view->widgetSettings = $widgetSettings;
			$this->view->contentSettings=$contentSettings;

            // Render the primary view and return its contents as a string
            return $this->view->render('slider-widget-static');
        } else {
            $this->view->widgetSettings = $widgetSettings;
			$this->view->contentSettings=$contentSettings;
			
            // Render the primary view and return its contents as a string
            return $this->view->render('slider-widget');
        }
    }

    private function convertToStaticReferences($widgetSettings)
    {
        $manipulateSettingValues = array();

        $settingIdsToManipulate = array(27, 30, 33, 36, 39);
        $strToReplace = $this->config->general->baseUri . "backend/render/renderImage/";
        $commonLibStrToReplace = $this->config->general->baseUri . "public/img/common-library/";
        $bucket = $this->mongo->selectGridFSBucket();
        $domain = $this->getWebsite()->getDomain();

        foreach ($widgetSettings as $key => $widgetSetting) {
            if (in_array($widgetSetting->setting_id, $settingIdsToManipulate)) {
                if (strpos($widgetSetting->value, $strToReplace) !== false) {
                    $fileId = str_replace($strToReplace, "", $widgetSetting->value);
                } else {
                    $file = str_replace($commonLibStrToReplace, "", $widgetSetting->value);
                    if ($file !== $widgetSetting->value) {
                        $file = $this->config->general->cdn . '/images/' . $file;
                    }
                    $fileExt = pathinfo($file, PATHINFO_EXTENSION);
                    $manipulateSettingValues[] = $file;
                    $fileId = "";
                }

                if ($fileId === "") {
                    continue;
                }

                $bucketFile = $bucket->findOne(array('_id' => new \MongoDB\BSON\ObjectId((string)$fileId)));
                if (!is_null($bucketFile)) {
                    $fileData = $bucketFile->jsonSerialize();
                    $fileExt = pathinfo($fileData->filename, PATHINFO_EXTENSION);
                    $manipulateSettingValues[] = '../img/site/'
                        . $fileId . "." . $fileExt;
                } else {
                    $manipulateSettingValues[] = $widgetSetting->value;
                }
            } else {
                $manipulateSettingValues[] = $widgetSetting->value;
            }
        }

        return $manipulateSettingValues;
    }
}
