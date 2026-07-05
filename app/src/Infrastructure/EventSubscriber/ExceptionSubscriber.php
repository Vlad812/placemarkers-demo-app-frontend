<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use App\Application\DTO\ErrorResponse;
use App\Application\Exception\ClientExceptionInterface;
use App\Infrastructure\Http\Responder\JsonResponder;
use App\Infrastructure\Service\IncidentLogger;
use Random\RandomException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IncidentLogger $incidentLogger,
        private JsonResponder $jsonResponder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    /**
     * @throws RandomException
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        if ($exception instanceof ClientExceptionInterface) {
            return;
        }

        $incidentId = $this->incidentLogger->logError(
            sprintf('Unexpected error. Error: [%s].', $exception::class),
            $exception,
        );

        $errorResponse = new ErrorResponse(
            message: $this->incidentLogger->userMessage($incidentId),
            statusCode: Response::HTTP_INTERNAL_SERVER_ERROR,
            incidentId: $incidentId,
        );

        $event->setResponse(
            $this->wantsJson($event->getRequest())
                ? $this->jsonResponder->respondError($errorResponse, $event->getRequest())
                : new Response(
                    $errorResponse->message,
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    ['Content-Type' => 'text/plain; charset=UTF-8'],
                ),
        );
    }

    private function wantsJson(Request $request): bool
    {
        if (str_starts_with($request->getPathInfo(), '/api')) {
            return true;
        }

        $accept = $request->headers->get('Accept', '');

        return str_contains($accept, 'application/json')
            || $request->getPreferredFormat() === 'json';
    }
}
