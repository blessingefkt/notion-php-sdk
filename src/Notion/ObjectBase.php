<?php namespace Notion;

use Illuminate\Support\Str;

class ObjectBase
{
    public $id;

    public $icon;

    protected $nextCursor;

    protected $hasMore;

    protected $object;

    protected Notion $notion;

    protected $title;
    /**
     * @var  PropertyBase[] $properties
     */
    public $properties = [];

    protected $propertyAliases = [];

    public function __construct($data, $notion)
    {
        $this->notion = $notion;

        if (!$data) {
            return;
        }

        $this->handleResponse($data);
    }

    protected function handleResponse($data)
    {
        $this->setProperties($data);
    }

    protected function setProperties($data): void
    {
        $this->id = $data->id;
        $this->created_time = $data->created_time;
        $this->last_edited_time = $data->last_edited_time;

        if (isset($data->archived)) {
            $this->archived = $data->archived;
        }

        $this->indexProperties($data->properties);
    }

    public function indexProperties($data)
    {
        $this->propertyAliases = [];
        $this->properties = [];
        foreach ($data as $label => $property) {
            if ($property instanceof PropertyBase) {
                $propertyObj = clone $property;
                $propertyObj->config = clone $property->config;
                $alias = $property->alias;
            } else {
                $propertyObj = $this->createNewProperty($property->name ?: $label, $property);
                $alias = Str::camel($label);
            }
            $propertyObj->alias = $alias;
            $this->propertyAliases[$propertyObj->config->id] = $alias;
            $this->propertyAliases[$label] = $alias;
            $this->propertyAliases[$propertyObj->encodedId] = $alias;
            $this->properties[$alias] = $propertyObj;
        }
    }

    public function initProperties($data): self
    {
        $this->indexProperties($data);
        return $this;
    }

    /**
     * @param $label
     * @param $property
     * @return PropertyBase
     */
    protected function createNewProperty($label, $property)
    {
        return $this->notion->toPageProperty($label, $property);
    }
}
