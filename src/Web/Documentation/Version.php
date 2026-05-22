<?php

declare(strict_types=1);

namespace App\Web\Documentation;

use Tempest\Support\IsEnumHelper;

enum Version: string
{
    use IsEnumHelper;

    case VERSION_1 = '1.x';
    case VERSION_2 = '2.x';
    case VERSION_3 = '3.x';

    public static function default(): self
    {
        return self::VERSION_3;
    }

    public function isNext(): bool
    {
        return match ($this) { default => false };
    }

    public function getBranch(): string
    {
        return match ($this) {
            self::VERSION_1 => '1.x',
            self::VERSION_2 => '2.x',
            self::VERSION_3 => '3.x',
        };
    }

    public function getUrlSegment(): string
    {
        return $this->value;
    }

    public function isPrevious(): bool
    {
        return ! $this->isNext() && ! $this->isCurrent();
    }

    public function isCurrent(): bool
    {
        return $this === self::default();
    }

    public static function tryFromString(?string $case): ?static
    {
        return match ($case) {
            'default', 'current', 'main', null => self::default(),
            'next' => array_find(self::cases(), static fn (self $version) => $version->isNext()),
            default => self::tryFrom($case),
        };
    }
}
