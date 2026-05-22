<?php

declare(strict_types=1);

namespace App\Advocacy\Reddit;

use RuntimeException;
use Tempest\Cache\Cache;
use Tempest\Container\Tag;
use Tempest\DateTime\Duration;
use Tempest\HttpClient\HttpClient;

use function Tempest\Support\str;

final readonly class RedditApi
{
    public function __construct(
        private RedditConfig $config,
        private HttpClient $http,
        #[Tag('advocacy')]
        private Cache $cache,
    ) {
        $this->config->token = $this->resolveToken();
    }

    private function resolveToken(): string
    {
        return $this->cache->resolve(
            'reddit.access-token',
            function () {
                $response = $this->http->post(
                    uri: 'https://www.reddit.com/api/v1/access_token?grant_type=client_credentials',
                    headers: [
                        'Authorization' => 'Basic ' . base64_encode("{$this->config->clientId}:{$this->config->clientSecret}"),
                        'User-Agent' => $this->config->userAgent,
                    ],
                );

                $payload = $this->decodeJson($response->body);
                $token = $payload['access_token'] ?? null;

                if (! is_string($token) || $token === '') {
                    throw new RuntimeException('Failed to fetch Reddit access token.');
                }

                return $token;
            },
            Duration::minutes(50),
        );
    }

    public function get(string $path): array
    {
        $cacheKey = str("reddit.{$path}")->slug()->toString();

        return $this->cache->resolve(
            $cacheKey,
            function () use ($path) {
                $response = $this->http->get(
                    uri: "https://oauth.reddit.com{$path}?limit={$this->config->limit}",
                    headers: [
                        'Authorization' => "Bearer {$this->config->token}",
                        'User-Agent' => $this->config->userAgent,
                    ],
                );

                $payload = $this->decodeJson($response->body);
                $children = $payload['data']['children'] ?? null;

                if (! is_array($children)) {
                    return [];
                }

                $items = [];

                foreach ($children as $child) {
                    $data = $child['data'] ?? null;

                    if (! is_array($data)) {
                        continue;
                    }

                    $items[] = $data;
                }

                return $items;
            },
            Duration::days(24),
        );
    }

    private function decodeJson(mixed $body): array
    {
        if (is_array($body)) {
            return $body;
        }

        if (! is_string($body) || $body === '') {
            return [];
        }

        $decoded = json_decode($body, associative: true);

        return is_array($decoded) ? $decoded : [];
    }
}
