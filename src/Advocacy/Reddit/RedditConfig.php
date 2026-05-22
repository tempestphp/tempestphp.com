<?php

declare(strict_types=1);

namespace App\Advocacy\Reddit;

use SensitiveParameter;
use Tempest\Http\SensitiveField;

final class RedditConfig
{
    #[SensitiveField]
    public ?string $token = null;

    public function __construct(
        /** var array<string, array<array-key string>> */
        public array $filters,
        #[SensitiveParameter]
        public string $clientId,
        #[SensitiveParameter]
        public string $clientSecret,
        public string $userAgent = 'TempestAdvocacyBot/1.0',
        public int $limit = 50,
    ) {}
}
