<?php

declare(strict_types=1);

namespace App\Advocacy\Reddit;

use App\Advocacy\Message;
use Tempest\Cache\Cache;
use Tempest\Container\Tag;

final readonly class Reddit
{
    public function __construct(
        private RedditConfig $config,
        private RedditApi $api,
        private RedditMessageFactory $messageFactory,
        #[Tag('advocacy')]
        private Cache $cache,
    ) {}

    /** @return Message[] */
    public function fetch(): array
    {
        $messages = [];

        foreach ($this->config->filters as $subreddit => $keywords) {
            $messages = [
                ...$messages,
                ...$this->fetchForSubreddit($subreddit, $keywords),
            ];
        }

        return $messages;
    }

    /** @return Message[] */
    private function fetchForSubreddit(string $subreddit, array $keywords): array
    {
        $posts = $this->api->get("/r/{$subreddit}/new");
        $comments = $this->api->get("/r/{$subreddit}/comments");

        return $this->makeMessages([...$posts, ...$comments], $subreddit, $keywords);
    }

    /** @return Message[] */
    private function makeMessages(array $items, string $subreddit, array $keywords): array
    {
        $messages = [];

        foreach ($items as $item) {
            $message = $this->messageFactory->fromRedditItem($item, $keywords);

            if ($message === null || $this->cache->has($message->id)) {
                continue;
            }

            $this->cache->put($message->id, true);

            $messages[] = $message;
        }

        return $messages;
    }
}
