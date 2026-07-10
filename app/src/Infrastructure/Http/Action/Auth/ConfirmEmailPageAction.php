<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Command\Auth\ConfirmEmailCommand;
use App\Application\Handler\Auth\ConfirmEmailHandler;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\ConfirmEmailResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/confirm-email/{token}',
    name: 'auth_confirm_email',
    methods: ['GET'],
)]
final class ConfirmEmailPageAction extends AbstractAction
{
    public function __construct(
        ConfirmEmailResponder $responder,
        private readonly ConfirmEmailHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $command = ConfirmEmailCommand::fromRawValues([
            'token' => (string) $request->attributes->get('token'),
        ]);
        $result = ($this->handler)($command);

        return $this->responder->respond($result);
    }
}
