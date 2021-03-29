<?php

namespace App\Services;

use App\Models\Subscription;
use App\Observers\SubscriptionObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SubscriptionService
{
    const CACHE_PATTERN = 'subscription_%s';

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * SubscriptionService constructor.
     *
     * @param ClientService $clientService
     */
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param array $data
     *
     * @return Subscription
     * @throws \Exception
     */
    public function create(string $receipt, string $clientToken, array $device): Subscription
    {
        $response = $this->purchase($receipt, $device);

        $data = [
            'receipt'    => $receipt,
            'status'     => $response['status'] ? Subscription::STATUS_NEW : Subscription::STATUS_CANCELLED,
            'expired_at' => $response['expire_date'],
            'os'         => $device['os'],
            'token'      => $clientToken,
            'device_id'  => $device['id'],
        ];

        return $this->save($data);
    }

    /**
     * @param array $data
     *
     * @return Subscription
     * @throws \Exception
     */
    public function save(array $data): Subscription
    {
        try {
            DB::beginTransaction();
            $subscription = Subscription::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $subscription;
    }

    /**
     * @param string $receipt
     *
     * @param string $clientToken
     * @param array  $device
     *
     * @return array
     * @throws \Exception
     */
    public function purchase(string $receipt, array $device)
    {
        $response = $this->purchaseByClient($receipt, $device['os'], $device['app_id']);

        if ($response['status'] === true) {
            $expiredAt = Carbon::parse($response['expire_date'], new \DateTimeZone('America/Guatemala'));

            $response['expire_date'] = $expiredAt->tz('Europe/Istanbul')->toDateTimeString();
        }

        return $response;
    }

    /**
     * @param string $receipt
     * @param string $os
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    public function purchaseByClient(string $receipt, string $os, string $appId): array
    {
        $client = $this->clientService->getClientByOS($os, $appId);

        try {
            $response = $client->purchase($receipt);
        } catch (\Exception $e) {
            throw $e;
        }

        return $response;
    }

    /**
     * @param string $clientToken
     *
     * @return $this
     */
    public function checkClientTokenExists(string $clientToken)
    {
        $token = Cache::has(sprintf(self::CACHE_PATTERN, $clientToken));

        if ($token) {
            throw new UnprocessableEntityHttpException('Client is already subscribed');
        }

        return $this;
    }

    /**
     * @param string $clientToken
     */
    public function getSubscriptionByClientToken(string $clientToken)
    {
        $subscription = Cache::get(sprintf(self::CACHE_PATTERN, $clientToken));

        // Duruma göre aktif abonelik bilgilis direkt olarak redis üzerinden verilebilir. Alternetatif olarak yazıldı.
        if (! $subscription) {
            $subscriptionData = Subscription::where('token', $clientToken)->firstOrFail();
            $subscription = static::cacheSubscription($subscriptionData);
        }

        return $subscription;
    }

    /**
     * @param \App\Models\Subscription $subscription
     */
    public static function refreshSubscription(Subscription $subscription): void
    {
        static::removeSubscriptionFromCache($subscription);

        static::cacheSubscription($subscription);
    }

    /**
     * @param Subscription $subscription
     */
    public static function cacheSubscription(Subscription $subscription): array
    {
        $data = $subscription->only(['expired_at', 'status', 'id', 'receipt']);

        Cache::add(
            sprintf(self::CACHE_PATTERN, $subscription->token),
            $data,
            Carbon::parse($subscription->expired_at)
        );

        return $data;
    }

    /**
     * @param \App\Models\Subscription $subscription
     */
    public static function removeSubscriptionFromCache(Subscription $subscription): void
    {
        Cache::forget(sprintf(self::CACHE_PATTERN, $subscription->token));
    }

    /**
     * @param \App\Models\Subscription $subscription
     */
    public function notify(Subscription $subscription, string $status): bool
    {
        $client = $this->clientService->getClientByOS($subscription->os, $subscription->app_id);

        return $client->notify($subscription, $status);
    }
}