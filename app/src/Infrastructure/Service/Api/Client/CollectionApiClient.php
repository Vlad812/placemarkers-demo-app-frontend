<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\Port\Api\CollectionApiInterface;
use App\Infrastructure\Service\Api\AccessTokenProvider;
use App\Infrastructure\Service\IncidentLogger;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class CollectionApiClient extends AbstractAuthenticatedHttpApiClient implements CollectionApiInterface
{
    public function __construct(
        HttpClientInterface $collectionClient,
        IncidentLogger $incidentLogger,
        SerializerInterface $serializer,
        AccessTokenProvider $accessTokenProvider,
    ) {
        parent::__construct($collectionClient, $incidentLogger, $serializer, $accessTokenProvider);
    }

    public function getAll(): HttpApiResult
    {
        return $this->executeRequest(
            'GET',
            '/collections',
            $this->withAuthHeaders(),
            'Не удалось загрузить коллекции.',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/collections',
            $this->withAuthHeaders(['json' => $data]),
            'Не удалось сохранить коллекцию.',
        );
    }

    public function delete(string $id): HttpApiResult
    {
        return $this->executeRequest(
            'DELETE',
            '/collections/' . rawurlencode($id),
            $this->withAuthHeaders(),
            'Не удалось удалить коллекцию.',
        );
    }

    protected function getServiceName(): string
    {
        return 'Collection';
    }

    protected function getUnavailableMessage(): string
    {
        return 'Сервис коллекций временно недоступен. Попробуйте позже.';
    }
}
