<?php

use App\Web\Blog\BlogController;
use App\Web\Community\CommunityController;
use App\Web\Documentation\DocumentationController;

use function Tempest\Router\is_current_uri;
use function Tempest\Router\uri;

$isBlog = is_current_uri([BlogController::class, 'show']) || is_current_uri([BlogController::class, 'index']);
?>

<!-- Header -->
<div class="relative inset-x-0 z-[1] h-(--ui-header-height)">
  <header
      class="group transition-[top,border] z-[1] fixed top-4 data-[scrolling]:top-0 flex justify-center bg-[transparent] border-b border-transparent data-[scrolling]:border-(--ui-border) w-full duration-200 data-[scrolling]:backdrop-blur data-[scrolling]:bg-(--ui-bg)/75"
      id="header"
  >
    <div class="grid grid-cols-[auto_1fr_auto] items-center gap-x-4 px-8 py-4 w-full 2xl:max-w-8xl lg:max-w-5xl xl:max-w-7xl">
      <!-- Left side -->
      <div class="flex items-center gap-4">
        <a href="/" class="flex items-center gap-4">
          <!-- Logo -->
          <div class="size-8">
            <img src="/img/tempest-logo.png" alt="Tempest logo" class="size-full"/>
          </div>
          <span class="hidden lg:inline font-medium">Tempest</span>
        </a>

        <a class="hidden md:inline text-xs tracking-wide font-medium text-(--ui-text-muted) bg-(--ui-bg)/50 px-2 py-1 rounded-lg border border-(--ui-border)" href="https://github.com/tempestphp/tempest-framework/releases/{{ $latest_release }}">
          {{ $this->latest_release }}
        </a>
      </div>

      <!-- Center -->
      <div class="flex items-center gap-4 min-w-0">
        <x-search />
      </div>
      <!-- Right side -->
      <div class="flex items-center gap-5 font-medium text-(--ui-text-muted)">
        <a
          :href="uri([CommunityController::class, 'index'])"
          class="transition hover:text-(--ui-text-highlighted) <?= is_current_uri([CommunityController::class, 'index']) ? 'md:text-(--ui-primary)' : '' ?>"
        >
          Community
        </a>
        <a
          :href="uri([BlogController::class, 'index'])"
          class="transition hover:text-(--ui-text-highlighted) <?= $isBlog ? 'md:text-(--ui-primary)' : '' ?>"
        >
          Blog
        </a>
        <a
          :href="uri([DocumentationController::class, 'index'])"
          class="transition hover:text-(--ui-text-highlighted) <?= is_current_uri([DocumentationController::class, '__invoke']) ? 'md:text-(--ui-primary)' : '' ?>"
        >
          <span class="sm:hidden">Docs</span>
          <span class="hidden sm:inline">Documentation</span>
        </a>
        <a href="https://github.com/tempestphp/tempest-framework" class="transition hover:text-(--ui-text-highlighted) flex items-center gap-x-1.5 ml-4">
          <x-icon name="tabler:brand-github" class="size-6"/>
          <span class="hidden lg:inline font-mono">{{ $this->stargazers_count }}</span>
        </a>
      </div>
    </div>
  </header>
</div>

<script>
const header = document.getElementById('header')

window.addEventListener('scroll', () => {
  if (window.scrollY > 0) {
    header.dataset.scrolling = true
  } else {
    delete header.dataset.scrolling
  }
})
</script>
