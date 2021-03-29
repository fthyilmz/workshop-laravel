<?php

namespace App\Services;

use App\Models\Application;
use App\Services\Clients\Apple;
use App\Services\Clients\Client;
use App\Services\Clients\Google;

class ClientService
{
    /**
     * @var Google
     */
    private $googleClient;

    /**
     * @var Apple
     */
    private $appleClient;

    /**
     * @var \App\Services\ApplicationService
     */
    private $applicationService;

    /**
     * ClientService constructor.
     *
     * @param Google $googleClient
     * @param Apple  $appleClient
     */
    public function __construct(Google $googleClient, Apple $appleClient, ApplicationService $applicationService)
    {
        $this->googleClient = $googleClient;
        $this->appleClient = $appleClient;
        $this->applicationService = $applicationService;
    }

    /**
     * @param string $operatingSystem
     *
     * @return Client
     */
    public function getClientByOS(string $operatingSystem, string $appId): Client
    {
        switch ($operatingSystem) {
            case Client::GOOGLE:
                $client = $this->googleClient;
                break;
            case Client::APPLE:
                $client = $this->appleClient;
                break;
            default:
                throw new \LogicException();
                break;
        }

        $application = $this->applicationService->getApplication($appId);

        $client->setApplication($application);

        return $client;
    }
}
