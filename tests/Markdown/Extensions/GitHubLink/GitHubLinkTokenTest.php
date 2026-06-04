<?php

namespace Tests\Markdown\Extensions\GitHubLink;

use App\Markdown\Extensions\GitHubLink\GitHubLinkToken;
use App\Web\Documentation\Version;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

class GitHubLinkTokenTest extends TestCase
{
    #[Test]
    public function test_with_class(): void
    {
        $token = new GitHubLinkToken(Version::VERSION_3, 'Tempest\Router\Route');

        $html = $token->parse(new Parser());

        $this->assertSame(
            '<a href="https://github.com/tempestphp/tempest-framework/blob/3.x/packages/router/src/Route.php"><code><span class="hl-type">Route</span></code></a>',
            $html,
        );
    }

    #[Test]
    public function test_with_class_and_leading_slash(): void
    {
        $token = new GitHubLinkToken(Version::VERSION_3, '\Tempest\Router\Route');

        $html = $token->parse(new Parser());

        $this->assertSame(
            '<a href="https://github.com/tempestphp/tempest-framework/blob/3.x/packages/router/src/Route.php"><code><span class="hl-type">Route</span></code></a>',
            $html,
        );
    }

    #[Test]
    public function test_with_attribute_and_leading_slash(): void
    {
        $token = new GitHubLinkToken(Version::VERSION_3, '#[Tempest\Discovery\SkipDiscovery]');

        $html = $token->parse(new Parser());

        $this->assertSame(
            '<a href="https://github.com/tempestphp/tempest-framework/blob/3.x/packages/discovery/src/SkipDiscovery.php"><code>#[<span class="hl-type">SkipDiscovery</span>]</code></a>',
            $html,
        );
    }
}
