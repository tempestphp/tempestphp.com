<?php

declare(strict_types=1);

namespace App\Markdown;

use App\Markdown\Alerts\AlertExtension;
use App\Markdown\CodeBlocks\CodeBlockRenderer;
use App\Markdown\CodeBlocks\InlineCodeBlockRenderer;
use App\Markdown\CodeGroups\CodeGroupBlock;
use App\Markdown\CodeGroups\CodeGroupBlockRenderer;
use App\Markdown\CodeGroups\CodeGroupExtension;
use App\Markdown\Symbols\AttributeParser;
use App\Markdown\Symbols\FqcnParser;
use App\Markdown\Symbols\FunctionParser;
use App\Markdown\Symbols\HandleParser;
use App\Web\Documentation\Version;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use Override;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;

final readonly class LeagueMarkdownInitializer implements Initializer
{
    #[Override]
    #[Singleton]
    public function initialize(Container $container): MarkdownConverter
    {
        $environment = new Environment();
        $highlighter = $container->get(Highlighter::class, tag: 'project');

        $codeBlockRenderer = new CodeBlockRenderer($highlighter);
        $version = $container->get(Version::class);

        $environment
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new FrontMatterExtension())
            ->addExtension(new AttributesExtension())
            ->addExtension(new AlertExtension())
            ->addExtension(new CodeGroupExtension())
            ->addExtension(new TableExtension())
            ->addInlineParser(new TempestPackageParser($version))
            ->addInlineParser(new FqcnParser($version))
            ->addInlineParser(new AttributeParser($version))
            ->addInlineParser(new FunctionParser($version))
            ->addInlineParser(new HandleParser())
            ->addRenderer(FencedCode::class, $codeBlockRenderer)
            ->addRenderer(Code::class, new InlineCodeBlockRenderer($highlighter))
            ->addRenderer(Link::class, new LinkRenderer())
            ->addRenderer(Heading::class, new HeadingRenderer())
            ->addRenderer(CodeGroupBlock::class, new CodeGroupBlockRenderer($codeBlockRenderer));

        return new MarkdownConverter($environment);
    }
}
