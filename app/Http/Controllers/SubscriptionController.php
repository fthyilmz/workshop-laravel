<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Requests\SubscriptionCheckRequest;
use App\Services\DeviceService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionController extends Controller
{
    /**
     * @var SubscriptionService
     */
    private $subscriptionService;

    /**
     * SubscriptionController constructor.
     *
     * @param SubscriptionService $subscriptionService
     * @param DeviceService       $deviceService
     */
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param \App\Http\Requests\StoreSubscriptionRequest $request
     *
     * @throws \Exception
     */
    public function store(StoreSubscriptionRequest $request, DeviceService $deviceService)
    {
        $clientToken = $request->post('client_token');
        $receipt = $request->post('receipt');

        $device = $deviceService->getDeviceByClientToken($clientToken);

        if (! $device) {
            throw new NotFoundHttpException('Client token does not found our storage');
        }

        $subscription = $this->subscriptionService->checkClientTokenExists($clientToken)->create(
            $receipt,
            $clientToken,
            $device
        );

        return new JsonResponse(
            $subscription->only('status', 'expired_at', 'token'), Response::HTTP_CREATED
        );
    }

    /**
     * @param \App\Http\Requests\SubscriptionCheckRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(SubscriptionCheckRequest $request): JsonResponse
    {
        $clientToken = $request->get('client_token');

        $subscription = $this->subscriptionService->getSubscriptionByClientToken($clientToken);

        return new JsonResponse($subscription);
    }
}
