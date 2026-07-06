<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\DTO\Api\HttpApiResult;
use App\Application\DTO\ResponsePayloadInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class ApiProxyResponse implements ResponsePayloadInterface
{
    /**
     * @param array<string, mixed>|list<mixed> $body
     */
    public function __construct(
        public int $status,
        public array $body,
    ) {
    }

    /**
     * @param array|HttpApiResult $result
     * @return ApiProxyResponse
     */
    public static function fromClientResult(array|HttpApiResult $result): self
    {
        if ($result instanceof HttpApiResult) {
            return new self($result->status, $result->body);
        }

        return new self($result['status'], $result['body']);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}
