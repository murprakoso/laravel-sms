<?php

namespace Yugo\SMSGateway\Vendors;

use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Yugo\SMSGateway\Interfaces\SMS;

class Zenziva implements SMS
{
    /**
     * API base URL.
     *
     * @var string
     */
    private $baseUrl = 'https://reguler.zenziva.net/apps';

    /**
     * Default userkey from Zenziva.
     *
     * @var string
     */
    private $userkey = null;

    /**
     * Default passkey from Zenziva.
     *
     * @var string
     */
    private $passkey = null;

    public function __construct()
    {
        $this->userkey = config('message.zenziva.userkey');
        $this->passkey = config('message.zenziva.passkey');
    }

    /**
     * Send message using Zenziva API.
     *
     * @param array  $destinations
     * @param string $message
     *
     * @return array|null
     */
    public function send(array $destinations, string $message): ?array
    {
        if (!empty($destinations)) {
            $destination = $destinations[0];
        }

        $query = http_build_query([
            'userkey' => $this->userkey,
            'passkey' => $this->passkey,
            'nohp'    => $destination,
            'pesan'   => $message,
        ]);

        $response = Request::get($this->baseUrl.'/smsapi.php?'.$query);

        $xml = simplexml_load_string($response->body);
        $body = json_decode(json_encode($xml), true);

        if (!empty($body['message']) and $body['message']['status'] != 0) {
            Log::error($body['message']['text']);
        }

        return $body ?? null;
    }

    /**
     * Check credit balance.
     *
     * @return array|null
     */
    public function credit(): ?array
    {
        $query = http_build_query([
            'userkey' => $this->userkey,
            'passkey' => $this->passkey,
        ]);

        $response = Request::get($this->baseUrl.'/smsapibalance.php?'.$query);

        $xml = simplexml_load_string($response->body);
        $body = json_decode(json_encode($xml), true);

        return $body ?? null;
    }
}
