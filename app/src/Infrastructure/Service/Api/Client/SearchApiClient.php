<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\Port\Api\SearchApiInterface;
use App\Infrastructure\Service\Api\AccessTokenProvider;
use App\Infrastructure\Service\IncidentLogger;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SearchApiClient extends AbstractAuthenticatedHttpApiClient implements SearchApiInterface
{
    public function __construct(
        HttpClientInterface $searchClient,
        IncidentLogger $incidentLogger,
        SerializerInterface $serializer,
        AccessTokenProvider $accessTokenProvider,
    ) {
        parent::__construct($searchClient, $incidentLogger, $serializer, $accessTokenProvider);
    }

    /**
     * @param list<string> $tags
     * @param list<string> $types
     */
    public function search(
        float $lat,
        float $lon,
        int $radiusMeters,
        array $tags = [],
        array $types = [],
    ): HttpApiResult {
        $query = [
            'lat' => $lat,
            'lon' => $lon,
            'radius' => $radiusMeters,
        ];

        if ($tags !== []) {
            $query['tags'] = $tags;
        }

        if ($types !== []) {
            $query['types'] = $types;
        }

        return $this->executeRequest(
            'GET',
            '/search',
            $this->withAuthHeaders(['query' => $query]),
            'Не удалось выполнить поиск.',
        );
    }

    public function getUserTags(): HttpApiResult
    {
        return $this->executeRequest(
            'GET',
            '/search/tags',
            $this->withAuthHeaders(),
            'Не удалось загрузить теги.',
        );
    }

    protected function getServiceName(): string
    {
        return 'Search';
    }

    protected function getUnavailableMessage(): string
    {
        return 'Сервис поиска временно недоступен. Попробуйте позже.';
    }
}
