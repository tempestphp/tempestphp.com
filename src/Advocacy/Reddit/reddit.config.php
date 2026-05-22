<?php

declare(strict_types=1);

use App\Advocacy\Reddit\RedditConfig;

use function Tempest\env;

return new RedditConfig(
    filters: [
        'php' => ['tempest', 'framework'],
        'laravel' => ['tempest'],
        'symfony' => ['tempest'],
    ],
    clientId: (string) env('REDDIT_CLIENT_ID', ''),
    clientSecret: (string) env('REDDIT_CLIENT_SECRET', ''),
    userAgent: (string) env('REDDIT_USER_AGENT', 'TempestAdvocacyBot/1.0'),
    limit: (int) env('REDDIT_LIMIT', 50),
);
