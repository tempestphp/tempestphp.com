<?php

namespace App\Markdown\Extensions\GitHubLink;

use App\Web\Documentation\Version;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;

final class GitHubLinkRule implements Rule, ProvidesStopChar
{
    public string $stopChar = '{';

    public function __construct(
        private readonly Version $version,
    ) {}

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('{b`', length: 3)
            || $parser->comesNext('{`', length: 2);
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consumeIncluding('`');
        $content = $parser->consumeUntil('`');
        $parser->consumeIncluding('}');

        return new GitHubLinkToken($this->version, $content);
    }
}