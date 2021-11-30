<?php namespace Notion\Objects;

use Notion\ObjectBase;

class Collection extends ObjectBase
{
    public $object = 'list';

    public $pages = [];

    public $databases = [];

    public $blocks = [];

    public function handleResponse($data)
    {
        foreach ($data->results as $item) {
            $instance = $this->notion->toResponse($item);
            if ($item->object === 'page') {
                $this->pages[] = $instance;
            } elseif ($item->object === 'database') {
                $this->databases[] = $instance;
            } elseif ($item->object === 'block' && in_array($item->type, $this->notion->blockTypes)) {
                $this->blocks[] = $this->notion->toBlockItem($item);
            }
        }
    }
}
