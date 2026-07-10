<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Handler\Auth\ForgotPasswordPageHandler;
use App\Application\Query\Auth\ForgotPasswordPageQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\ForgotPasswordResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/forgot-password',
    name: 'auth_forgot_password_page',
    methods: ['GET'],
)]
final class ForgotPasswordPageAction extends AbstractAction
{
    public function __construct(
        ForgotPasswordResponder $responder,
        private readonly ForgotPasswordPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new ForgotPasswordPageQuery());

        return $this->responder->respond($result);
    }
}
