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
     * @param array $data
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
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
     * @param string $id
     * @param array $data
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
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

    /**
     * @param string $id
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
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
     * @param array $data
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
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

    /**
     * @return string
     */
    protected function getServiceName(): string
    {
        return 'Placemarker';
    }

    /**
     * @return string
     */
    protected function getUnavailableMessage(): string
    {
        return 'Сервис меток временно недоступен. Попробуйте позже.';
    }
}
