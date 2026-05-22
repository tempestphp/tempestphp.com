<?php

declare(strict_types=1);

use App\Advocacy\Discord\DiscordConfig;

use function Tempest\env;

return new DiscordConfig(
    webhookUrl: (string) env('DISCORD_WEBHOOK_URL', ''),
);
