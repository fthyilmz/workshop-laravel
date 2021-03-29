<?php

namespace App\Services\Clients;

use App\Models\Subscription;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client as GuzzleClient;

class Google extends Client
{
    /**
     * @var \GuzzleHttp\Client
     */
    public $client;

    public function getClient(): GuzzleClient
    {

        if (! $this->client) {
            $this->client = self::prepareGuzzleClient($this->getApplication());
        }

        return $this->client;
    }

    /**
     * @param string $receipt
     *
     * @return array
     */
    public function purchase(string $receipt): array
    {
        $lastChar = (int) substr($receipt, -1);

        $response = ['status' => false];

        if ($lastChar % 2 !== 0) {
            $response = [
                'status'      => true,
                'expire_date' => Carbon::parse('+7 days', 'America/Guatemala')->toDateTimeString(),
            ];
        }

        return $response;
    }

    /**
     * @param \App\Models\Subscription $subscription
     * @param string                   $status
     *
     * @return bool
     */
    public function notify(Subscription $subscription, string $status): bool
    {
        $client = $this->getClient();

        $endpoint = $this->getApplication()->getAttribute('notify_endpoint');

        $data = [
            'app_id'    => $subscription->app_id,
            'device_id' => $subscription->device->id,
            'status'    => $status,
        ];

        //$client->post($endpoint, $data);

        return true;
    }
}