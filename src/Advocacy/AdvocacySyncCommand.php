<?php

declare(strict_types=1);

namespace App\Advocacy;

use App\Advocacy\Discord\Discord;
use App\Advocacy\Reddit\Reddit;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Schedule;
use Tempest\Console\Scheduler\Every;

final readonly class AdvocacySyncCommand
{
    use HasConsole;

    public function __construct(
        private Reddit $reddit,
        private Discord $discord,
    ) {}

    #[ConsoleCommand, Schedule(Every::HALF_HOUR)]
    public function __invoke(bool $sync = true): void // @mage-expect lint:no-boolean-flag-parameter
    {
        $messages = $this->reddit->fetch();

        //        $messages = [new Message('1', 'Test', ['tempest'], 'https://tempestphp.com')];

        foreach ($messages as $message) {
            if ($sync) {
                $this->discord->notify($message);
            }

            $this->info('[' . implode(',', $message->matches) . '] ' . $message->uri);
        }

        $this->success('Done');
    }
}
