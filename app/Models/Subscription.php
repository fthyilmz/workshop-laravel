<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    CONST STATUS_NEW = 'new';

    CONST STATUS_RENEW = 'renewed';

    CONST STATUS_CANCELLED = 'cancelled';

    CONST ALL_STATUS = [self::STATUS_NEW, self::STATUS_RENEW, self::STATUS_CANCELLED];

    protected $table = 'subscriptions';

    protected $fillable = [
        'receipt',
        'status',
        'expired_at',
        'os',
        'token',
        'device_id',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
