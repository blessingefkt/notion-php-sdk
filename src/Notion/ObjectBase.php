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

        foreach ($data->properties as $label => $property) {
            $propertyObj = $this->createNewProperty($label, $property);
            $propertyObj->alias = Str::camel($label);
            $this->properties[$propertyObj->alias] = $propertyObj;
        }
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
