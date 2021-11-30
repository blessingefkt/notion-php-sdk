<?php namespace Notion;

use Notion\Blocks\{Heading1Block, Heading2Block, Heading3Block, ParagraphBlock, TodoBlock};
use Notion\Http\{Client, Request};
use Notion\Objects\{Collection, NotionError};
use Notion\Properties\{Checkbox,
    CreatedBy,
    CreatedTime,
    Date,
    Email,
    File,
    Formula,
    LastEditedBy,
    LastEditedTime,
    MultiSelect,
    Number,
    People,
    PhoneNumber,
    Relation,
    RichText,
    Rollup,
    Select,
    Title,
    URL
};
use Notion\Resources\{Block, Database, Page, PageProperty, Search};

class Notion
{
    public $blockTypes = [
        'heading_1',
        'heading_2',
        'heading_3',
        'paragraph',
        'todo',
    ];
    protected $client;

    public function __construct($token)
    {
        $this->client = new Client($token);
    }

    public function getRequest(): Request
    {
        return new Request($this->client);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function search(): Search
    {
        return new Search($this);
    }

    public function block($id = null)
    {
        return new Block($this, $id);
    }


    public function database($id = null): Database
    {
        return new Database($this, $id);
    }

    public function page($id = null): Page
    {
        return new Page($this, $id);
    }

    public function pageProperty($pageId, $property)
    {
        return new PageProperty($this, $pageId, $property);
    }

    public function toResponse($response)
    {
        if ($response->object === 'list') {
            return new Collection($response, $this);
        } elseif ($response->object === 'error') {
            return new NotionError($response);
        } elseif ($response->object === 'page') {
            return new Objects\Page($response, $this);
        } elseif ($response->object === 'block') {
            return $this->toBlockItem($response);
        } elseif ($response->object === 'database') {
            return new Objects\Database($response, $this);
        }
        return $response;
    }

    public function toPropertyResponse($response)
    {
        if ($response->object === 'error') {
            return new NotionError($response);
        } elseif ($response->object === 'list') {
            return $this->toPropertyResponse($response->results[0]);
        } elseif ($response->object === 'block') {
            return new Objects\Block($response, $this);
        } else {
            return $this->toPageProperty('', $response);
        }
    }


    public function toPageProperty($label, $property)
    {
        $type = null;
        if (isset($property->config))
            $type = $property->config->type;
        elseif (isset($property->type))
            $type = $property->type;

        switch ($type) {
            case 'title':
                return new Title($label, $property);
            case 'relation':
                return new Relation($label, $property);
            case 'checkbox':
                return new Checkbox($label, $property);
            case 'created_by':
                return new CreatedBy($label, $property);
            case 'created_time':
                return new CreatedTime($label, $property);
            case 'date':
                return new Date($label, $property);
            case 'email':
                return new Email($label, $property);
            case 'file':
                return new File($label, $property);
            case 'formula':
                return new Formula($label, $property);
            case 'last_edited_by':
                return new LastEditedBy($label, $property);
            case 'last_edited_time':
                return new LastEditedTime($label, $property);
            case 'multi_select':
                return new MultiSelect($label, $property);
            case 'number':
                return new Number($label, $property);
            case 'people':
                return new People($label, $property);
            case 'phone_number':
                return new PhoneNumber($label, $property);
            case 'rich_text':
                return new RichText($label, $property);
            case 'rollup':
                return new Rollup($label, $property);
            case 'select':
                return new Select($label, $property);
            case 'url':
                return new URL($label, $property);
            default:
                return new PropertyBase($label, $property);
        }
    }

    /**
     * @param object $item
     * @return Heading1Block|Heading2Block|Heading3Block|ParagraphBlock|TodoBlock
     */
    public function toBlockItem($item)
    {
        $richTextData = $item->{$item->type}->text;
        $richText = new \Notion\RichText($richTextData);
        switch ($item->type) {
            case 'heading_1':
                $block = new Heading1Block($richText);
                break;
            case 'heading_2':
                $block = new Heading2Block($richText);
                break;
            case 'heading_3':
                $block = new Heading3Block($richText);
                break;
            case 'paragraph':
                $block = new ParagraphBlock($richText);
                break;
            case 'todo':
                $block = new TodoBlock($richText);
                break;
        }
        return $block;
    }

    public static function isWritableProperty($type): bool
    {
        if (!is_scalar($type)) {
            if (isset($type->config))
                $type = $type->config->type;
            elseif (isset($type->type))
                $type = $type->type;
        }
        switch ($type) {
            case 'title':
            case 'relation':
            case 'checkbox':
            case 'date':
            case 'email':
            case 'file':
            case 'multi_select':
            case 'number':
            case 'people':
            case 'phone_number':
            case 'rich_text':
            case 'select':
            case 'url':
                return true;
            case 'created_by':
            case 'created_time':
            case 'formula':
            case 'last_edited_by':
            case 'last_edited_time':
            case 'rollup':
            default:
                return false;
        }
    }
}
