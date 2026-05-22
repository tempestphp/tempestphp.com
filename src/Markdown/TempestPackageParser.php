<?php

declare(strict_types=1);

namespace App\Markdown;

use App\Web\Documentation\Version;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Override;

use function Tempest\Support\str;

final readonly class TempestPackageParser implements InlineParserInterface
{
    public function __construct(
        private Version $version,
    ) {}

    #[Override]
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex("{`tempest\\/([\w-]+)`}");
    }

    #[Override]
    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $previousChar = $cursor->peek(-1);

        if ($previousChar !== null && $previousChar !== ' ') {
            return false;
        }

        $cursor->advanceBy($inlineContext->getFullMatchLength());

        [$package] = $inlineContext->getSubMatches();

        $url = match ($package) {
            'app' => 'https://github.com/tempestphp/tempest-app',
            default => str($package)
                ->kebab()
                ->prepend('https://github.com/tempestphp/tempest-framework/tree/' . $this->version->getBranch() . '/packages/')
                ->toString(),
        };

        $link = new Link($url);
        $link->appendChild(new Code("tempest/{$package}"));
        $inlineContext->getContainer()->appendChild($link);

        return true;
    }
}
