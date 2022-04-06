<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 04/01/2018
 * Time: 09:52
 */

namespace Multiple\Core\Misc;

class SettingMappingService
{
    public static function mapWidgetSettings($pageContentWidget)
    {
        $settingsMapper = new SettingsMapper();

        // Any settings which the user have created will take preference
        if (count($pageContentWidget->getPageContentWidgetSettings()) > 0) {

            // Map user defined settings
            $settingsMapper = self::createAndAddSettingVo(
                $pageContentWidget->getPageContentWidgetSettings(),
                $settingsMapper,
                false
            );

            $settingsMapper = self::ensureLatestSettings($pageContentWidget, $settingsMapper);

        } else {
            // Otherwise resort to using default widget settings
            $settingsMapper = self::createAndAddSettingVo(
                $pageContentWidget->widget->getWidgetSettings(),
                $settingsMapper,
                true
            );
        }

        return $settingsMapper;
    }

    /*
           We need to check here what happens when new default settings have been added to the widget after user
           settings have been created. We need to compare the number of user settings against the number of default
           settings for the widget and add them in for consistency.
           The way the existing code works should on the next save would persist the new setting to the
           user added setting for that particular widget.
    */
    private static function ensureLatestSettings($pageContentWidget, $settingsMapper)
    {
        // Lookup all setting ids that the user may have defined for this widget
        $currentUserWidgetSettings = $pageContentWidget->getPageContentWidgetSettings(
            array(
                'columns' => 'setting_id'
            )
        );

        // Lookup all setting ids that the widget has by default
        $defaultWidgetSettings = $pageContentWidget->widget->getWidgetSettings(
            array(
                'columns' => 'setting_id'
            )
        );

        // Flatten down the results for easier comparision
        $defaultSettingsTmp = self::flattenSettingsArray(($defaultWidgetSettings->toArray()));
        $currentSettingTmp = self::flattenSettingsArray(($currentUserWidgetSettings->toArray()));

        // Compare the arrays and check for any differences
        $settingsDifferences = array_diff($defaultSettingsTmp, $currentSettingTmp);

        // If a new default setting has been added after the widget was created then it should appear in this array
        $missingSettings = array_values($settingsDifferences);

        // Only if there are new settings do we need to look them up
        if (!empty($missingSettings)) {
            $missingWidgetSettings = $pageContentWidget->widget->getWidgetSettings(
                array(
                    "conditions" => "setting_id in ({setting_id:array})",
                    "bind" => array(
                        "setting_id" => $missingSettings
                    )
                )
            );

            $settingsMapper = self::createAndAddSettingVo($missingWidgetSettings, $settingsMapper, true);
        }
        return $settingsMapper;
    }

    private static function flattenSettingsArray($multiSettingsArray)
    {
        $flattenedArray = array();

        // Flatten 2D array to 1D array
        foreach ($multiSettingsArray as $aV) {
            $flattenedArray[] = $aV['setting_id'];
        }

        return $flattenedArray;
    }

    private static function createAndAddSettingVo($settings, $settingsMapper, $default = false)
    {
        if (count($settings) > 0) {
            foreach ($settings as $widgetSetting) {
                if ($default) {
                    $setting = new SettingVo(
                        $widgetSetting->setting->id,
                        $widgetSetting->setting->name,
                        htmlentities($widgetSetting->setting->default_value, ENT_QUOTES)
                    );
                } else {
                    $setting = new SettingVo(
                        $widgetSetting->setting->id,
                        $widgetSetting->setting->name,
                        htmlentities($widgetSetting->value, ENT_QUOTES)
                    );
                }

                if (!is_null($widgetSetting->setting->type)) {
                    $setting->setType($widgetSetting->setting->type);
                }

                if (!is_null($widgetSetting->setting->data_type)) {
                    $setting->setDataType($widgetSetting->setting->data_type);
                }

                if (!is_null($widgetSetting->setting->form_input_type)) {
                    $setting->setFormInputType($widgetSetting->setting->form_input_type);
                }

                if (!is_null($widgetSetting->setting->translate_token)) {
                    $setting->setTranslateToken($widgetSetting->setting->translate_token);
                }

                if (!is_null($widgetSetting->setting->display_order)) {
                    $setting->setDisplayOrder($widgetSetting->setting->display_order);
                }

                if ($default) {
                    $setting->setUsingDefault(true);
                }

                $settingsMapper->append($setting);
            }
        }

        return $settingsMapper;
    }
}