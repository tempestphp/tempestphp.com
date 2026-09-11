<?php

declare(strict_types=1);

namespace App\Web\Homepage;

final readonly class Feature
{
    public function __construct(
        public string $icon,
        public string $title,
        public string $description,
        public string $uri,
    ) {}
}
