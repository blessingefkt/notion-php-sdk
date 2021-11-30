<?php

namespace Notion\Resources;

use Notion\Objects\Collection;

class Search extends Resource
{
    public $typeFilter = [];
    protected $method = 'post';
    protected $endpoint = 'search';
    protected $query = null;


    public function query(string $query = null)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return mixed
     */
    public function sendRequest()
    {
        return $this->notion->getRequest()
            ->setFilter($this->typeFilter)
            ->query($this->query)
            ->endpoint($this->endpoint)
            ->method($this->method)
            ->get();
    }

    public function pages()
    {
        $this->typeFilter = [
            'property' => 'object',
            'value' => 'page'
        ];
        return $this;
    }

    public function databases()
    {
        $this->typeFilter = [
            'property' => 'object',
            'value' => 'database'
        ];
        return $this;
    }

    public function get(): Collection
    {
        $response = $this->sendRequest();
        return $this->notion->toResponse($response);
    }

}
