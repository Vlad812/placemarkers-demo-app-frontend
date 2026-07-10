<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\LoginCommand;
use App\Application\DTO\RedirectPageResponse;
use App\Application\Handler\Auth\LoginHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\LoginResponder;
use App\Infrastructure\Security\AuthSessionStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/login',
    name: 'auth_login',
    methods: ['POST'],
)]
final class LoginAction extends AbstractAction
{
    public function __construct(
        LoginResponder $responder,
        private readonly LoginHandler $handler,
        private readonly AuthSessionStorageInterface $authSessionStorage,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = LoginCommand::fromRawValues($request->request->all());
        $result = ($this->handler)($command);

        $this->authSessionStorage->store(
            $result->accessToken,
            $result->refreshToken,
        );

        return $this->responder->respond(new RedirectPageResponse(
            $result->route,
            $result->routeParams,
        ));
    }
}
