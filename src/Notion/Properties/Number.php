<?php namespace Notion\Properties;

use Notion\PropertyBase;
use Notion\Traits\HasSimpleValue;

class Number extends PropertyBase
{
    use HasSimpleValue;

    public function set($value): void
    {
        if (is_string($value)) {
            $value = floatval($value);
        }
        $this->config->{$this->config->type} = $value;
    }

}
