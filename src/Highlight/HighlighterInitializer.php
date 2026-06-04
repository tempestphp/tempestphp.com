<?php

declare(strict_types=1);

namespace App\Highlight;

use Override;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Languages\Php\PhpLanguage;
use Tempest\Highlight\Themes\CssTheme;

final readonly class HighlighterInitializer implements Initializer
{
    #[Override]
    #[Singleton(tag: 'project')]
    public function initialize(Container $container): Highlighter
    {
        $highlighter = new Highlighter(
            theme: new CssTheme(),
            fallbackLanguage: new PhpLanguage(),
        );

        $highlighter
            ->addLanguage(new TempestViewLanguage())
            ->addLanguage(new TempestConsoleWebLanguage())
            ->addLanguage(new ExtendedJsonLanguage());

        return $highlighter;
    }
}
