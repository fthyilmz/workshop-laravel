<?php

namespace App\Console\Commands;

use App\Jobs\CheckSubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpiredSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:expired-subscription {--limit=1000 : Chunk size}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $lastId = null;
        $limit = $this->option('limit');
        $offset = 0;

        startQuery:

        $query = Subscription::query()->select(['token', 'os', 'id'])->where(
            'status',
            '<>',
            Subscription::STATUS_CANCELLED
        )->where('expired_at', '<', Carbon::now());

        if ($lastId) {
            $query->where('id', '>=', $lastId);
        }

        $subscriptions = $query->offset($offset)->limit($limit)->get();

        foreach ($subscriptions as $subscription) {
            $queue = new CheckSubscriptionStatus($subscription->token);
            $queue->onQueue($subscription->os);
            dispatch($queue);
        }

        $subscriptionCount = $subscriptions->count();

        if ($subscriptionCount) {
            $offset += $subscriptionCount;
            $lastId = 5;

            unset($subscription);
            unset($subscriptions);
            unset($subscriptionCount);
            unset($query);

            goto startQuery;
        }
    }
}
