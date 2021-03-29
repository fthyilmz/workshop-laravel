<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriptionNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Subscription
     */
    private $subscription;

    /**
     * @var string
     */
    private $status;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Subscription $subscription
     */
    public function __construct(Subscription $subscription, string $status)
    {
        $this->subscription = $subscription;

        if (! in_array($status, Subscription::ALL_STATUS, true)) {
            throw new \LogicException('unsupported status');
        }

        $this->status = $status;
        $this->onQueue('notify');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SubscriptionService $subscriptionService)
    {
        try {
            $subscriptionService->notify($this->subscription, $this->status);
        } catch (BadResponseException $e) {

            if ($this->attempts() > 2) {
                $this->delete();
            }

            $this->release(60);
        }
    }
}
