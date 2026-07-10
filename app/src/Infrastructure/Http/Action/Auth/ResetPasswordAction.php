<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\ResetPasswordCommand;
use App\Application\Handler\Auth\ResetPasswordHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\ResetPasswordResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/reset-password/{token}',
    name: 'auth_reset_password',
    methods: ['POST'],
)]
final class ResetPasswordAction extends AbstractAction
{
    public function __construct(
        ResetPasswordResponder $responder,
        private readonly ResetPasswordHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = ResetPasswordCommand::fromRawValues([
            ...$request->request->all(),
            'token' => (string) $request->attributes->get('token'),
        ]);
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
