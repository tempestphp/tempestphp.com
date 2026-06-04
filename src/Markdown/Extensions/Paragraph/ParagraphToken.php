<?php

namespace App\Markdown\Extensions\Paragraph;

use App\Markdown\Extensions\GitHubLink\GitHubLinkRule;
use App\Markdown\Extensions\Link\LinkRule;
use App\Web\Documentation\Version;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final readonly class ParagraphToken implements Token
{
    public function __construct(
        public Version $version,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new GitHubLinkRule($this->version),
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new StrikethroughRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        ]);

        $content = $parser->parse($this->content);

        return "<p>{$content}</p>";
    }
}