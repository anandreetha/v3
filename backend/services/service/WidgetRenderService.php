<?php

namespace Multiple\Backend\Services\Service;

use Phalcon\Mvc\User\Component;

class WidgetRenderService extends Component
{
    public function renderWidgets($pageWidgets, $properties = null)
    {
		foreach ($pageWidgets as $key => $pageWidget) {
            $widgetType = $pageWidget->widget->class_name;

            switch ($widgetType) {
                // Advanced Chapter Search chapter form widget
                case "AdvancedChapterSearchFormWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "advancedchaptersearch"
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["languages"],
                            $properties["page"],
                            $properties['domainName'],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Advanced Chapter Search chapter list widget
                case "AdvancedChapterSearchResultWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "advancedchaptersearch"
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["pageMode"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // AdvancedChapterSearchDetailWidget Widget
                case "AdvancedChapterSearchDetailWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "advancedchaptersearch"
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["pageMode"],
                            $properties["languages"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // ChapterMap Widget
                case "ChapterMapWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["domainName"],
                            $properties["website"],
                            $properties["languages"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Contact-us Widget
                case "ContactUsWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["languages"],
                            $properties["page"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Event calendar widget
                case "EventCalendarWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["page"],
                            $properties["languages"]
                        );
                    }
                    break;

                // Event detail widget
                case "EventDetailWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["pageMode"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Event registration widget
                case "EventRegistrationWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            null,
                            null,
                            null,
                            null,
                            null
                        );
                    }
                    break;

                // Find a member list widget
                case "FindAMemberListWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "findamember"
                    );
                    if ($widget != false) {
                        if (isset($properties["regionId"])) {
                            echo $widget->getContent(
                                $properties["orgIds"],
                                $properties["languages"],
                                $properties["pageMode"],
                                $properties["regionId"],
                                $properties['domainName'],
                                $properties['renderStaticContent']
                            );
                        } else {
                            echo $widget->getContent(
                                $properties["orgIds"],
                                $properties["languages"],
                                $properties["pageMode"],
                                null,
                                $properties['domainName'],
                                $properties["renderStaticContent"]
                            );
                        }
                    }
                    break;

                // Find a member form widget
                case "MemberFormWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "findamember"
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["languages"],
                            $properties["page"]
                        );
                    }
                    break;

