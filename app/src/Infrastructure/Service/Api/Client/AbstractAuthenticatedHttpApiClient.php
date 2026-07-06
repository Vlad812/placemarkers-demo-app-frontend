<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Infrastructure\Service\Api\AccessTokenProvider;
use App\Infrastructure\Service\IncidentLogger;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract readonly class AbstractAuthenticatedHttpApiClient extends AbstractHttpApiClient
{
    public function __construct(
        HttpClientInterface $httpClient,
        IncidentLogger $incidentLogger,
        SerializerInterface $serializer,
        protected AccessTokenProvider $accessTokenProvider,
    ) {
        parent::__construct($httpClient, $incidentLogger, $serializer);
    }

    /**
     * @param array $options
     * @return array
     */
    protected function withAuthHeaders(array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        $options['headers'] = array_merge($headers, $this->accessTokenProvider->getAuthorizationHeaders());

        return $options;
    }
}
