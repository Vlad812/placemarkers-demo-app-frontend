<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\ApiProxyResponse;
use App\Application\Query\HealthCheckQuery;

final readonly class HealthCheckHandler
{
    /**
     * @param HealthCheckQuery $query
     * @return ApiProxyResponse
     */
    public function __invoke(HealthCheckQuery $query): ApiProxyResponse
    {
        return new ApiProxyResponse(200, ['status' => 'ok']);
    }
}
