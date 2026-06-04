<?php

namespace Tests\Markdown\Extensions\GitHubLink;

use App\Markdown\Extensions\Paragraph\ParagraphToken;
use App\Web\Documentation\Version;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

class GitHubLinkRuleTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function test_parse(): void
    {
        $token = new ParagraphToken(
            Version::VERSION_3,
            'These attributes implement the {b`Tempest\Router\Route`} interface, allowing custom route attributes to be created',
        );

        $parsed = $token->parse($this->parser);

        $this->assertStringContainsString(
            'https://github.com/tempestphp/tempest-framework/blob/3.x/packages/router/src/Route.php',
            $parsed,
        );
    }

    #[Test]
    public function test_parse_without_b(): void
    {
        $token = new ParagraphToken(
            Version::VERSION_3,
            'These attributes implement the {`Tempest\Router\Route`} interface, allowing custom route attributes to be created',
        );

        $parsed = $token->parse($this->parser);

        $this->assertStringContainsString(
            'https://github.com/tempestphp/tempest-framework/blob/3.x/packages/router/src/Route.php',
            $parsed,
        );
    }
}
