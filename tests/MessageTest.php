<?php

declare(strict_types=1);

namespace Denismitr\LaravelMQ\Tests;


use Denismitr\LaravelMQ\Broker\Message;

class MessageTest extends BaseTestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_raw_data()
    {
        $msg = Message::fromRawBody('{"foo":123,"bar":"baz"}', 'application/json');

        $this->assertInstanceOf(Message::class, $msg);
        $this->assertTrue($msg->isJson());
        $this->assertFalse($msg->isEmpty());
        $this->assertEquals([
            'foo' => 123,
            'bar' => 'baz'
        ], $msg->jsonSerialize());
        $this->assertEquals('{"foo":123,"bar":"baz"}', $msg->rawBody());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_json_data()
    {
        $msg = Message::fromJsonData([
            'foo' => 123,
            'bar' => 'baz',
        ]);

        $this->assertInstanceOf(Message::class, $msg);
        $this->assertTrue($msg->isJson());
        $this->assertFalse($msg->isEmpty());
        $this->assertEquals([
            'foo' => 123,
            'bar' => 'baz'
        ], $msg->jsonSerialize());
        $this->assertEquals('{"foo":123,"bar":"baz"}', $msg->rawBody());
    }
}