<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action\Auth;

use App\Application\Handler\Auth\SignupPageHandler;
use App\Application\Query\Auth\SignupPageQuery;
use App\Infrastructure\Http\Action\AbstractAction;
use App\Infrastructure\Http\Responder\Auth\SignupResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/signup',
    name: 'auth_signup_page',
    methods: ['GET'],
)]
final class SignupPageAction extends AbstractAction
{
    public function __construct(
        SignupResponder $responder,
        private readonly SignupPageHandler $handler,
    ) {
        parent::__construct($responder);
    }

    protected function handleRequest(Request $request): Response
    {
        $result = ($this->handler)(new SignupPageQuery());

        return $this->responder->respond($result);
    }
}
