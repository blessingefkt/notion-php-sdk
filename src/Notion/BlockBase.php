<?php namespace Notion;

class BlockBase
{
    public $object = 'block';

    public $id = '';

    public $type = '';

    public $created_time = '';

    public $last_edited_time = '';

    public $has_children = false;

    public $typeConfiguration = [];

    public $plain_text = '';

    public function __construct($richText = null)
    {
        if ($richText instanceof RichText || (is_object($richText) && isset($richText->plain_text)))
            $this->plain_text = $richText->plain_text;
    }

    public static function make(string $type, array $configuation, string $id = null)
    {
        $block = new self();
        $block->type = $type;
        $block->typeConfiguration = $configuation;
        if (isset($id))
            $block->id = $id;
        return $block;
    }

    public function get(): array
    {
        $data = [
            'object' => $this->object,
            'type' => $this->type,
            $this->type => $this->typeConfiguration,
        ];

        if ($this->id) {
            $data['id'] = $this->id;
        }

        return $data;
    }
}
