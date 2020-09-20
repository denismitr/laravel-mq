# Laravel MQ

### WIP

### Sending messages
```php
$msg = Message::fromRawBody('{"foo":123,"bar":"baz"}', 'application/json');
$this->broker->connection('some-connection')->target('some-exchange')->send($msg);
```