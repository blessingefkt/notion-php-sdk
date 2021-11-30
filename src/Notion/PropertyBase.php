<?php namespace Notion;

class PropertyBase
{
    /**
     * @var callable
     */
    public static $idEncoder = null;
    public $name;
    public $id;
    public $encodedId;
    public $alias;
    /**
     * @var \stdClass
     */
    public $config;

    protected $filterType = 'text';

    public function __construct($name, $config)
    {
        $this->name = $name;
        $this->config = $config;
        if ($config->id)
            [$this->id, $this->encodedId] = self::normalizeId($config->id);
    }

    public static function normalizeId(string $idFromApi)
    {
        $decoded = urldecode($idFromApi);
        if (!empty(static::$idEncoder))
            $encoded = call_user_func(static::$idEncoder, $decoded);
        else
            $encoded = $idFromApi;
        return [$decoded, $encoded];
    }

    public function value()
    {
        return $this->config->{$this->config->type};
    }

    public function set($value): void
    {
        $this->config->{$this->config->type} = $value;
    }


    public function getValue()
    {
        return null;
    }

    public function get()
    {
        $value = json_decode(json_encode($this->getValue()), true);

        if (!$value) {
            return null;
        }

        return [
            $this->config->type => $value,
        ];
    }

    public function toPageValue()
    {
        return $this->get();
    }
}
