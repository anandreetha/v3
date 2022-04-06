<?php
/**
 * Created by PhpStorm.
 * User: MSeymour
 * Date: 04/01/2018
 * Time: 09:52
 */

namespace Multiple\Core\Misc;

use ArrayIterator;

class SettingsMapper extends ArrayIterator
{
    public function __construct(SettingVo ...$settingKeyValues)
    {
        parent::__construct($settingKeyValues);
    }

    public function current(): SettingVo
    {
        return parent::current();
    }

    public function offsetGet($offset): SettingVo
    {
        return parent::offsetGet($offset);
    }

    public function getByKey($settingId)
    {
        $this->rewind();

        while ($this->valid()) {
            $settingVo = $this->current();

            if ($settingVo->getId() == $settingId) {
                return $settingVo;
            }

            $this->next();
        }
        return new SettingVo();
    }

    public function getValueByKey($settingId)
    {
        $this->rewind();

        while ($this->valid()) {
            $settingVo = $this->current();

            if ($settingVo->getId() == $settingId) {
                return $settingVo->getValue();
            }

            $this->next();
        }
        return null;
    }

    public function getByName($settingName)
    {
        $this->rewind();

        while ($this->valid()) {
            $settingVo = $this->current();

            if ($settingVo->getName() == $settingName) {
                return $settingVo;
            }

            $this->next();
        }
        return new SettingVo();
    }

    public function getValueByName($settingName)
    {
        $this->rewind();

        while ($this->valid()) {
            $settingVo = $this->current();

            if ($settingVo->getName() == $settingName) {
                return html_entity_decode($settingVo->getValue());
            }
            $this->next();
        }
        return null;
    }

    public function sortByDisplayOrder()
    {
        $this->rewind();

        $this->uasort(array($this,"displayOrderCmp"));
    }

    protected function displayOrderCmp($item1, $item2)
    {
        return $item1->getDisplayOrder() <=> $item2->getDisplayOrder();
    }

}