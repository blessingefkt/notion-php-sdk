<?php namespace Notion\Properties;

use Notion\PropertyBase;

class Date extends PropertyBase
{
    public function value()
    {
        return $this->config->date->start;
    }

    public function set($value): void
    {
        if (is_object($value) && isset($value->start))
            $this->config->date = $value;
        else {
            if (!isset($this->config->date))
                $this->config->date = (object)['start' => null, 'end' => null];
            $this->config->date->start = $value;
            unset($this->config->date->end);
        }
    }

    public function getValue()
    {
        return $this->config->date;
    }
}
