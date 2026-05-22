<?php

declare(strict_types=1);

namespace App\Markdown\Alerts;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Util\RegexHelper;
use Override;

final class AlertBlockParser implements BlockContinueParserInterface
{
    protected AlertBlock $block;
    protected bool $finished = false;

    public function __construct(
        protected string $alertType,
        protected ?string $icon,
        protected ?string $title,
    ) {
        $this->block = new AlertBlock($alertType, $icon, $title);
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
    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    #[Override]
    public function canHaveLazyContinuationLines(): bool
    {
        return false;
    }

    public function parseInlines(): bool
    {
        return true;
    }

    #[Override]
    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): ?BlockContinue
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
    public function closeBlock(): void
    {
        // Nothing to do here
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }
}
