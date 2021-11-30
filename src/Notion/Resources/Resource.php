<?php namespace Notion\Resources;

use Notion\Notion;
use Notion\Objects\Page;
use Notion\Http\Request;
use Notion\Objects\Database;
use Notion\Traits\Filterable;
use Notion\Objects\Collection;

class Resource
{
    use Filterable;

    protected Notion $notion;

    protected $id;

    protected $endpoint = '';

    protected $method = 'get';

    public $created_time;

    public $last_edited_time;

    public $archived = false;

    public $properties = [];

    public $parent_type;

    public $parent_id;

    public function __construct(Notion $notion, $id = null)
    {
        $this->notion = $notion;

        $this->id = $id;
    }

    public function get()
    {
        $response = $this->getRequest();
        return $this->notion->toResponse($response);
    }

    /**
     * @param \Notion\Http\Client $client
     * @return mixed
     */
    public function getRequest()
    {
        return $this->notion->getRequest()
            ->filter($this->filter)
            ->endpoint($this->endpoint)
            ->method($this->method)
            ->get($this->id);
    }
}
