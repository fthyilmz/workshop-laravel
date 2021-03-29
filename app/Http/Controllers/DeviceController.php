<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreDeviceRequest;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();

        return new JsonResponse($devices->toArray());
    }

    /**
     * @param StoreDeviceRequest $request
     * @param DeviceService      $deviceService
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreDeviceRequest $request, DeviceService $deviceService): JsonResponse
    {
        $requestData = $request->only(['u_id', 'app_id', 'os', 'language']);

        $device = $deviceService->create($requestData);

        return new JsonResponse(
            ['token' => $device->token], Response::HTTP_CREATED
        );
    }
}

