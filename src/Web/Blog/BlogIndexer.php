<?php

declare(strict_types=1);

namespace App\Web\Blog;

use App\Web\CommandPalette\Command;
use App\Web\CommandPalette\Indexer;
use App\Web\CommandPalette\Type;
use Override;
use Tempest\Markdown\Markdown;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Arr\wrap;

final readonly class BlogIndexer implements Indexer
{
    public function __construct(
        private Markdown $markdown,
    ) {}

    #[Override]
    public function index(): ImmutableArray
    {
        return arr(glob(__DIR__ . '/articles/*.md'))
            ->map(function (string $path) {
                $parsed = $this->markdown->parse(file_get_contents($path));
                preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);

                $frontmatter = $parsed->frontmatter;
                $title = get_by_key($frontmatter, 'title');
                $author = get_by_key($frontmatter, 'author');
                $description = get_by_key($frontmatter, 'description');
                $keywords = get_by_key($frontmatter, 'keywords');
                $tags = get_by_key($frontmatter, 'tag');

                return new Command(
                    title: $title,
                    type: Type::URI,
                    hierarchy: [
                        'Blog',
                        Author::tryFrom($author)?->getName() ?? 'Tempest',
                        $title,
                    ],
                    uri: uri([BlogController::class, 'show'], slug: $matches['slug']),
                    fields: [
                        $author,
                        $description,
                        ...wrap($keywords),
                        ...wrap($tags),
                    ],
                );
            });
    }
}
