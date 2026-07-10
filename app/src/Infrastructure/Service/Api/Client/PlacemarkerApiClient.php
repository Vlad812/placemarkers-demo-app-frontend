<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\PlacemarkerCreatePayload;
use App\Application\DTO\Api\Payload\PlacemarkerUpdatePayload;
use App\Application\DTO\Api\Payload\TagCreatePayload;
use App\Application\Port\Api\PlacemarkerApiInterface;
use App\Infrastructure\Service\Api\AccessTokenProviderInterface;
use App\Infrastructure\Service\IncidentLoggerInterface;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PlacemarkerApiClient extends AbstractAuthenticatedHttpApiClient implements PlacemarkerApiInterface
{
    public function __construct(
        HttpClientInterface $databaseClient,
        IncidentLoggerInterface $incidentLogger,
        SerializerInterface $serializer,
        AccessTokenProviderInterface $accessTokenProvider,
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
     * @param PlacemarkerCreatePayload $payload
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function create(PlacemarkerCreatePayload $payload): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/api/placemarkers',
            $this->withAuthHeaders(['json' => $payload->toArray()]),
            'Не удалось создать метку.',
        );
    }

    /**
     * @param string $id
     * @param PlacemarkerUpdatePayload $payload
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function update(string $id, PlacemarkerUpdatePayload $payload): HttpApiResult
    {
        return $this->executeRequest(
            'PUT',
            '/api/placemarkers/' . rawurlencode($id),
            $this->withAuthHeaders(['json' => $payload->toArray()]),
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
     * @param TagCreatePayload $payload
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function createTag(TagCreatePayload $payload): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/api/tags',
            $this->withAuthHeaders(['json' => $payload->toArray()]),
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
