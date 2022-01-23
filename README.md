# Simple RabbitMQ Management API PHP Client

This library is intended to help manage of RabbitMQ server in a PHP application.

RabbitMQ Management API provided by the [RabbitMQ Management Plugin](https://www.rabbitmq.com/management.html)

### Install

```bash
composer require groall/rabbitmq-http-api-client-php
```

### Quick start

```php
$client = new \RabbitMqHttpApiClient('localhost', 15672, 'login', 'password');
$queueInfo = $client->queueInfo('/', 'queueName');
```
