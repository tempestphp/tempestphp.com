<?php

declare(strict_types=1);

namespace App\Advocacy\Reddit;

use App\Advocacy\Message;

// @mago-expect lint:cyclomatic-complexity
final class RedditMessageFactory
{
    public function fromRedditItem(array $item, array $keywords): ?Message
    {
        $id = $this->resolveId($item);

        if ($id === null) {
            return null;
        }

        $matchedKeyword = $this->findMatchedKeyword($item, $keywords);

        if ($matchedKeyword === null) {
            return null;
        }

        $body = trim((string) ($item['selftext'] ?? $item['body'] ?? ''));

        return new Message(
            id: "advocacy.reddit.message.{$id}",
            body: $body,
            matches: [$matchedKeyword],
            uri: $this->resolveUri($item),
        );
    }

    private function findMatchedKeyword(array $item, array $keywords): ?string
    {
        $content = mb_strtolower(implode(' ', [
            (string) ($item['title'] ?? ''),
            (string) ($item['selftext'] ?? ''),
            (string) ($item['body'] ?? ''),
            (string) ($item['link_title'] ?? ''),
        ]));

        foreach ($keywords as $keyword) {
            if (mb_stripos($content, mb_strtolower($keyword)) === false) {
                continue;
            }

            return $keyword;
        }

        return null;
    }

    private function resolveId(array $item): ?string
    {
        $id = $item['name'] ?? $item['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveUri(array $item): string
    {
        $permalink = (string) ($item['permalink'] ?? '');

        if ($permalink !== '') {
            return 'https://reddit.com' . $permalink;
        }

        return (string) ($item['url'] ?? 'https://reddit.com');
    }
}
