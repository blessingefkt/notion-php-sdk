<?php namespace Notion\Properties;

use Notion\PropertyBase;

class Select extends PropertyBase
{

    public function value()
    {
        return $this->config->select->name;
    }

    public function set($value): void
    {
        $matchingOption = collect($this->config->select->options ?: [])->firstWhere('id', $value);
        if ($matchingOption) {
            $this->config->select->id = $matchingOption->id;
            $this->config->select->name = $matchingOption->name;
            $this->config->select->color = $this->config->select->color;
        } else {
            $this->config->select->name = $value;
            unset($this->config->select->id, $this->config->select->color);
        }
    }

    public function get()
    {
        return $this->toPageValue();
    }

    public function getValue()
    {
        return $this->config->select;
    }

    public function toPageValue()
    {
        if (isset($this->config->select->id))
            return [
                'select' => (object)[
                    'id' => $this->config->select->id
                ]
            ];
        if (isset($this->config->select->name))
            return [
                'select' => (object)[
                    'name' => $this->config->select->name
                ]
            ];
        return [
            'select' => null
        ];
    }

}
