<?php

namespace Yugo\SMSGateway\Vendors\Smsgatewayme;

use Unirest\Request;
use Unirest\Request\Body;

class Callback
{
    /**
     * API URL.
     *
     * @var string
     */
    private $baseUrl = 'https://smsgateway.me/api/v4/';

    /**
     * Callback name.
     *
     * @var string
     */
    private $name;

    /**
     * Callback event name.
     * Available: received, sent, failed.
     *
     * @var string
     */
    private $event;

    /**
     * Hook URL.
     *
     * @var string
     */
    private $url;

    /**
     * Secret key for callback.
     *
     * @var string
     */
    private $secret;

    /**
     * Default device.
     *
     * @var int
     */
    private $device;

    /**
     * Authorization.
     *
     * @var string
     */
    private $token;

    public function __construct(int $device, string $token)
    {
        $this->device = $device;
        $this->token = $token;

        $this->secret = str_random(15);

        Request::defaultHeaders([
            'Accept' => 'application/json',
            'Authorization' => $this->token,
        ]);
    }

    /**
     * Set default name.
     *
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set default event.
     *
     * @param string $event
     * @return self
     */
    public function event(string $event): self
    {
        if (!in_array($event, ['received', 'sent', 'failed'])) {
            abort(500, sprintf('Event %s not available.', $event));
        }

        $this->event = $event;

        return $this;
    }

    /**
     * Set hook URL.
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * If not defined, secret key will generated automatically.
     *
     * @param string $secret
     * @return self
     */
    public function secret(string $secret = ''): self
    {
        if (empty($secret)) {
            $secret = str_random(15);
        }

        $this->secret = $secret;

        return $this;
    }

    /**
     * Store new callback to SMSGateway.me server.
     *
     * @return array|null
     */
    public function create(): ?array
    {
        $body = Body::json([
            'name' => $this->name,
            'event' => $this->event,
            'device_id' => $this->device,
            'filter_type' => 'contains',
            'filter' => 'stop',
            'method' => 'http',
            'action' => $this->url,
            'secret' => $this->secret,
        ]);
        $response = Request::post($this->baseUrl . 'callback', [], $body);

        return (array) $response->body;
    }

    /**
     * Get detailed information about callback.
     *
     * @param integer $id
     * @return array|null
     */
    public function info(int $id): ?array
    {
        $response = Request::get($this->baseUrl . 'callback/' . $id);

        return (array) $response->body ?? null;
    }
}
