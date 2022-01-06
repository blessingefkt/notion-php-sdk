<?php namespace Notion\Properties;

use Notion\PropertyBase;
use Notion\Traits\HasSimpleValue;

class Checkbox extends PropertyBase
{
    use HasSimpleValue;

    public function toFlatValue()
    {
        return $this->getValue() ? 1 : 0;
    }

    public function getValue()
    {
        if (!is_bool($this->config->checkbox)) {
            return false;
        }

        return (boolean)$this->config->checkbox;
    }
}
