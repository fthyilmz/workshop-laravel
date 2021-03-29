<?php

namespace App\Events;

use App\Jobs\SubscriptionNotify;
use App\Models\Subscription;

class SubscriptionEvent
{
    /**
     * @param \App\Models\Subscription $subscription
     */
    public static function started(Subscription $subscription)
    {
        SubscriptionNotify::dispatch($subscription, Subscription::STATUS_NEW);
    }

    /**
     * @param \App\Models\Subscription $subscription
     */
    public static function renewed(Subscription $subscription)
    {
        SubscriptionNotify::dispatch($subscription, Subscription::STATUS_RENEW);
    }

    /**
     * @param \App\Models\Subscription $subscription
     */
    public static function canceled(Subscription $subscription)
    {
        SubscriptionNotify::dispatch($subscription, Subscription::STATUS_CANCELLED);
    }
}
