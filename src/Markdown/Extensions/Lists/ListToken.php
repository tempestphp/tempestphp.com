<?php

namespace App\Markdown\Extensions\Lists;

use App\Markdown\Extensions\GitHubLink\GitHubLinkRule;
use App\Markdown\Extensions\Link\LinkRule;
use App\Web\Documentation\Version;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class ListToken implements Token
{
    public function __construct(
        private readonly Version $version,
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new GitHubLinkRule($this->version),
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        ]);

        $list = '<ul>';

        foreach ($this->items as $item) {
            $content = $parser->parse($item->content);
            $children = $item->children?->parse($parser) ?? '';
            $list .= "<li>{$content}{$children}</li>";
        }

        $list .= '</ul>';

        return $list;
    }
}
