<?php namespace Notion\Resources;

use Notion\Objects\Block as BlockObject;

class Block extends Resource
{
    public $endpoint = 'blocks';

    public function patch(): BlockObject
    {
        $object = new BlockObject([], $this->notion);
        $object->id = $this->id;
        return $object;
    }

    public function get(): BlockObject
    {
        if (!isset($this->id))
            return new BlockObject([], $this->notion);

        return parent::get();
    }

    public function sendRequest()
    {
        return $this->notion->getRequest()
            ->filter($this->filter)
            ->endpoint($this->endpoint)
            ->method($this->method)
            ->get($this->id);
    }
}
