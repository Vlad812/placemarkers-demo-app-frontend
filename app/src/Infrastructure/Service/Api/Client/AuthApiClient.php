<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\Auth\AuthMessageResponse;
use App\Application\DTO\Api\Auth\AuthTokenResponse;
use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\Api\Payload\AuthLoginPayload;
use App\Application\DTO\Api\Payload\AuthRefreshPayload;
use App\Application\DTO\Api\Payload\AuthRequestPasswordResetPayload;
use App\Application\DTO\Api\Payload\AuthResetPasswordPayload;
use App\Application\DTO\Api\Payload\AuthSignupPayload;
use App\Application\Exception\ServiceUnavailableException;
use App\Application\Port\Api\AuthApiInterface;
use App\Infrastructure\Service\IncidentLoggerInterface;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class AuthApiClient extends AbstractHttpApiClient implements AuthApiInterface
{
    public function __construct(
        HttpClientInterface $authClient,
        IncidentLoggerInterface $incidentLogger,
        SerializerInterface $serializer,
    ) {
        parent::__construct($authClient, $incidentLogger, $serializer);
    }

    /**
     * @param AuthLoginPayload $payload
     * @return AuthTokenResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function login(AuthLoginPayload $payload): AuthTokenResponse
    {
        $result = $this->request('POST', '/login', ['json' => $payload->toArray()], 'Неверный email или пароль.');

        return AuthTokenResponse::fromArray($result->body);
    }

    /**
     * @param AuthSignupPayload $payload
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function signup(AuthSignupPayload $payload): AuthMessageResponse
    {
        $result = $this->request('POST', '/signup', ['json' => $payload->toArray()], 'Ошибка при регистрации.');

        return AuthMessageResponse::fromArray($result->body, 'Ошибка при регистрации.');
    }

    /**
     * @param AuthRefreshPayload $payload
     * @return AuthTokenResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function refresh(AuthRefreshPayload $payload): AuthTokenResponse
    {
        $result = $this->request('POST', '/refresh', ['json' => $payload->toArray()]);

        return AuthTokenResponse::fromArray($result->body);
    }

    /**
     * @param string $accessToken
     * @param string|null $refreshToken
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function logout(string $accessToken, ?string $refreshToken = null): HttpApiResult
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ];

        if ($refreshToken !== null) {
            $options['json'] = ['refresh_token' => $refreshToken];
        }

        return $this->request('POST', '/logout', $options);
    }

    /**
     * @param string $token
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function confirmEmail(string $token): AuthMessageResponse
    {
        $result = $this->request(
            'GET',
            '/confirm-email/' . rawurlencode($token),
            [],
            'Ссылка подтверждения недействительна или устарела.',
        );

        return AuthMessageResponse::fromArray($result->body, 'Ссылка подтверждения недействительна или устарела.');
    }

    /**
     * @param AuthRequestPasswordResetPayload $payload
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function requestPasswordReset(AuthRequestPasswordResetPayload $payload): AuthMessageResponse
    {
        $result = $this->request('POST', '/forgot-password', ['json' => $payload->toArray()], 'Не удалось отправить запрос на сброс пароля.');

        return AuthMessageResponse::fromArray($result->body, 'Не удалось отправить запрос на сброс пароля.');
    }

    /**
     * @param AuthResetPasswordPayload $payload
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function resetPassword(AuthResetPasswordPayload $payload): AuthMessageResponse
    {
        $result = $this->request('POST', '/reset-password', ['json' => $payload->toArray()], 'Ссылка для сброса пароля недействительна или устарела.');

        return AuthMessageResponse::fromArray($result->body, 'Ссылка для сброса пароля недействительна или устарела.');
    }

    /**
     * @return string
     */
    protected function getServiceName(): string
    {
        return 'Auth';
    }

    /**
     * @return string
     */
    protected function getUnavailableMessage(): string
    {
        return ServiceUnavailableException::DEFAULT_MESSAGE;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $options
     * @param string $clientErrorFallback
     * @return HttpApiResult
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    private function request(
        string $method,
        string $path,
        array $options = [],
        string $clientErrorFallback = 'Произошла ошибка при выполнении запроса.',
    ): HttpApiResult {
        return $this->executeRequest($method, $path, $options, $clientErrorFallback);
    }
}
