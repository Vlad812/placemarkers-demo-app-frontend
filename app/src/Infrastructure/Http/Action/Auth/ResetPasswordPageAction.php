<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Handler\Auth\ResetPasswordPageHandler;
use App\Application\Query\Auth\ResetPasswordPageQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\ResetPasswordResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/reset-password/{token}',
    name: 'auth_reset_password_page',
    methods: ['GET'],
)]
final class ResetPasswordPageAction extends AbstractAction
{
    public function __construct(
        ResetPasswordResponder $responder,
        private readonly ResetPasswordPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $query = ResetPasswordPageQuery::fromRawValues([
            'token' => (string) $request->attributes->get('token'),
        ]);
        $result = ($this->handler)($query);

        return $this->responder->respond($result);
    }
}
