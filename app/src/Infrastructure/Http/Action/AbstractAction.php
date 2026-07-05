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
            return $this->responder->respondError(new ErrorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_SERVICE_UNAVAILABLE,
                context: $this->buildErrorContext($request),
            ));
        } catch (ApiException $exception) {
            return $this->responder->respondError(new ErrorResponse(
                message: $exception->getMessage(),
                statusCode: $exception->getStatusCode(),
                context: $this->buildErrorContext($request, $exception->getContext()),
            ));
        } catch (UnauthorizedException $exception) {
            return $this->responder->respondError(new ErrorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNAUTHORIZED,
                context: $this->buildErrorContext($request),
            ));
        } catch (FormValidationException $exception) {
            return $this->responder->respondError(new ErrorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
                context: $exception->getContext(),
            ));
        }
    }

    /**
     * @param array<string, mixed> $extra
     *
     * @return array<string, mixed>
     */
    protected function buildErrorContext(Request $request, array $extra = []): array
    {
        $context = array_merge(
            $request->query->all(),
            $request->request->all(),
        );

        foreach ($request->attributes->all() as $key => $value) {
            if (!is_string($key) || str_starts_with($key, '_')) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $context[$key] = $value;
            }
        }

        return array_merge($context, $extra);
    }
}
