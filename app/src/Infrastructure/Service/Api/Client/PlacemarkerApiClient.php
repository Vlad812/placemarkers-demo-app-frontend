<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\Port\Api\PlacemarkerApiInterface;
use App\Infrastructure\Service\Api\AccessTokenProvider;
use App\Infrastructure\Service\IncidentLogger;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlacemarkerApiClient extends AbstractAuthenticatedHttpApiClient implements PlacemarkerApiInterface
{
    public function __construct(
        HttpClientInterface $databaseClient,
        IncidentLogger $incidentLogger,
        SerializerInterface $serializer,
        AccessTokenProvider $accessTokenProvider,
    ) {
        parent::__construct($databaseClient, $incidentLogger, $serializer, $accessTokenProvider);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws RandomException
     */
    public function getAll(): HttpApiResult
    {
        return $this->executeRequest(
            'GET',
            '/api/placemarkers',
            $this->withAuthHeaders(),
            'Не удалось загрузить метки.',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/api/placemarkers',
            $this->withAuthHeaders(['json' => $data]),
            'Не удалось создать метку.',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): HttpApiResult
    {
        return $this->executeRequest(
            'PUT',
            '/api/placemarkers/' . rawurlencode($id),
            $this->withAuthHeaders(['json' => $data]),
            'Не удалось обновить метку.',
        );
    }

    public function delete(string $id): HttpApiResult
    {
        return $this->executeRequest(
            'DELETE',
            '/api/placemarkers/' . rawurlencode($id),
            $this->withAuthHeaders(),
            'Не удалось удалить метку.',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTag(array $data): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/api/tags',
            $this->withAuthHeaders(['json' => $data]),
            'Не удалось создать тег.',
        );
    }

    protected function getServiceName(): string
    {
        return 'Placemarker';
    }

    protected function getUnavailableMessage(): string
    {
        return 'Сервис меток временно недоступен. Попробуйте позже.';
    }
}
