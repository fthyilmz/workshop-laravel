<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\DeviceService;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSubscriptionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $clientToken;

    /**
     * Create a new job instance.
     *
     * @param string $clientToken
     */
    public function __construct(string $clientToken)
    {
        $this->clientToken = $clientToken;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle(
        DeviceService $deviceService,
        SubscriptionService $subscriptionService
    ) {
        $token = $this->clientToken;

        $subscription = $subscriptionService->getSubscriptionByClientToken($token);

        $receipt = $subscription['receipt'];

        $lastChar = (int) substr($receipt, -2);

        if ($lastChar % 6 === 0) {
            $this->release(60);

            return;
        }

        $device = $deviceService->getDeviceByClientToken($token);

        $response = $subscriptionService->purchase($receipt, $device);

        $data = ['status' => Subscription::STATUS_CANCELLED];

        if ($response['status']) {
            $data = [
                'status'     => Subscription::STATUS_RENEW,
                'expired_at' => $data['expire_date'],
            ];
        }

        Subscription::where('id', $subscription['id'])->update($data);
    }
}
