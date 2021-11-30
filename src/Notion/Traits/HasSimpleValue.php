<?php namespace Notion\Traits;
use Notion\PropertyBase;

/**
 * @mixin PropertyBase
 */
trait HasSimpleValue
{

    public function value()
    {
        return $this->config->{$this->config->type};
    }

    public function set($value): void
    {
        $this->config->{$this->config->type} = $value;
    }


    public function getValue()
    {
        return $this->value();
    }
}
