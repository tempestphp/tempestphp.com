<?php

declare(strict_types=1);

namespace App\Markdown\Extensions\GitHubLink;

use App\Web\Documentation\Version;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

use function Tempest\Support\str;
use function Tempest\Support\Str\to_kebab_case;

final readonly class GitHubLinkToken implements Token
{
    public function __construct(
        private Version $version,
        private string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $uri = str($this->content)
            ->stripStart('#[')
            ->stripEnd(']')
            ->stripStart(['\\Tempest\\', 'Tempest\\'])
            ->replaceRegex("/^(\w+)/", static fn (array $matches) => sprintf('packages/%s/src', to_kebab_case($matches[0])))
            ->replaceEvery(['date-time' => 'datetime'])
            ->replace('\\', '/')
            ->prepend('https://github.com/tempestphp/tempest-framework/blob/' . $this->version->getBranch() . '/')
            ->append('.php')
            ->toString();

        $isAttribute = str_starts_with($this->content, '#[');
        $text = str($this->content)
            ->stripStart('#[')
            ->stripEnd(']')
            ->stripStart('\\')
            ->classBasename()
            ->wrap(
                $isAttribute ? '#[<span class="hl-type">' : '<span class="hl-type">',
                $isAttribute ? '</span>]' : '</span>',
            )
            ->toString();

        return sprintf(
            '<a href="%s"><code>%s</code></a>',
            $uri,
            $text,
        );
    }
}
