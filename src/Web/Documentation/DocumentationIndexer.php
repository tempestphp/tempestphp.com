<?php

declare(strict_types=1);

namespace App\Web\Documentation;

use App\Web\CommandPalette\Command;
use App\Web\CommandPalette\Indexer;
use App\Web\CommandPalette\Type;
use Override;
use Tempest\Markdown\Markdown;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Support\str;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Arr\wrap;
use function Tempest\Support\Str\to_sentence_case;

/**
 * Indexes the blog.
 */
final readonly class DocumentationIndexer implements Indexer
{
    public function __construct(
        private Markdown $markdown,
    ) {}

    /**
     * @return ImmutableArray<Command>
     */
    #[Override]
    public function index(): ImmutableArray
    {
        $version = Version::default();

        return arr(glob(__DIR__ . "/content/{$version->value}/*/*.md"))
            ->flatMap(function (string $path) use ($version) {
                $markdown = $this->markdown->parse(file_get_contents($path));

                $path = new ImmutableString($path);
                $category = $path->beforeLast('/')->afterLast('/')->replaceRegex('/\d+-/', '');
                $chapter = $path->basename('.md')->replaceRegex('/\d+-/', '');
                $title = get_by_key($markdown->frontmatter, 'title');
                $keywords = get_by_key($markdown->frontmatter, 'keywords');

                if (get_by_key($markdown->frontmatter, 'hidden') === true) {
                    return [];
                }

                $main = new Command(
                    title: $title,
                    type: Type::URI,
                    hierarchy: [
                        'Documentation',
                        to_sentence_case($category),
                        $title,
                    ],
                    uri: uri(DocumentationController::class, version: $version, category: $category, slug: $chapter),
                    fields: [
                        ...wrap($keywords),
                    ],
                );

                preg_match_all('/<h2.*?<\/h2>/', $markdown->html, $matches);

                $indices = arr($matches[0] ?? [])
                    ->map(static function (string $h2) use ($main) {
                        $title = str($h2)->afterLast('</span>')->beforeLast('</a')->trim();

                        $slug = $title->kebab();

                        return new Command(
                            title: $title->toString(),
                            type: Type::URI,
                            hierarchy: [
                                ...$main->hierarchy,
                                $slug->toString(),
                            ],
                            uri: $main->uri . '#' . $slug,
                        );
                    })
                    ->filter();

                return [
                    $main,
                    ...$indices,
                ];
            });
    }
}
