<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;


class BattlereportClient
{
    private $client;

    public function __construct()
    {
        $this->setClient();
    }

    public function setClient()
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'max_retry_attempts' => 5,
        ]));

        $this->client = new Client([
             'handler' => $stack,
             'http_errors'     => false,
             'connect_timeout' => 20,
             'timeout'         => 20,
             'headers' => [
                 'User-Agent' => 'Battlereport Node/1.0',
                 'X-AjaxNavigation' => 1,
                 'Accept-Encoding'  => 'gzip'
             ],
             'decode_content' => true,
             'verify' => false
         ]);
    }

    public function getClient()
    {
        return $this->client;
    }
}
