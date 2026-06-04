<?php

declare(strict_types=1);

namespace App\Web\Homepage;

use Tempest\Http\Responses\Redirect;
use Tempest\Markdown\Markdown;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;

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
            fn (string $path) => yield strip_end(basename($path), '.md') => $this->markdown->parse(file_get_contents($path))->html,
        );

        return \Tempest\View\view('./home.view.php', codeBlocks: $codeBlocks);
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
