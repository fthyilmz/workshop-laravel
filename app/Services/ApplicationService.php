<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Cache;

class ApplicationService
{
    const APP_PATTERN = 'application_%s';

    /**
     * @param $applicationId
     *
     * @return \App\Models\Application|bool
     */
    public function getApplication($applicationId)
    {
        $application = $this->getApplicationFromCache($applicationId);

        if (! $application) {
            $application = Application::findOrFail($applicationId);
            Cache::forever(sprintf(ApplicationService::APP_PATTERN, $application->id), $application->name);
        }

        return $application;
    }

    /**
     * @param $applicationId
     *
     * @return \App\Models\Application|bool
     */
    protected function getApplicationFromCache($applicationId)
    {
        $applicationName = Cache::get(sprintf(self::APP_PATTERN, $applicationId));

        if (! $applicationName) {
            return false;
        }

        $application = new Application();
        $application->name = $applicationName;

        return $application;
    }
}