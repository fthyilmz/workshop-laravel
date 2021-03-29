<?php

namespace App\Models;

use App\Services\ApplicationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Application extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::created(
            function ($application) {
                Cache::forever(sprintf(ApplicationService::APP_PATTERN, $application->id), $application->name);
            }
        );

        static::deleted(
            function ($application) {
                Cache::forget(sprintf(ApplicationService::APP_PATTERN, $application->id));
            }
        );
    }
}
