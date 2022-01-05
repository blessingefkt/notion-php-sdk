<?php namespace Notion\Properties;

use Notion\ObjectBase;
use Notion\PropertyBase;

class Relation extends PropertyBase
{
    public $value = [];

    public function __construct($name, $config)
    {
        parent::__construct($name, $config);
        if (is_array($this->config->relation))
            $this->value = $this->config->relation;
        elseif (isset($this->config->relation->id))
            $this->value = [$this->normalizeValue($this->config->relation)];
    }

    public function add($value)
    {
        $this->value[] = $this->normalizeValue($value);
    }

    protected function normalizeValue($value)
    {
        if ($value instanceof ObjectBase) {
            return (object)['id' => $value->id];
        }
        if (is_object($value) && isset($value->id))
            return $value;
        elseif (is_scalar($value)) {
            return (object)['id' => $value];
        } elseif ($id = data_get($value, 'id')) {
            return (object)['id' => $id];
        }
        return $value;
    }

    public function set($value): void
    {
        if (isset($value))
            $this->add($value);
        else
            $this->value = null;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function toPageValue()
    {
        return isset($this->value)
            ? ['relation' => $this->value]
            : null;
    }

}
