<?php

declare(strict_types=1);

namespace App\Web\Challenges;

use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;

final class ChallengesController
{
    #[StaticPage, Get('/challenges/parsing-100m-lines')]
    public function parsing100mLines(): GenericResponse
    {
        return new GenericResponse(
            status: Status::OK,
            body: file_get_contents(__DIR__ . '/parsing-100m-lines.html'),
            headers: [
                'Content-Type' => 'text/html; charset=utf-8',
            ],
        );
    }
}
