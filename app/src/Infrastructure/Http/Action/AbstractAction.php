<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\DTO\ErrorResponse;
use App\Application\Exception\ApiException;
use App\Application\Exception\FormValidationException;
use App\Application\Exception\ServiceUnavailableException;
use App\Application\Exception\UnauthorizedException;
use App\Infrastructure\Http\Responder\ResponderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAction
{
    public function __construct(
        protected readonly ResponderInterface $responder,
    ) {
    }

    abstract protected function handleRequest(Request $request): Response;

    public function __invoke(Request $request): Response
    {
        try {
            return $this->handleRequest($request);
        } catch (ServiceUnavailableException $exception) {
            return $this->responder->respondError(
                new ErrorResponse(
                    message: $exception->getMessage(),
                    statusCode: Response::HTTP_SERVICE_UNAVAILABLE,
                ),
                $request,
            );
        } catch (ApiException $exception) {
            return $this->responder->respondError(
                new ErrorResponse(
                    message: $exception->getMessage(),
                    statusCode: $exception->getStatusCode(),
                    context: $exception->getContext(),
                ),
                $request,
            );
        } catch (UnauthorizedException $exception) {
            return $this->responder->respondError(
                new ErrorResponse(
                    message: $exception->getMessage(),
                    statusCode: Response::HTTP_UNAUTHORIZED,
                ),
                $request,
            );
        } catch (FormValidationException $exception) {
            return $this->responder->respondError(
                new ErrorResponse(
                    message: $exception->getMessage(),
                    statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                    context: $exception->getContext(),
                ),
                $request,
            );
        }
    }
}
