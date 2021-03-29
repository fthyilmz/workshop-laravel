<?php

namespace App\Services\Clients;

use App\Models\Application;
use App\Models\Subscription;
use GuzzleHttp\Client as GuzzleClient;

abstract class Client
{
    CONST APPLE = 'ios';

    CONST GOOGLE = 'android';

    CONST ALL = [self::APPLE, self::GOOGLE];

    /**
     * @var Application
     */
    private $application;

    abstract public function purchase(string $receipt): array;

    abstract public function notify(Subscription $subscription, string $status): bool;

    abstract public function getClient(): GuzzleClient;

    /**
     * @return Application
     */
    protected function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    /**
     * @param Application $application
     *
     * @return \GuzzleHttp\Client
     */
    protected function prepareGuzzleClient(Application $application): GuzzleClient
    {
        // Buradaki yapı password'ü db'de tutmamak ve erişilmemesi için böyle yapıldı. Password'ü bir private key ile kriptolayıp da eklenebilir. Daha sonra public key ile açıp redis vs. çözümlerden veri çekilebilir.
        $name = $application->name;
        $baseUri = config(sprintf('services.applications.%s.base_uri', $name));
        $username = config(sprintf('services.applications.%s.base_uri', $name));
        $password = config(sprintf('services.applications.%s.base_uri', $name));

        return new GuzzleClient(
            [
                'base_uri' => $baseUri,
                'headers'  => [
                    'Content-Type' => 'application/json',
                ],
                'auth'     => [$username, $password],
            ]
        );
    }
}