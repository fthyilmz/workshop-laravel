<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    public const EXISTS_PATTERN = 'device_%s_%s';

    public const TOKEN_PATTERN = 'token_%s';

    /**
     * @param array $fillableData
     *
     * @return mixed
     * @throws \Exception
     */
    protected function save(array $fillableData)
    {
        try {
            DB::beginTransaction();
            $device = Device::create($fillableData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $device;
    }

    /**
     * @param array $requestData
     *
     * @return Device|bool|mixed
     * @throws \Exception
     */
    public function create(array $requestData)
    {
        $device = $this->getDeviceTokenFromCache($requestData['u_id'], $requestData['app_id']);

        if (! $device) {
            $device = $this->save($requestData);
        }

        return $device;
    }

    /**
     * @param $uId
     *
     * @return Device|bool
     */
    public function getDeviceTokenFromCache($uId, $app_id)
    {
        $token = Cache::get(sprintf(self::EXISTS_PATTERN, $uId, $app_id));

        if (! $token) {
            return false;
        }

        $device = new Device();
        $device->token = $token;

        return $device;
    }

    /**
     * @param string $clientToken
     *
     * @return mixed
     */
    public function getDeviceByClientToken(string $clientToken)
    {
        $device = Cache::get(sprintf(self::TOKEN_PATTERN, $clientToken));

        if (! $device) {
            $device = Device::where('token', $clientToken)->firstOrFail();
            static::cacheDevice($device);
        }

        return $device;
    }

    /**
     * @param Device $device
     *
     * @return array
     */
    public static function cacheDevice(Device $device): array
    {
        $data = $device->only(['os', 'app_id', 'id']);
        Cache::forever(
            sprintf(self::TOKEN_PATTERN, $device->token),
            $data
        );

        return $data;
    }
}