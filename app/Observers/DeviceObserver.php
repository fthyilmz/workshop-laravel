<?php

namespace App\Observers;

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Cache;

class DeviceObserver
{
    /**
     * Handle the device "created" event.
     *
     * @param  \App\Models\Device $device
     *
     * @return void
     */
    public function created(Device $device)
    {
        Cache::forever(sprintf(DeviceService::EXISTS_PATTERN, $device->u_id, $device->app_id), $device->token);
        DeviceService::cacheDevice($device);
    }

    /**
     * @param \App\Models\Device $device
     */
    public function creating(Device $device)
    {
        $device->token = password_hash(
            sprintf('%s-%s', $device->u_id, $device->app_id),
            PASSWORD_BCRYPT
        );
    }

    /**
     * Handle the device "updated" event.
     *
     * @param  \App\Models\Device $device
     *
     * @return void
     */
    public function updated(Device $device)
    {
        //
    }

    /**
     * Handle the device "deleted" event.
     *
     * @param  \App\Models\Device $device
     *
     * @return void
     */
    public function deleted(Device $device)
    {
        Cache::forget(sprintf(DeviceService::EXISTS_PATTERN, $device->u_id, $device->app_id));
        Cache::forget(sprintf(DeviceService::TOKEN_PATTERN, $device->token));
    }

    /**
     * Handle the device "restored" event.
     *
     * @param  \App\Models\Device $device
     *
     * @return void
     */
    public function restored(Device $device)
    {
        //
    }

    /**
     * Handle the device "force deleted" event.
     *
     * @param  \App\Models\Device $device
     *
     * @return void
     */
    public function forceDeleted(Device $device)
    {
        //
    }
}
