<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Broker;


class Message implements \JsonSerializable
{
    public const JSON = 'application/json';

    /** @var string */
    private $rawBody;

    /** @var array */
    private $params;

    private $jsonData = [];

    /** @var string */
    private $contentEncoding;

    private function __construct()
    {}

    public static function fromRawBody(string $rawBody, string $contentEncoding, array $params = []): Message
    {
        $msg = new static();
        $msg->rawBody = $rawBody;
        $msg->params = $params;
        $msg->contentEncoding = $contentEncoding;
        return $msg;
    }

    public static function fromJsonData(array $data, array $params = []): Message
    {
        $msg = new static();
        $msg->jsonData = $data;
        $msg->contentEncoding = static::JSON;
        return $msg;
    }

    public function isJson(): bool
    {
        return $this->contentEncoding === static::JSON || ! empty($this->jsonData);
    }

    public function isEmpty(): bool
    {
        return empty($this->rawBody) && empty($this->jsonData);
    }

    public function jsonSerialize()
    {
        if ($this->isEmpty()) {
            return [];
        }

        if ( ! empty($this->jsonData)) {
            return $this->jsonData;
        }

        return json_decode($this->rawBody, true, JSON_THROW_ON_ERROR);
    }
}