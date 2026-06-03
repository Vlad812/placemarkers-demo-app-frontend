<?php

namespace App\Infrastructure\Http\Action;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HelloWorldAction
{
    #[Route('/', name: 'hello_world', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response('Hello World');
    }
}
