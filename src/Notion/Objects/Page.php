<?php namespace Notion\Objects;

use Notion\BlockBase;
use Notion\ObjectBase;
use Notion\Properties\Relation;
use Notion\PropertyBase;

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

		$data['properties'] = $this->preparePropertiesForRequest($this->properties);

		if (count($this->children) > 0) {
			$data['children'] = $this->getChildrenRequestData();
		}

		return $data;
	}

	public function getRelation($property): ?Relation
	{
		$property = $this->normalizePropertyName($property);
		return $this->properties[$property] ?? null;
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

	public function patchProperties(iterable $properties)
	{
		if (!$this->id)
			throw new \Error('Page does not exist. Can not update.');
		$data = [
			'properties' => [],
		];

		if (isset($this->parent))
			$data['parent'] = $this->parent;

		$data['properties'] = $this->preparePropertiesForRequest($properties);

		$options = [
			'body' => json_encode($data),
		];

		$response = $this->notion->getClient()->patch($this->endpoint . '/' . $this->id, $options);
		$result = $response->getJson();
		if (isset($result->object) && $result->object === 'page') {
			$this->handleResponse($result);
		}
		return $this->notion->toResponse($result);
	}


	public function save()
	{
		$data = $this->prepareForRequest();
		$options = [
			'body' => json_encode($data),
		];

		if (!$this->id) {
			$result = $this->notion->getClient()->post($this->endpoint, $options);
		} else {
			$result = $this->notion->getClient()->patch($this->endpoint . '/' . $this->id, $options);
		}
		return $this->notion->toResponse($result->getJson());
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

	/**
	 * @param PropertyBase[] $properties
	 * @return array
	 */
	public function preparePropertiesForRequest(iterable $properties): array
	{
		$propertiesPayload = [];
		foreach ($properties as $property) {
			$value = $property->toPageValue();
			if (!isset($value)) {
				continue;
			}
			$propertiesPayload [$property->name] = $value;
		}
		return $propertiesPayload;
	}

}
