<?php

declare(strict_types=1);

namespace App\Web\Homepage;

use App\Web\Documentation\DocumentationController;
use App\Web\Documentation\Version;
use Tempest\Http\Responses\Redirect;
use Tempest\Markdown\Markdown;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

use function Tempest\Router\uri;
use function Tempest\Support\Arr\map_with_keys;
use function Tempest\Support\Str\strip_end;

final readonly class HomeController
{
    public function __construct(
        private Markdown $markdown,
    ) {}

    #[StaticPage]
    #[Get('/')]
    public function __invoke(): View
    {
        $codeBlocks = map_with_keys(
            glob(__DIR__ . '/codeblocks/*.md'),
            fn (string $path) => yield strip_end(basename($path), '.md') => $this->emphasiseFilenames(
                $this->markdown->parse(file_get_contents($path))->html,
            ),
        );

        return \Tempest\View\view(
            './home.view.php',
            codeBlocks: $codeBlocks,
            features: $this->features(),
        );
    }

    /**
     * Splits a code block's title into its directory and its filename so the
     * header can let the path recede and the filename carry. Done here rather
     * than in JavaScript so there is no flash of unstyled path on load.
     *
     * Titles that aren't file paths — a console command, say — are left alone.
     */
    private function emphasiseFilenames(string $html): string
    {
        return preg_replace_callback(
            '#(<div class="code-title">)([^<]+)(</div>)#',
            static function (array $matches): string {
                $title = $matches[2];

                if (preg_match('#^[\w./@-]+/[\w.-]+\.\w+$#', $title) !== 1) {
                    return $matches[0];
                }

                $separator = strrpos($title, '/');

                return sprintf(
                    '%s<span class="code-title-dir">%s</span><span class="code-title-name">%s</span>%s',
                    $matches[1],
                    substr($title, 0, $separator + 1),
                    substr($title, $separator + 1),
                    $matches[3],
                );
            },
            $html,
        );
    }

    /**
     * The grab-bag of capabilities shown as cards below the main feature
     * sections. Each one links straight into its documentation chapter.
     *
     * @return Feature[]
     */
    private function features(): array
    {
        $docs = static fn (string $category, string $slug) => uri(
            DocumentationController::class,
            version: Version::default(),
            category: $category,
            slug: $slug,
        );

        return [
            new Feature(
                icon: 'tabler:box',
                title: 'Container',
                description: 'Autowiring that resolves your dependencies without a single binding.',
                uri: $docs('essentials', 'container'),
            ),
            new Feature(
                icon: 'tabler:shield-check',
                title: 'Validation',
                description: 'Attribute-based rules that validate and map in one step.',
                uri: $docs('features', 'validation'),
            ),
            new Feature(
                icon: 'tabler:transform',
                title: 'Data mapping',
                description: 'Turn arrays, JSON or requests into objects, and back again.',
                uri: $docs('features', 'mapper'),
            ),
            new Feature(
                icon: 'tabler:broadcast',
                title: 'Events',
                description: 'Discovered event handlers, no listener registration needed.',
                uri: $docs('features', 'events'),
            ),
            new Feature(
                icon: 'tabler:arrow-guide',
                title: 'Command bus',
                description: 'Dispatch commands synchronously or to the background.',
                uri: $docs('features', 'command-bus'),
            ),
            new Feature(
                icon: 'tabler:mail',
                title: 'Mail',
                description: 'Send mailables built from the same view components you already use.',
                uri: $docs('features', 'mail'),
            ),
            new Feature(
                icon: 'tabler:lock',
                title: 'Authentication',
                description: 'Sessions, permissions and OAuth providers out of the box.',
                uri: $docs('features', 'authentication'),
            ),
            new Feature(
                icon: 'tabler:world-code',
                title: 'Static pages',
                description: 'Generate a fully static site from the routes you already wrote.',
                uri: $docs('features', 'static-pages'),
            ),
            new Feature(
                icon: 'tabler:flask',
                title: 'Testing',
                description: 'First-class helpers for HTTP, console, mail and the container.',
                uri: $docs('essentials', 'testing'),
            ),
        ];
    }

    #[Get('/view')]
    public function viewRedirect(): Redirect
    {
        return new Redirect('/current/essentials/views');
    }

    #[Get('/console')]
    public function consoleRedirect(): Redirect
    {
        return new Redirect('current/packages/console');
    }
}
