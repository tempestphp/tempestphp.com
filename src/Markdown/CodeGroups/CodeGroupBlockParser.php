<?php

declare(strict_types=1);

namespace App\Markdown\CodeGroups;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Util\RegexHelper;
use Override;

final class CodeGroupBlockParser implements BlockContinueParserInterface
{
    private CodeGroupBlock $block;
    private bool $finished = false;

    public function __construct()
    {
        $this->block = new CodeGroupBlock();
    }

    #[Override]
    public function addLine(string $line): void {}

    #[Override]
    public function getBlock(): AbstractBlock
    {
        return $this->block;
    }

    #[Override]
    public function isContainer(): bool
    {
        return true;
    }

    #[Override]
    public function canContain(AbstractBlock $child_block): bool
    {
        return true;
    }

    #[Override]
    public function canHaveLazyContinuationLines(): bool
    {
        return false;
    }

    #[Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $active_block_parser): ?BlockContinue
    {
        if ($cursor->isIndented()) {
            return BlockContinue::at($cursor);
        }

        $match = RegexHelper::matchFirst('/^:::$/', $cursor->getLine());

        if ($match !== null) {
            $this->finished = true;

            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }

    #[Override]
    public function closeBlock(): void {}

    public function isFinished(): bool
    {
        return $this->finished;
    }
}
