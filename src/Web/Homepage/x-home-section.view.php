<?php

$variant ??= 'default';
$snippetLayout ??= 'stack';

// The copy is capped at max-w-xl anyway, so the wider track always goes to the
// code — which means the track order flips along with the columns.
$layoutClass = match ($variant) {
    'wide' => 'flex flex-col gap-8 lg:gap-12',
    'reversed' => 'grid grid-cols-1 lg:grid-cols-[minmax(0,1.25fr)_minmax(0,1fr)] items-start gap-10 lg:gap-16',
    default => 'grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] items-start gap-10 lg:gap-16',
};

// Code columns run much taller than the copy, so the copy sticks alongside it
// rather than stranding the reader in a column of empty background.
$stickyClass = 'lg:sticky lg:top-[calc(var(--ui-header-height)+2rem)]';

$copyClass = match ($variant) {
    'wide' => 'max-w-2xl',
    'reversed' => "max-w-xl lg:order-2 {$stickyClass}",
    default => "max-w-xl {$stickyClass}",
};

$codeClass = match (true) {
    $snippetLayout === 'columns' => 'home-code-columns',
    $variant === 'reversed' => 'flex flex-col gap-4 lg:order-1',
    default => 'flex flex-col gap-4',
};

?>

<section class="px-6 py-10 lg:py-16 tracking-tighter">
  <div class="<?= $layoutClass ?>">
    <!-- Copy -->
    <div class="<?= $copyClass ?>">
      <h2 class="text-2xl md:text-3xl lg:text-4xl leading-tight text-(--ui-text-highlighted)">
        {{ $heading }}
      </h2>
      <p :foreach="$paragraphs as $paragraph" class="mt-4 lg:mt-5 text-lg lg:text-xl text-(--ui-text-muted) leading-snug">
        {{ $paragraph }}
      </p>
      <!--
        Centred on small screens: the labels are long enough to reach the edge
        of a phone-width column, so left-aligning leaves the shorter ones
        looking ragged and wraps the longer ones untidily.
      -->
      <div class="mt-6 lg:mt-8 flex justify-center lg:justify-start">
        <!--
          Tracking is reset here: the section sets tracking-tighter, which suits
          a heading but squashes a full sentence of button copy.
        -->
        <a
          :href="$linkUri"
          class="group no-primary inline-flex items-center justify-center gap-x-2 rounded-lg px-4 py-2.5 font-medium tracking-normal text-center lg:text-left ring ring-inset ring-(--ui-border) text-(--ui-text) bg-(--ui-bg) hover:bg-(--ui-bg-elevated) hover:ring-(--ui-border-accented) focus:outline-hidden focus-visible:no-underline focus-visible:ring-2 focus-visible:ring-(--ui-border-inverted) transition"
        >
          <span>{{ $linkLabel }}</span>
          <x-icon name="tabler:arrow-right" class="size-4.5 shrink-0 text-(--ui-text-dimmed) group-hover:text-(--ui-text) group-hover:translate-x-0.5 transition"/>
        </a>
      </div>
    </div>
    <!-- Code -->
    <div class="home min-w-0 tracking-normal <?= $codeClass ?>">
      <div :foreach="$snippets as $snippet" class="home-code-block">
        {!! $this->codeBlocks[$snippet] !!}
      </div>
    </div>
  </div>
</section>
