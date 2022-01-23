<?php

/**
 * Client HTTP API RabbitMQ
 * API Doc http://www.rabbitmq.com/management.html
 * API Help http://hg.rabbitmq.com/rabbitmq-management/raw-file/3646dee55e02/priv/www-api/help.html
 *
 * Class RabbitMqHttpApiClient
 */
class RabbitMqHttpApiClient
{
    /**
     * resource a cURL handle on success, false on errors.
     *
     * @var null|resource
     */
    private $curl;

    /**
     * URL HTTP API Rabbit MQ
     * Example: amqp02.abcp.ru
     *
     * @var string
     */
    private $rabbitMqApiHost;

    /**
     * @param string $rabbitMqApiHost Domain of RabbitMQ HTTP API
     * @param int $port Port of RabbitMQ HTTP API
     * @param string $login Login of RabbitMQ HTTP API
     * @param string $password Password of RabbitMQ HTTP API
     */
    public function __construct($rabbitMqApiHost, $port = 15672, $login = 'guest', $password = 'guest')
    {
        $this->curl = curl_init();
        $this->rabbitMqApiHost = $rabbitMqApiHost;
        curl_setopt($this->curl, CURLOPT_PORT, $port);
        curl_setopt($this->curl, CURLOPT_USERPWD, "$login:$password");
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($this->curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    /**
     * Close the connection with RabbitMQ HTTP API
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * A list of all nodes
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listNodes()
    {
        return $this->requestGet('nodes');
    }

    /**
     * Details about an individual node.
     *
     * @param string $name Name of an individual node
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function nodeInfo($name)
    {
        return $this->requestGet('nodes/' . urlencode($name));
    }

    /**
     * A list of all extensions
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listExtensions()
    {
        return $this->requestGet('extensions');
    }

    /**
     * A list of all definitions
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listDefinitions()
    {
        return $this->requestGet('definitions');
    }

    /**
     * Update the definitions
     *
     * @param $defs
     * @throws BadMethodCallException
     */
    public function uploadDefinitions($defs)
    {
        // TODO need to rework/finish
        throw new BadMethodCallException();
    }

    /**
     * A list of all open connections.
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listConnections()
    {
        return $this->requestGet('connections');
    }

    /**
     * Details about an individual connection.
     *
     * @param string $name Name of an individual connection
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function connectionInfo($name)
    {
        return $this->requestGet('connections/' . urlencode($name));
    }

    /**
     * Сlose the connection.
     *
     * @param string $name Name of an individual connection
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function closeConnection($name)
    {
        $this->requestDelete('connections/' . urlencode($name));
    }

    /**
     * A list of all open channels.
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listChannels()
    {
        return $this->requestGet('channels');
    }

    /**
     * Details about an individual channel.
     *
     * @param string $name Name of an individual channel
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function channelInfo($name)
    {
        return $this->requestGet('channels/' . urlencode($name));
    }

    /**
     * A list of all exchanges.
     * A list of all exchanges in a given virtual host.
     *
     * @param null|string $vhost Name of an individual virtual host
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listExchanges($vhost = null)
    {
        $path = null === $vhost ? 'exchanges' : 'exchanges/' . urlencode($vhost);

        return $this->requestGet($path);
    }

    /**
     * PUT an exchange, you will need a body looking something like this:
     * {"type":"direct","auto_delete":false,"durable":true,"arguments":[]}
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @param array $attributes Attributes of exchange
     * @return array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function declareExchange($vhost, $exchange, array $attributes = [])
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        // TODO need to rework/finish
        return $this->requestPut('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange));
    }

    /**
     * Delete exchange.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function deleteExchange($vhost, $exchange)
    {
        $this->requestDelete('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange));
    }

    /**
     * Details about an individual exchange.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function exchangeInfo($vhost, $exchange)
    {
        return $this->requestGet('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange));
    }

    /**
     * A list of all bindings on a given exchange.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listExchangesBindings($vhost, $exchange)
    {
        return $this->requestGet('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange) . '/bindings');
    }

    /**
     * A list of all bindings on a given exchange by source.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listBindingsBySource($vhost, $exchange)
    {
        return $this->requestGet('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange) . '/bindings/source');
    }

    /**
     * A list of all bindings on a given exchange by destination.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $exchange Name of an individual exchange
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listBindingsByDestination($vhost, $exchange)
    {
        return $this->requestGet('exchanges/' . urlencode($vhost) . '/' . urlencode($exchange) . '/bindings/destination');
    }

    /**
     * A list of all queues.
     * A list of all queues in a given virtual host.
     *
     * @param null|string $vhost Name of an individual virtual host
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listQueues($vhost = null)
    {
        $path = null === $vhost ? 'queues' : 'queues/' . urlencode($vhost);

        return $this->requestGet($path);
    }

    /**
     * Details about an individual queue.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual queue
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function queueInfo($vhost, $name)
    {
        return $this->requestGet('queues/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * To PUT a queue, you will need a body looking something like this:
     * {"auto_delete":false,"durable":true,"arguments":[]}
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual queue
     * @param $attributes
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function declareQueue($vhost, $name, $attributes)
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        // TODO need to rework/finish
        return $this->requestPut('queues/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * Delete a queue
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual queue
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function deleteQueue($vhost, $name)
    {
        $this->requestDelete('queues/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * A list of all bindings on a given queue.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listQueueBindings($vhost, $queue)
    {
        return $this->requestGet('queues/' . urlencode($vhost) . '/' . urlencode($queue) . '/bindings');
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function purgeQueue($vhost, $queue)
    {
        // TODO need to rework/finish
        $this->requestDelete('queues/' . urlencode($vhost) . '/' . urlencode($queue) . '/contents');
        //Hashie::Mash.new
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @param array $options
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function getMessages($vhost, $queue, array $options)
    {
        $dataString = json_encode($options);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        return $this->requestPost('queues' . urlencode($vhost) . '/' . urlencode($queue) . '/get');
    }

    /**
     * A list of all bindings.
     * A list of all bindings in a given virtual host.
     *
     * @param null|string $vhost Name of an individual virtual host
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listBindings($vhost = null)
    {
        $path = null === $vhost ? 'bindings' : 'bindings/' . urlencode($vhost);

        return $this->requestGet($path);
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @param string $exchange
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listBindingsBetweenQueueAndExchange($vhost, $queue, $exchange)
    {
        return $this->requestGet('bindings/' . urlencode($vhost) . '/e/' . urlencode($exchange) . '/q/' . urlencode($queue));
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @param string $exchange Name of an individual exchange
     * @param string $propertiesKey
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function queueBindingInfo($vhost, $queue, $exchange, $propertiesKey)
    {
        return $this->requestGet('bindings/' . urlencode($vhost) . '/e/' . urlencode($exchange) . '/q/' . urlencode($queue) . '/' . urlencode($propertiesKey));
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue Name of an individual queue
     * @param string $exchange Name of an individual exchange
     * @param string $routingKey
     * @param array $arguments
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function bindQueue($vhost, $queue, $exchange, $routingKey, array $arguments = [])
    {
        $dataString = json_encode([
            'routing_key' => $routingKey,
            'arguments' => $arguments,
        ]);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        // TODO need to rework/finish
        //response['location']
        return $this->requestPost('bindings/' . urlencode($vhost) . '/e/' . urlencode($exchange) . '/q/' . urlencode($queue));
    }

    /**
     * @param string $vhost Name of an individual virtual host
     * @param string $queue
     * @param string $exchange
     * @param string $propertiesKey
     * @return mixed
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function deleteQueueBinding($vhost, $queue, $exchange, $propertiesKey)
    {
        // TODO need to rework/finish
        $response = $this->requestDelete('bindings/' . urlencode($vhost) . '/e/' . urlencode($exchange) . '/q/' . urlencode($queue) . '/' . urlencode($propertiesKey));

        return $response['success'];
    }

    /**
     * A list of all vhosts.
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listVhosts()
    {
        return $this->requestGet('vhosts');
    }

    /**
     * Details about an individual virtual host.
     *
     * @param string $name
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function vhostInfo($name)
    {
        return $this->requestGet('vhosts/' . urlencode($name));
    }

    /**
     * Create virtual host. As a virtual host only has a name, you do not need an HTTP body when PUTing one of these.
     *
     * @param string $name
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function createVhost($name)
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        return $this->requestPut('vhosts/' . urlencode($name));
    }

    /**
     * Delete virtual host
     *
     * @param string $name
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function deleteVhost($name)
    {
        $this->requestDelete('vhosts/' . urlencode($name));
    }

    /**
     * A list of all permissions for all users.
     * An individual permission of virtual host
     *
     * @param null|string $vhost Name of an individual virtual host
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listPermissions($vhost = null)
    {
        $path = null === $vhost ? 'permissions' : 'vhosts/' . urlencode($vhost) . '/permissions';

        return $this->requestGet($path);
    }

    /**
     * A list an individual permission of virtual host
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $user
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listPermissionsOf($vhost, $user)
    {
        return $this->requestGet('permissions/' . urlencode($vhost) . '/' . urlencode($user));
    }

    /**
     * An individual permission of a user and virtual host. To PUT a permission, you will need a body looking something like this:
     * {"scope":"client","configure":".*","write":".*","read":".*"}
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $user
     * @param array $attributes
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function updatePermissionsOf($vhost, $user, array $attributes)
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        return $this->requestPut('permissions/' . urlencode($vhost) . '/' . urlencode($user));
    }

    /**
     * Delete an individual permission of a user and virtual host.
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $user Name of an individual user
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function clearPermissionsOf($vhost, $user)
    {
        $this->requestDelete('permissions/' . urlencode($vhost) . '/' . urlencode($user));
    }

    /**
     * A list of all users.
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listUsers()
    {

        return $this->requestGet('users');
    }

    /**
     * Returns information about individual user.
     *
     * @param string $name Name of an individual user
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function userInfo($name)
    {

        return $this->requestGet('users/' . urlencode($name));
    }

    /**
     * Updates information about individual user.
     * To PUT a user, you will need a body looking something like this:
     * {"password":"secret"}
     *
     * @param string $name Name of an individual user
     * @param array $attributes
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function updateUser($name, array $attributes)
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        return $this->requestPut('users/' . urlencode($name));
    }

    /**
     * Delete information about individual user.
     *
     * @param string $name Name of an individual user
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function deleteUser($name)
    {

        $this->requestDelete('users/' . urlencode($name));
    }

    /**
     * Details of an user permission
     *
     * @param string $name Name of an individual user
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function userPermissions($name)
    {

        return $this->requestGet('users/' . urlencode($name) . '/permissions');
    }

    /**
     * Who am i ?
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function whoami()
    {

        return $this->requestGet('whoami');
    }

    /**
     * A list of all policies.
     * An individual policy of virtual host
     *
     * @param null|string $vhost Name of an individual virtual host
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listPolicies($vhost = null)
    {
        $path = null === $vhost ? 'policies' : 'policies/' . urlencode($vhost);

        return $this->requestGet($path);
    }

    /**
     * A list of policies of virtual host and name.
     *
     * @param string $vhost Name of an individual virtual host
     * @param null|string $name Name of an individual policy
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listPoliciesOf($vhost, $name = null)
    {
        $path = null === $name ? 'policies/' . urlencode($vhost) : 'policies/' . urlencode($vhost) . '/' . urlencode($name);

        return $this->requestGet($path);
    }

    /**
     * Update policy
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual policy
     * @param array $attributes
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function updatePoliciesOf($vhost, $name, array $attributes)
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        return $this->requestPut('policies/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * Delete policy
     *
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual policy
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function clearPoliciesOf($vhost, $name)
    {
        $this->requestDelete('policies/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * A list of all parameters of component
     *
     * @param null|string $component Name of component
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listParameters($component = null)
    {
        $path = null === $component ? 'parameters' : 'parameters/' . urlencode($component);

        return $this->requestGet($path);
    }

    /**
     * A list of all parameters of component and virtual host and name
     *
     * @param string $component Name of component
     * @param string $vhost Name of an individual virtual host
     * @param null|string $name Name of an individual parameter
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function listParametersOf($component, $vhost, $name = null)
    {
        $path = null === $name ? 'parameters/' . urlencode($component) . '/' . urlencode($vhost) : 'parameters/' . urlencode($component) . '/' . urlencode($vhost) . '/' . urlencode($name);

        return $this->requestGet($path);
    }

    /**
     * Update a parameters
     *
     * @param string $component Name of component
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual parameter
     * @param array $attributes
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function updateParametersOf($component, $vhost, $name, array $attributes)
    {
        $dataString = json_encode($attributes);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($dataString),
        ]);

        return $this->requestPut('parameters/' . urlencode($component) . '/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * Delete a parameters
     *
     * @param string $component Name of component
     * @param string $vhost Name of an individual virtual host
     * @param string $name Name of an individual parameter
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function clearParametersOf($component, $vhost, $name)
    {
        $this->requestDelete('parameters/' . urlencode($component) . '/' . urlencode($vhost) . '/' . urlencode($name));
    }

    /**
     * Run a aliveness test
     *
     * @param string $vhost Name of an individual virtual host
     * @return bool
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function alivenessTest($vhost)
    {
        $result = $this->requestGet('aliveness-test/' . urlencode($vhost));

        return $result['status'] === 'ok';
    }

    /**
     * Various random bits of information that describe the whole system.
     *
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function overview()
    {
        return $this->requestGet('overview');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Private methods
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Send request to RabbitMQ by OUT method
     *
     * @param string $path
     * @param array $requestVars
     * @return array
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function requestPut($path, array $requestVars = [])
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        $url = $this->buildGetUrl($this->getServiceUrl($path), []);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->buildPostBody($requestVars));

        return $this->execCurl();
    }

    /**
     * Send request to RabbitMQ by DELETE method
     *
     * @param string $path
     * @param array $requestVars
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private function requestDelete($path, array $requestVars = [])
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $url = $this->buildGetUrl($this->getServiceUrl($path), []);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->buildPostBody($requestVars));

        return $this->execCurl();
    }

    /**
     * Send request to RabbitMQ by POST method
     *
     * @param string $path
     * @param array $requestVars
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private function requestPost($path, array $requestVars = [])
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->getServiceUrl($path));
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->buildPostBody($requestVars));

        return $this->execCurl();
    }

    /**
     * Send request to RabbitMQ by GET method
     *
     * @param string $path
     * @param array $requestVars
     * @return array
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private function requestGet($path, array $requestVars = [])
    {
        $url = $this->buildGetUrl($this->getServiceUrl($path), $requestVars);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, false);

        return $this->execCurl();
    }

    /**
     * Execute request by CURL
     *
     * @return array
     * @throws RuntimeException
     */
    private function execCurl()
    {
        $response = curl_exec($this->curl);
        $responseInfo = curl_getinfo($this->curl);
        if ($responseInfo['content_type'] !== 'application/json') {
            throw new RuntimeException($response);
        }

        return $this->responseDecode($response);
    }

    /**
     * Service URL with path of operation
     *
     * @param string $path
     * @return string
     * @throws InvalidArgumentException
     */
    private function getServiceUrl($path)
    {
        if (empty($path)) {
            throw new InvalidArgumentException();
        }

        return "http://$this->rabbitMqApiHost/api/$path/";
    }

    /**
     * URL with GET-params
     *
     * @param string $url
     * @param array $requestVars
     * @return string
     */
    private function buildGetUrl($url, array $requestVars = [])
    {
        return $url . ($requestVars ? '?' . htmlspecialchars(http_build_query($requestVars)) : '');
    }

    /**
     * Build POST-body from request params
     *
     * @param array $data
     * @return string
     */
    private function buildPostBody(array $data = [])
    {
        return http_build_query($data);
    }

    /**
     * Decode response of RabbitMQ HTTP API
     *
     * @param string $response
     * @return array
     */
    protected function responseDecode($response)
    {
        return $this->getObjectsVars(json_decode($response));
    }

    /**
     * Recursive convert object to array
     *
     * @param object|array $oneResult
     * @return array
     */
    protected function getObjectsVars($oneResult)
    {
        if (is_object($oneResult)) {
            $oneResult = get_object_vars($oneResult);
        }
        if (is_array($oneResult)) {
            foreach ($oneResult as $key => $value) {
                $oneResult[$key] = $this->getObjectsVars($value);
            }
        }

        return $oneResult;
    }
}