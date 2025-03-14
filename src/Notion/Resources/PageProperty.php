<?php

namespace Notion\Resources;

use Notion\Notion;
use Notion\Objects\NotionError;
use Notion\PropertyBase;

class PageProperty extends Resource
{
    public $endpoint = 'pages';
    public $pageId;

    public function __construct(Notion $notion, $pageId, $id)
    {
        parent::__construct($notion, $id);
        $this->pageId = $pageId;
        $this->endpoint = 'pages/' .$pageId. '/properties/' . urlencode($id);
    }

    /**
     * @return PropertyBase|NotionError
     */
    public function get()
    {
        if (empty($this->id) || empty($this->pageId))
            return null;

        $response = $this->sendRequest();
        return $this->notion->toPropertyResponse($response);
    }

    /**
     * @return mixed
     */
    public function sendRequest()
    {
        return $this->notion->getRequest()
            ->filter($this->filter)
            ->endpoint($this->endpoint)
            ->method($this->method)
            ->get();
    }
}
