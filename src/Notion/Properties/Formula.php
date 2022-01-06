<?php namespace Notion\Properties;

use Notion\PropertyBase;

class Formula extends PropertyBase
{
    public function toFlatValue()
    {
        $value = $this->value();
        if (isset($value[0]->type))
            return $value[0]->{$value[0]->type};
        return $value;
    }

    public function value()
    {
        return $this->config->formula->{$this->config->formula->type};
    }
}
