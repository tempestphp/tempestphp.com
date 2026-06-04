<?php

namespace App\Markdown;

use App\Markdown\Extensions\Heading\HeadingRule;
use App\Markdown\Extensions\Lists\ListRule;
use App\Markdown\Extensions\Paragraph\ParagraphRule;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Rules\ParagraphRule as TempestParagraphRule;
use Tempest\Markdown\Rules\ListRule as TempestListRule;
use Tempest\Markdown\Rules\HeadingRule as TempestHeadingRule;

final readonly class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Markdown
    {
        return new Markdown(
            highlighter: $container->get(Highlighter::class, tag: 'project'),
        )
            ->removeRules(
                TempestParagraphRule::class,
                TempestListRule::class,
                TempestHeadingRule::class,
            )
            ->prependRules(
                $container->get(ListRule::class),
                $container->get(HeadingRule::class),
            )
            ->appendRules(
                $container->get(ParagraphRule::class),
            );
    }
}