<?php namespace Notion\Objects;

use Notion\ObjectBase;
use Notion\PropertyBase;
use Notion\RichText;

class Database extends ObjectBase
{
    const ID_SEPARATOR = '|';
    public $id;

    public $name;

    protected function handleResponse($data): void
    {
        $this->setProperties($data);

        $title = new RichText($data->title);
        $this->name = $title->plain_text;
    }

    protected function indexProperty($property, $label): PropertyBase
    {
        $property = parent::indexProperty($property, $label);
        $property->qualifiedId = $this->id . self::ID_SEPARATOR . $property->config->id;
        return $property;
    }


    public function newPage()
    {
        return (new Page(null, $this->notion))
            ->setParent('database', $this->id)
            ->initProperties($this->properties);
    }
}
