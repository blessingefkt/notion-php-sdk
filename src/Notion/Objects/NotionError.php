<?php

namespace Notion\Objects;

class NotionError extends \Exception
{

    public string $errorCode;

    /**
     * @param string $errorCode
     */
    public function __construct(\stdClass $data, \Throwable $throwable = null)
    {
        parent::__construct($data->message, intval($data->status ?? 500), $throwable);
        $this->errorCode = $data->code;
    }

}
