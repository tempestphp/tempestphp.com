<?php

declare(strict_types=1);

namespace App\Markdown\Symbols;

use App\Web\Documentation\Version;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Override;
use Tempest\Support\Str;

use function Tempest\Support\str;
use function Tempest\Support\Str\class_basename;
use function Tempest\Support\Str\strip_start;
use function Tempest\Support\Str\to_kebab_case;

final readonly class FqcnParser implements InlineParserInterface
{
    public function __construct(
        private Version $version,
    ) {}

    #[Override]
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::regex("{(b)?`((?:\\\{1,2}\w+|\w+\\\{1,2})(?:\w+\\\{0,2})+)`}");
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

        [$flag, $fqcn] = $inlineContext->getSubMatches();
        $url = str($fqcn)
            ->stripStart(['\\Tempest\\', 'Tempest\\'])
            ->replaceRegex("/^(\w+)/", static fn (array $matches) => sprintf('packages/%s/src', to_kebab_case($matches[0])))
            ->replaceEvery(['date-time' => 'datetime'])
            ->replace('\\', '/')
            ->prepend('https://github.com/tempestphp/tempest-framework/blob/' . $this->version->getBranch() . '/')
            ->append('.php')
            ->toString();

        $link = new Link($url);
        $link->appendChild(new Code($flag === 'b' ? class_basename($fqcn) : strip_start($fqcn, '\\')));
        $inlineContext->getContainer()->appendChild($link);

        return true;
    }
}
