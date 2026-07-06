<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\Api\Client;

use App\Application\DTO\Api\Auth\AuthMessageResponse;
use App\Application\DTO\Api\Auth\AuthTokenResponse;
use App\Application\DTO\Api\HttpApiResult;
use App\Application\Exception\ServiceUnavailableException;
use App\Application\Port\Api\AuthApiInterface;
use App\Infrastructure\Service\IncidentLogger;
use Random\RandomException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class AuthApiClient extends AbstractHttpApiClient implements AuthApiInterface
{
    public function __construct(
        HttpClientInterface $authClient,
        IncidentLogger $incidentLogger,
        SerializerInterface $serializer,
    ) {
        parent::__construct($authClient, $incidentLogger, $serializer);
    }

    /**
     * @param array $data
     * @return AuthTokenResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function login(array $data): AuthTokenResponse
    {
        $result = $this->request('POST', '/login', ['json' => $data], 'Неверный email или пароль.');

        return AuthTokenResponse::fromArray($result->body);
    }

    /**
     * @param array $data
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function signup(array $data): AuthMessageResponse
    {
        $result = $this->request('POST', '/signup', ['json' => $data], 'Ошибка при регистрации.');

        return AuthMessageResponse::fromArray($result->body, 'Ошибка при регистрации.');
    }

    /**
     * @param array $data
     * @return AuthTokenResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function refresh(array $data): AuthTokenResponse
    {
        $result = $this->request('POST', '/refresh', ['json' => $data]);

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
     * @param array $data
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function requestPasswordReset(array $data): AuthMessageResponse
    {
        $result = $this->request('POST', '/forgot-password', ['json' => $data], 'Не удалось отправить запрос на сброс пароля.');

        return AuthMessageResponse::fromArray($result->body, 'Не удалось отправить запрос на сброс пароля.');
    }

    /**
     * @param array $data
     * @return AuthMessageResponse
     * @throws RandomException
     * @throws TransportExceptionInterface
     */
    public function resetPassword(array $data): AuthMessageResponse
    {
        $result = $this->request('POST', '/reset-password', ['json' => $data], 'Ссылка для сброса пароля недействительна или устарела.');

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
