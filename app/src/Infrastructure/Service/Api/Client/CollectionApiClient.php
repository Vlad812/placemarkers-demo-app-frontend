<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\CollectionCreatePayload;
use App\Application\Port\Api\CollectionApiInterface;
use App\Infrastructure\Service\Api\AccessTokenProvider;
use App\Infrastructure\Service\IncidentLogger;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    /**
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
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
     * @param CollectionCreatePayload $payload
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function create(CollectionCreatePayload $payload): HttpApiResult
    {
        return $this->executeRequest(
            'POST',
            '/collections',
            $this->withAuthHeaders(['json' => $payload->toArray()]),
            'Не удалось сохранить коллекцию.',
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
            '/collections/' . rawurlencode($id),
            $this->withAuthHeaders(),
            'Не удалось удалить коллекцию.',
        );
    }

    /**
     * @return string
     */
    protected function getServiceName(): string
    {
        return 'Collection';
    }

    /**
     * @return string
     */
    protected function getUnavailableMessage(): string
    {
        return 'Сервис коллекций временно недоступен. Попробуйте позже.';
    }
}
