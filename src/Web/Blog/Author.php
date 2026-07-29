<?php

declare(strict_types=1);

namespace App\Web\Blog;

enum Author: string
{
    case BRENT = 'brent';
    case MARK = 'mark';

    public function getFullName(): string
    {
        return match ($this) {
            self::BRENT => 'Brent Roose',
            self::MARK => 'Márk Magyar',
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::BRENT => 'Brent',
            self::MARK => 'Márk',
        };
    }

    public function getBluesky(): string
    {
        return match ($this) {
            self::BRENT => 'brendt.bsky.social',
            default => null,
        };
    }

    public function getX(): string
    {
        return match ($this) {
            self::BRENT => 'brendt_gd',
            default => null,
        };
    }
}
