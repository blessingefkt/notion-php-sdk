<?php namespace Notion\Http;

class Request
{
	protected \Notion\Http\Client $client;

	public $body;
	public $options = [];
	public $method = 'get';
	public $endpoint;

	protected $result;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function filter($filter)
	{
		if (!empty($filter)) {
			$this->body['filter'] = [];
		}

		if (isset($filter['and'])) {
			$this->body['filter']['and'] = $filter['and'];
		}

		if (isset($filter['or'])) {
			$this->body['filter']['or'] = $filter['or'];
		}

		return $this;
	}

	public function endpoint($endpoint)
	{
		$this->endpoint = $endpoint;
		return $this;
	}

	public function method($method)
	{
		$this->method = $method;
		return $this;
	}

	public function get($id = null)
	{
		if ($this->body) {
			$this->options = [
				'body' => json_encode($this->body),
			];
		}

		$endpoint = $this->getEndpoint($id);
		$response = $this->client->{$this->method}($endpoint, $this->options);

		return $response->getJson();
	}


	public function query($query)
	{
		if (isset($query))
			$this->body['query'] = $query;
		else
			unset($this->body['query']);
		return $this;
	}

	public function setFilter(array $data)
	{
		$this->body['filter'] = $data;
		return $this;
	}

	protected function getEndpoint($id)
	{
		if ($id && !str_contains($this->endpoint, $id)) {
			$this->endpoint .= '/' . $id;
		}
		return $this->endpoint;
	}
}
