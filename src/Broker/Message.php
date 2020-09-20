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

    /**
     * @var int|null
     */
    private $attempt;

    private function __construct()
    {}

    public static function fromRawBody(string $rawBody, string $contentEncoding, ?int $attempts = null, array $params = []): Message
    {
        $msg = new static();
        $msg->rawBody = $rawBody;
        $msg->params = $params;
        $msg->contentEncoding = $contentEncoding;
        $msg->attempt = $attempts;

        return $msg;
    }

    public static function fromJsonData(array $data, ?int $attempts = null, array $params = []): Message
    {
        $msg = new static();
        $msg->jsonData = $data;
        $msg->contentEncoding = static::JSON;
        $msg->attempt = $attempts;

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

    public function hasAttempt(): bool
    {
        return ! is_null($this->attempt);
    }

    public function attempt(): int
    {
        return (int) $this->attempt;
    }

    /**
     * @return array|mixed
     * @throws \JsonException
     */
    public function jsonSerialize()
    {
        if ($this->isEmpty()) {
            return [];
        }

        if ( ! empty($this->jsonData)) {
            return $this->jsonData;
        }

        return json_decode($this->rawBody, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return string|null
     * @throws \JsonException
     */
    public function rawBody(): ?string
    {
        if ($this->jsonData) {
            return json_encode($this->jsonData, JSON_THROW_ON_ERROR);
        }

        return $this->rawBody;
    }

    public function contentEncoding(): string
    {
        return $this->contentEncoding;
    }
}