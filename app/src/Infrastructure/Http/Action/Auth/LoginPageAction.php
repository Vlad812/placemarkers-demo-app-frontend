<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Handler\Auth\LoginPageHandler;
use App\Application\Query\Auth\LoginPageQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\LoginResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/login',
    name: 'auth_login_page',
    methods: ['GET'],
)]
final class LoginPageAction extends AbstractAction
{
    public function __construct(
        LoginResponder $responder,
        private readonly LoginPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new LoginPageQuery());

        return $this->responder->respond($result);
    }
}
