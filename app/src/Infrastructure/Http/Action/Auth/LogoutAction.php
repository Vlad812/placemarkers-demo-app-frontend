<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\LogoutCommand;
use App\Application\Handler\Auth\LogoutHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\RedirectResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/logout',
    name: 'auth_logout',
    methods: ['GET'],
)]
final class LogoutAction extends AbstractAction
{
    public function __construct(
        RedirectResponder $responder,
        private readonly LogoutHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new LogoutCommand());

        return $this->responder->respond($result);
    }
}
