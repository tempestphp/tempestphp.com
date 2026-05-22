<?php

declare(strict_types=1);

namespace App\Advocacy;

use function Tempest\Support\str;

final class Message
{
    public string $preview {
        get => str($this->body)
            ->substr(0, 1200)
            ->excerpt(
                from: 0,
                to: 20,
                asArray: true,
            )
            ->map(static fn (string $line) => '> ' . $line)
            ->implode(PHP_EOL)
            ->toString();
    }

    public function __construct(
        public string $id,
        public string $body,
        public array $matches,
        public string $uri,
    ) {}
}
