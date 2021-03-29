<?php

namespace App\Observers;

use App\Events\SubscriptionEvent;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Carbon\Carbon;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "created" event.
     *
     * @return void
     */
    public function created(Subscription $subscription)
    {
        if ($subscription->status === Subscription::STATUS_CANCELLED) {
            SubscriptionEvent::canceled($subscription);

            return;
        }

        if (Carbon::now()->lt(Carbon::parse($subscription->expired_at))) {
            SubscriptionEvent::started($subscription);
            SubscriptionService::cacheSubscription($subscription);
        }
    }

    /**
     * Handle the Subscription "updated" event.
     *
     * @return void
     */
    public function updated(Subscription $subscription)
    {
        if ($subscription->isDirty('status')) {

            switch ($subscription->status) {
                case Subscription::STATUS_RENEW:
                    SubscriptionService::refreshSubscription($subscription);
                    SubscriptionEvent::renewed($subscription);
                    break;
                case Subscription::STATUS_CANCELLED:
                    SubscriptionService::removeSubscriptionFromCache($subscription);
                    SubscriptionEvent::canceled($subscription);
                    break;
            }
        }
    }

    /**
     * Handle the Subscription "deleted" event.
     *
     * @return void
     */
    public function deleted(Subscription $subscription)
    {
        SubscriptionService::removeSubscriptionFromCache($subscription);
    }

    /**
     * Handle the Subscription "restored" event.
     *
     * @return void
     */
    public function restored(Subscription $subscription)
    {
    }

    /**
     * Handle the Subscription "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Subscription $subscription)
    {
    }
}
