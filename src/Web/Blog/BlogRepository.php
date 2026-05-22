<?php

declare(strict_types=1);

namespace App\Web\Blog;

use DateTimeImmutable;
use Exception;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Mapper\map;
use function Tempest\Support\arr;

final readonly class BlogRepository
{
    public function __construct(
        private MarkdownConverter $markdown,
    ) {}

    /**
     * @return ImmutableArray<BlogPost>
     * @mago-expect lint:no-boolean-flag-parameter
     */
    public function all(bool $loadContent = false): ImmutableArray
    {
        return arr(glob(__DIR__ . '/articles/*.md'))
            ->reverse()
            ->map(function (string $path) use ($loadContent) {
                preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);

                $data = [
                    'slug' => $matches['slug'],
                    'createdAt' => $this->parseDate($path),
                    'tag' => null,
                    'description' => null,
                    ...YamlFrontMatter::parse(file_get_contents($path))->matter(),
                ];

                if ($data['tag'] ?? null) {
                    $data['tag'] = strtolower($data['tag']);
                }

                if ($data['author'] ?? null) {
                    $data['author'] = strtolower($data['author']);
                }

                if ($loadContent) {
                    $data['content'] = $this->parseContent($path)->getContent();
                }

                return $data;
            })
            ->mapTo(BlogPost::class)
            ->filter(static fn (BlogPost $post) => $post->published);
    }

    public function find(string $slug): ?BlogPost
    {
        $path = glob(__DIR__ . "/articles/*{$slug}*.md")[0] ?? null;

        if (! $path) {
            return null;
        }

        $content = $this->parseContent($path);

        $data = [
            'slug' => $slug,
            'content' => $content->getContent(),
            'createdAt' => $this->parseDate($path),
            ...$content->getFrontMatter(),
        ];

        if (isset($data['tag'])) {
            $data['tag'] = strtolower($data['tag']);
        }

        if (isset($data['author'])) {
            $data['author'] = strtolower($data['author']);
        }

        return map($data)->to(BlogPost::class);
    }

    private function parseContent(string $path): ?RenderedContentWithFrontMatter
    {
        $content = @file_get_contents($path); // @mago-expect lint:no-error-control-operator

        if (! $content) {
            return null;
        }

        $parsed = $this->markdown->convert($content);

        if (! $parsed instanceof RenderedContentWithFrontMatter) {
            throw new Exception("Missing frontmatter or content in {$path}");
        }

        return $parsed;
    }

    private function parseDate(string $path): DateTimeImmutable
    {
        preg_match('#\d+-\d+-\d+#', $path, $matches);

        $date = $matches[0] ?? null;

        return new DateTimeImmutable($date ?? 'now');
    }
}
