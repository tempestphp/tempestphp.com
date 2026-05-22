<?php

declare(strict_types=1);

namespace App\StoredEvents;

use DateTimeImmutable;

interface HasCreatedAtDate
{
    public DateTimeImmutable $createdAt { get; }
}
