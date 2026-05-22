<?php

declare(strict_types=1);

namespace App\Markdown\CodeBlocks;

use App\Markdown\ResolvesInfoWords;
use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Override;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\WebTheme;

final class CodeBlockRenderer implements NodeRendererInterface
{
    use ResolvesInfoWords;

    public function __construct(
        private Highlighter $highlighter = new Highlighter(),
    ) {}

    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        preg_match('/^(?<language>[\w]+)(\{(?<startAt>[\d]+)\})?/', $node->getInfoWords()[0] ?? 'txt', $matches);

        $highlighter = $this->highlighter;

        if ($startAt = $matches['startAt'] ?? null) {
            $highlighter = $highlighter->withGutter((int) $startAt);
        }

        $language = $matches['language'] ?? 'txt';
        $parsed = $highlighter->parse($node->getLiteral(), $language);
        $theme = $highlighter->getTheme();

        if ($theme instanceof WebTheme) {
            $pre = $theme->preBefore($highlighter) . $parsed . $theme->preAfter($highlighter);

            if ($this->getInfoWords($node)[1] ?? false) {
                return <<<HTML
                    <div class="code-block named-code-block">
                        <div class="code-block-name">{$this->getInfoWords($node)[1]}</div>
                        {$pre}
                    </div>
                    HTML;
            }

            return <<<HTML
                <div class="code-block named-code-block">
                    {$pre}
                </div>
                HTML;
        }

        return '<pre data-lang="' . $language . '" class="notranslate">' . $parsed . '</pre>';
    }
}
