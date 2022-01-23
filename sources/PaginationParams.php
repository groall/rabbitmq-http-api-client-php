<?php

/**
 * The pagination can be applied to the endpoints that list:
 *   queues
 *   exchanges
 *   connections
 *   channels
 */
class PaginationParams
{
    /**
     * Page number
     *
     * @var int
     */
    public $page = 0;

    /**
     * Number of elements for page (default value: 100)
     *
     * @var int
     */
    public $pageSize = 0;

    /**
     * Filter by name, for example queue name, exchange name etc.
     *
     * @var string
     */
    public $name = '';

    /**
     * Enables regular expression for the param name
     *
     * @var bool
     */
    public $useRegex = false;

    /**
     * Return array to send it as a query in a request
     * @return array
     */
    public function asArray()
    {
        return [
            'page' => $this->page,
            'page_size' => $this->pageSize,
            'name' => $this->name,
            'use_regex' => $this->useRegex ? 'true' : 'false',
        ];
    }
}