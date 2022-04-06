<?php

namespace Multiple\Core\Widgets\Factory;

use Multiple\Core\Misc\SettingMappingService;

class CoreWidgetFactory
{
    public static function getCountryWidget($name)
    {
        $className = "\\Multiple\\Core\\Widgets\\Widget\\Country\\{$name}";

        if (!class_exists($className)) {
            return false;
        }

        return new $className();
    }

    public static function getCountryPageWidgetWithProperties($name, $widgetProperties = null, $folder = null)
    {
        if (is_null($folder)) {
            $className = "\\Multiple\\Core\\Widgets\\Widget\\Country\\Page\\{$name}";
        } else {
            $className = "\\Multiple\\Core\\Widgets\\Widget\\Country\\Page\\{$folder}\\{$name}";
        }

        return self::getWidgetWithProperties($widgetProperties, $className);
    }

    public static function getRegionPageWidgetWithProperties($name, $widgetProperties = null, $folder = null)
    {
        if (is_null($folder)) {
            $className = "\\Multiple\\Core\\Widgets\\Widget\\Region\\Page\\{$name}";
        } else {
            $className = "\\Multiple\\Core\\Widgets\\Widget\\Region\\Page\\{$folder}\\{$name}";
        }

        return self::getWidgetWithProperties($widgetProperties, $className);
    }

    public static function getChapterChapterWidgetWithProperties($name, $widgetProperties = null, $folder = null)
    {
        if (is_null($folder)) {
            if (is_null($folder)) {
                $className = "\\Multiple\\Core\\Widgets\\Widget\\Chapter\\Page\\{$name}";
            } else {
                $className = "\\Multiple\\Core\\Widgets\\Widget\\Chapter\\Page\\{$folder}\\{$name}";
            }
        }

        return self::getWidgetWithProperties($widgetProperties, $className);
    }

    /**
     * Return an instance of the WYSIWYG widget
     * @param string $name
     * @return bool|\Multiple\Core\Widgets\Widget\Country\WysiwygWidget
     */
    public static function getWysiwygContentWidget($name = 'WysiwygWidget')
    {
        $className = "\\Multiple\\Core\\Widgets\\Widget\\Country\\{$name}";

        if (!class_exists($className)) {
            return false;
        }

        return new $className;
    }

    /**
     * @param $widgetProperties
     * @param $className
     * @return bool
     */
    private static function getWidgetWithProperties($widgetProperties, $className)
    {
        if (!class_exists($className)) {
            return false;
        }

        $newInstance = new $className();


        //Get the page widget properties/settings if they have been defined
        if (!is_null($widgetProperties)) {
            $newInstance->setMappedWidgetSettings(SettingMappingService::mapWidgetSettings($widgetProperties));

            if (count($widgetProperties->getPageContentWidgetSettings()) > 0) {
                $newInstance->setWidgetSettings($widgetProperties->getPageContentWidgetSettings());
            } else {
                // Otherwise resort to using defaults
                $widgetSettings = $widgetProperties->widget->getWidgetSettings();

                if (count($widgetSettings) > 0) {
                    $settingsContainer = array();

                    foreach ($widgetSettings as $widgetSetting) {
                        $settingsContainer[] = $widgetSetting->setting;
                    }
                    $newInstance->setWidgetSettings($settingsContainer);
                } else {
                    $newInstance->setWidgetSettings(null);
                }
            }

            return $newInstance;
        } elseif (!class_exists($className)) {
            return false;
        } else {
            return new $className();
        }
    }

}
