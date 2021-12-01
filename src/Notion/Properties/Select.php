<?php namespace Notion\Properties;

use Illuminate\Support\Str;
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
            $this->config->select->color = $matchingOption->color;
        } else {
            unset($this->config->select->id, $this->config->select->name, $this->config->select->color);
            if (Str::isUuid($value))
                $this->config->select->id = $value;
            else {
                $this->config->select->name = $value;
            }
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
        return null;
    }

}
