<?php

namespace App\Markdown\Extensions\Heading;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class HeadingToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
    ) {}

    public function parse(Parser $parser): string
    {
        $tag = "h{$this->level}";

        $slug = $this->content |> trim(...) |> strtolower(...) |> (fn (string $x) => str_replace(' ', '-', $x));

        $id = " id=\"{$slug}\"";

        $content = $parser
            ->forToken($this, [
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new CodeRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        if ($this->level === 2 || $this->level === 3) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 9h14M5 15h14M11 4L7 20M17 4l-4 16"/></svg>';

            return "<{$tag}{$id}><a href=\"#{$slug}\" class=\"heading-permalink\"><span>{$svg}</span> {$content}</a></{$tag}>";
        }

        return "<{$tag}{$id}>{$content}</{$tag}>";
    }
}
