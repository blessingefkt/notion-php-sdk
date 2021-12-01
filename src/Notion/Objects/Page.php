<?php namespace Notion\Objects;

use Notion\BlockBase;
use Notion\ObjectBase;

class Page extends ObjectBase
{
    protected $childEndpoint = 'blocks';
    protected $endpoint = 'pages';
    /**
     * @var BlockBase[]
     */
    public $children = [];

    public $parent;

    public function get(): self
    {
        $client = $this->notion->getClient();

        $response = $client->get($this->endpoint . '/' . $this->id);

        $data = $response->getJson();

        $this->handleResponse($data);

        return $this;
    }

    public function setParent($type, $id): self
    {
        $this->parent[$type . '_id'] = $id;

        return $this;
    }

    public function addBlock($block): self
    {
        $this->children[] = $block;
        return $this;
    }

    public function prepareForRequest()
    {
        $data = [
            'properties' => [],
        ];

        if (isset($this->parent))
            $data['parent'] = $this->parent;

        foreach ($this->properties as $property) {
            $value = $property->toPageValue();
            if (!$value) {
                continue;
            }
            $data['properties'][$property->name] = $value;
        }

        if (count($this->children) > 0) {
            $data['children'] = $this->getChildrenRequestData();
        }

        return $data;
    }

    public function getProperty($property)
    {
        $property = $this->normalizePropertyName($property);
        return $this->properties[$property] ?? null;
    }

    public function __get($property)
    {
        $property = $this->normalizePropertyName($property);
        if (!isset($this->properties[$property])) {
            return $this->{$property};
        }

        return $this->properties[$property]->value();
    }

    public function __set($property, $value)
    {
        $property = $this->normalizePropertyName($property);
        if (!isset($this->properties[$property])) {
            $this->{$property} = $value;
            return;
        }

        $this->properties[$property]->set($value);
    }

    public function __isset($property)
    {
        $property = $this->normalizePropertyName($property);
        return isset($this->properties[$property]);
    }

    public function save()
    {
        $options = [
            'body' => json_encode($this->prepareForRequest()),
        ];

        if (!$this->id) {
            return $this->notion->getClient()->post($this->endpoint, $options);
        }

        return $this->notion->getClient()->patch($this->endpoint . '/' . $this->id, $options);
    }

    public function setContext($context): self
    {
        $this->context = $context;

        return $this;
    }

    public function children()
    {
        $response = $this->notion->getClient()->get($this->childEndpoint . '/' . $this->id . '/children');
        return new Collection($response->getJson(), $this->notion);
    }

    public function appendChildren()
    {
        $client = $this->notion->getClient();
        $options = [
            'body' => json_encode(['children' => $this->getChildrenRequestData()]),
        ];
        $result = $client->patch($this->childEndpoint . '/' . $this->id . '/children', $options);
        return $this->notion->toResponse($result);
    }

    protected function getChildrenRequestData()
    {
        $data = [];
        if (count($this->children) > 0) {
            foreach ($this->children as $child) {
                $data[] = $child->get();
            }
        }
        return $data;
    }

    /**
     * @param $property
     * @return mixed|string
     */
    protected function normalizePropertyName($property)
    {
        if (isset($this->propertyAliases[$property]))
            return $this->propertyAliases[$property];
        return $property;
    }

}
