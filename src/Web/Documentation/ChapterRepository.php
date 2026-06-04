<?php

declare(strict_types=1);

namespace App\Web\Documentation;

use App\Support\HasMemoization;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\MarkdownConverter;
use RuntimeException;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Markdown\Markdown;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\Arr\get_by_key;
use function Tempest\Support\Path\to_relative_path;
use function Tempest\Support\Regex\replace;
use function Tempest\Support\str;

final class ChapterRepository
{
    use HasMemoization;

    public function __construct(
        private readonly Markdown $markdown,
    ) {}

    public function find(Version $version, string $category, string $slug): ?Chapter
    {
        $category = replace($category, '/^\d+-/', '');
        $slug = replace($slug, '/^\d+-/', '');
        $directory = __DIR__ . "/content/{$version->getUrlSegment()}";

        if (! is_dir($directory)) {
            throw new RuntimeException("Documentation for version {$version->value} has not been fetched. Run `tempest docs:pull {$version->value}`.");
        }

        $path = ImmutableArray::createFrom(recursive_search(
            folder: $directory,
            pattern: sprintf("#.*\/(\d+-)?%s\/(\d+-)?%s\.md#", preg_quote($category, '#'), preg_quote($slug, '#')),
        ))->sort()->first();

        if (! $path) {
            return null;
        }

        $raw = file_get_contents($path);
        $markdown = $this->markdown->parse($raw);

        $frontmatter = $markdown->frontmatter;
        $title = get_by_key($frontmatter, 'title');
        $description = get_by_key($frontmatter, 'description');
        $hidden = get_by_key($frontmatter, 'hidden');

        return new Chapter(
            version: $version,
            category: $category,
            slug: $slug,
            body: $markdown->html,
            raw: $raw,
            title: $title,
            path: to_relative_path(root_path(), $path),
            hidden: $hidden ?? false,
            description: $description ?? null,
        );
    }

    /**
     * @return ImmutableArray<Chapter>
     */
    public function all(Version $version, string $category = '*'): ImmutableArray
    {
        return $this->memoize(
            $version->value . $category,
            static fn () => arr(glob(__DIR__ . "/content/{$version->getUrlSegment()}/*{$category}/*.md"))
                ->map(static function (string $path) use ($version) {
                    $content = file_get_contents($path);
                    $category = str($path)->beforeLast('/')->afterLast('/')->replaceRegex('/^\d+-/', '');

                    preg_match('/(?<index>\d+-)?(?<slug>.*)\.md/', pathinfo($path, PATHINFO_BASENAME), $matches);

                    return [
                        'version' => $version,
                        'slug' => replace($matches['slug'], '/^\d+-/', ''),
                        'index' => $matches['index'],
                        'category' => $category->toString(),
                        'path' => to_relative_path(root_path(), $path),
                        ...YamlFrontMatter::parse($content)->matter(),
                    ];
                })
                ->filter(static fn (array $chapter) => get_by_key($chapter, 'hidden') !== true)
                ->mapTo(Chapter::class),
        );
    }
}
