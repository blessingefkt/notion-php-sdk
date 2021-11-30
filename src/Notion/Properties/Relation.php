<?php namespace Notion\Properties;

use Notion\PropertyBase;

class Relation extends PropertyBase
{
    public function set($value): void
    {
        if (is_scalar($value)) {
            $this->config->{$this->config->type} = [
                (object)['id' => $value]
            ];
        } elseif (isset($value['id']) || isset($value->id)) {
            $this->config->{$this->config->type} = [(object)$value];
        } else
            $this->config->{$this->config->type} = $value;
    }

    public function toPageValue()
    {
        if (empty($this->config->relation))
            return null;
        if (is_array($this->config->relation))
            return [
                'relation' => $this->config->relation
            ];
        if (isset($this->config->relation->id))
            return [
                'relation' => [
                    $this->config->relation
                ]
            ];
        return [
            'relation' => []
        ];
    }

}
