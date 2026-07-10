<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\SignupCommand;
use App\Application\Handler\Auth\SignupHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/signup',
    name: 'auth_signup',
    methods: ['POST'],
)]
final class SignupAction extends AbstractAction
{
    public function __construct(
        JsonResponder $responder,
        private readonly SignupHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = SignupCommand::fromRawValues($request->request->all());
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
