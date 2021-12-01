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

        if (isset($data->properties)) {
            $this->indexProperties($data->properties);
        } else {
            error_log('Missing properties: ' . $data->id . ' ' . json_encode($data, JSON_PRETTY_PRINT));
        }

        if (isset($data->url)) {
            $this->url = $data->url;
        }

        if (isset($data->cover)) {
            $this->cover = $data->cover;
        }

        if (isset($data->icon)) {
            $this->icon = $data->icon;
        }

        if (isset($data->parent)) {
            $this->parent = $data->parent;
        }
    }

    public function indexProperties($data)
    {
        $this->propertyAliases = [];
        $this->properties = [];
        foreach ($data as $label => $property) {
            $this->indexProperty($property, $label);
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

    /**
     * @param $property
     * @param $label
     */
    protected function indexProperty($property, $label): PropertyBase
    {
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
        return $propertyObj;
    }
}