                // Member Details widget
                case "MemberDetailsWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "findamember"
                    );
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["languages"],
                            $properties["pageMode"],
                            $properties["domainName"],
                            $properties["website"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // News Widget
                case "NewsWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["languages"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Newsletter Widget
                case "NewsletterWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["orgIds"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Region-List Widget
                case "RegionListWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["orgIds"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                // Send a message Widget
                case "SendMessageWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties(
                        "SendMessageWidget",
                        $pageWidget
                    );
                    if ($widget != false) {
                        echo $widget->getContent();
                    }
                    break;

                // Testimonial Widget
                case "TestimonialWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent();
                    }
                    break;

                // Album Widget
                case "AlbumWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        $widget->setKeyIdentifer($key);
                        $widget->setRenderStaticContent($properties['renderStaticContent']);
                        $widget->setWebsite($properties['website']);

                        if (count($pageWidget->PageContentWidgetItems) > 0) {
                            $widget->setWidgetItems($pageWidget->PageContentWidgetItems);
                        }

                        echo $widget->getContent();
                    }
                    break;

                // Chapter Album Links Widget
                case "ChapterAlbumLinksWidget":
                    $widget = $this->coreWidgetFactory->getRegionPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        $widget->setRenderStaticContent($properties['renderStaticContent']);
                        $widget->setWebsite($properties['website']);
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties['renderStaticContent'],
                            $properties["domainName"]
                        );
                    }
                    break;

                // Wysiwyg widget
                case "WysiwygWidget":
                    /** @var $contentWidget \Multiple\Core\Widgets\Widget\Country\WysiwygWidget */
                    $contentWidget = $this->coreWidgetFactory->getWysiwygContentWidget();

                    foreach ($pageWidget->getPageContentWidgetSettings() as $pageContentWidgetSetting) {
                        // Need to get the website, maybe a bit OTT on the nested if's but should prevent any failures
                        $pageContentWidget = $pageContentWidgetSetting->getPageContentWidgets();
                        // We used to use count(...) here. From what I can see, the above call does NOT return an array
                        // of results, instead returning a (singular) PageContentWidgets which is probably the same
                        // instance as $pageWidget (but I don't know for sure.) count(...) has the odd property of returning
                        // 1 when a non-Countable/array object is passed to it, so I believe that the following code was
                        // mistakenly using count as a null check (???) but using count this way throws warnings.
                        if (isset($pageContentWidget)) {
                            $pageContent = $pageContentWidget->getPageContent();

                            if (isset($pageContent)) {
                                $page = $pageContent->getPage();

                                if (isset($page)) {
                                    $website = $page->getWebsite();

                                    if (isset($website)) {
                                        $contentWidget->setWebsite($website);
                                    }
                                }
                            }
                        }

                        if (isset($properties['renderStaticContent'])) {
                            $contentWidget->setRenderStaticContent($properties['renderStaticContent']);
                        } else {
                            $contentWidget->setRenderStaticContent(false);
                        }

                        $contentWidget->setHtmlContent($pageContentWidgetSetting->getValue());
                        echo $contentWidget->getContent();
                    }
                    break;

                // core group widget
                case "CoreGroupWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                case "ChapterFormingWidget":
                    $widget = $this->coreWidgetFactory->getRegionPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["orgIds"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                case "FindAnOpeningFormWidget":
                    $widget = $this->coreWidgetFactory->getRegionPageWidgetWithProperties($widgetType, $pageWidget, "findanopening");
                    if ($widget != false) {
                        echo $widget->getContent($properties["languages"], $properties["orgIds"]);
                    }
                    break;

                case "FindAnOpeningResultWidget":
                    $widget = $this->coreWidgetFactory->getRegionPageWidgetWithProperties(
                        $widgetType,
                        $pageWidget,
                        "findanopening"
                    );
                    if ($widget != false) {
                        echo $widget->getContent($properties["languages"]);
                    }
                    break;

                case "VisitorRegistrationWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            null,
                            null,
                            null,
                            null,
                            null
                        );
                    }
                    break;

                case "ApplicationRegistrationWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            null,
                            null,
                            null,
                            null,
                            null
                        );
                    }
                    break;
				
				case "CookiebotDeclarationWidget":
                    $widget = $this->coreWidgetFactory->getCountryPageWidgetWithProperties($widgetType, $pageWidget);					
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["pageMode"],
                            $properties["domainName"],
                            $properties["renderStaticContent"]
                        );
                    }
                    break;

                case "ChapterDetailFooterWidget":
                    $widget = $this->coreWidgetFactory->getChapterChapterWidgetWithProperties($widgetType, $pageWidget);
                    if ($widget != false) {
                        echo $widget->getContent(
                            $properties["languages"],
                            $properties["orgIds"],
                            $properties["regionId"],
                            $properties["domainName"],
                            $properties["renderStaticContent"],
                            $properties["websiteId"]
                        );
                    }
            }
        }
    }

    /**
     * Method to find those widget settings which has image as value. At the moment we have a group of settings,
     * [tect,
     * url,
     * image]
     *
     * It iterate throw widget setting list and find those with images and instert those which have value in an array.
     *
     * @param $widgetSettings
     * @return array
     */
    public function findWidgetSettingWithImages($widgetSettings): array
    {
        $slidesToDisplayArray = [];

        for ($settingNumber = 2; $settingNumber <= count($widgetSettings) - 2; $settingNumber += 3) {
            $settingValue = null;

            if (property_exists($widgetSettings[$settingNumber], 'value') ?
                $widgetSettings[$settingNumber]->value :
                $widgetSettings[$settingNumber]->default_value) {
                $settingValue = $widgetSettings[$settingNumber]->value;
            }

            if ($settingValue != null && $settingValue != "") {
                array_push($slidesToDisplayArray, $widgetSettings[$settingNumber - 2]);
            }
        }
        return $slidesToDisplayArray;
    }

}
