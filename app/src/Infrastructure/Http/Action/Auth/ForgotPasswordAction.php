<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\ForgotPasswordCommand;
use App\Application\Handler\Auth\ForgotPasswordHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\ForgotPasswordResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/forgot-password',
    name: 'auth_forgot_password',
    methods: ['POST'],
)]
final class ForgotPasswordAction extends AbstractAction
{
    public function __construct(
        ForgotPasswordResponder $responder,
        private readonly ForgotPasswordHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = ForgotPasswordCommand::fromRawValues($request->request->all());
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
