<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\Exception\ApiException;
use App\Application\Exception\ServiceUnavailableException;
use App\Application\Exception\UnauthorizedException;
use App\Infrastructure\Service\IncidentLogger;
use JsonException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

abstract readonly class AbstractHttpApiClient
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected IncidentLogger $incidentLogger,
        protected SerializerInterface $serializer,
    ) {
    }

    abstract protected function getServiceName(): string;

    abstract protected function getUnavailableMessage(): string;

    /**
     * @param array<string, mixed> $options
     * @throws TransportExceptionInterface|RandomException
     */
    protected function executeRequest(
        string $method,
        string $path,
        array $options = [],
        string $clientErrorFallback = 'Произошла ошибка при выполнении запроса.',
    ): HttpApiResult {
        try {
            $response = $this->httpClient->request($method, $path, $options);
        } catch (TransportExceptionInterface $e) {
            $this->incidentLogger->logWarning(
                sprintf('%s service transport error for [%s %s].', $this->getServiceName(), $method, $path),
                $e,
            );

            throw new ServiceUnavailableException($this->getUnavailableMessage());
        } catch (Throwable $e) {
            if ($e instanceof ServiceUnavailableException || $e instanceof ApiException || $e instanceof UnauthorizedException) {
                throw $e;
            }

            $this->incidentLogger->logWarning(
                sprintf('%s service request failed for [%s %s].', $this->getServiceName(), $method, $path),
                $e,
            );

            throw new ServiceUnavailableException($this->getUnavailableMessage());
        }

        $statusCode = $response->getStatusCode();
        $body = $this->decodeResponse($response, $method, $path);

        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->incidentLogger->logWarningMessage(
                sprintf(
                    '%s service returned server error [%d] for [%s %s].',
                    $this->getServiceName(),
                    $statusCode,
                    $method,
                    $path,
                ),
            );

            throw new ServiceUnavailableException($this->getUnavailableMessage());
        }

        if ($statusCode >= Response::HTTP_BAD_REQUEST) {
            if ($statusCode === Response::HTTP_UNAUTHORIZED) {
                throw new UnauthorizedException($clientErrorFallback);
            }

            throw new ApiException(
                $this->resolveMessage($body, $clientErrorFallback),
                $statusCode,
            );
        }

        return new HttpApiResult($statusCode, $body);
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializePayload(object $payload): array
    {
        /** @var array<string, mixed> $normalized */
        $normalized = $this->serializer->normalize($payload, JsonEncoder::FORMAT);

        return $normalized;
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function decodeResponse(ResponseInterface $response, string $method, string $path): array
    {
        $content = $response->getContent(false);

        if ($content === '') {
            return [];
        }

        try {
            /** @var array<string, mixed>|list<mixed> $decoded */
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        } catch (JsonException $e) {
            $this->incidentLogger->logWarning(
                sprintf('%s service returned invalid JSON for [%s %s].', $this->getServiceName(), $method, $path),
                $e,
            );

            throw new ServiceUnavailableException($this->getUnavailableMessage());
        }
    }

    /**
     * @param array<string, mixed>|list<mixed> $body
     */
    private function resolveMessage(array $body, string $fallback): string
    {
        if (!array_is_list($body)
            && isset($body['message'])
            && is_string($body['message'])
            && $body['message'] !== ''
        ) {
            return $body['message'];
        }

        if (isset($body['errors']) && is_array($body['errors'])) {
            $firstError = $body['errors'][0] ?? null;

            if (is_array($firstError)
                && isset($firstError['message'])
                && is_string($firstError['message'])
                && $firstError['message'] !== ''
            ) {
                return $firstError['message'];
            }
        }

        return $fallback;
    }
}
