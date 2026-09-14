<?php

use App\Web\Documentation\DocumentationController;
use App\Web\Documentation\Version;
use App\Web\RedirectsController;

use function Tempest\Router\uri;

?>

<x-base full-title="Tempest, the PHP framework that gets out of your way" :stargazers="$this->stargazers" body-class="bg-[#f1f7ff] dark:bg-[black]">
    <main class="relative flex flex-col grow -mt-(--ui-header-height)">
        <!-- Hero. Full-bleed so the glow reaches the screen edges; the content
             inside stays on the same container as every section below it. -->
        <!-- Capped so the hero doesn't strand its content in the middle of a very
             tall viewport; on a typical laptop this is just 100svh. -->
        <section class="relative flex flex-col justify-center min-h-[min(100svh,56rem)] pt-24">
            <div class="hero-glow z-[-1] absolute inset-0 overflow-hidden pointer-events-none"></div>
            <div class="container mx-auto px-6 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] items-center gap-10 lg:gap-16">
                <!-- Copy -->
                <div class="flex flex-col items-start">
                    <h1 class="flex flex-col text-3xl md:text-4xl lg:text-5xl leading-none tracking-tighter">
                        <span>The framework that</span>
                        <span class="text-(--ui-primary)">gets out of your way</span>
                    </h1>
                    <p class="mt-5 lg:mt-7 max-w-xl text-lg md:text-xl lg:text-2xl text-(--ui-text-toned) leading-snug">
                        Focus on code that matters and brings value for the modern web. That's what Tempest is about.
                    </p>
                    <div class="mt-6 lg:mt-8 max-w-full flex flex-col xl:flex-row gap-4 items-center sm:items-start xl:items-center">
                        <x-install-command/>
                        <a
                                :href="uri([DocumentationController::class, 'index'])"
                                class="bg-(--ui-bg-inverted) text-(--ui-bg) hover:bg-(--ui-bg-inverted)/90 rounded-xl px-5 py-3 transition flex gap-2 items-center"
                        >
                            <span class="grow whitespace-nowrap">Get started</span>
                        </a>
                    </div>
                    <!-- Proof -->
                    <ul class="hidden lg:flex flex-wrap items-center gap-x-5 gap-y-2 mt-6 lg:mt-8 font-mono text-sm text-(--ui-text-dimmed)">
                        <li class="flex items-center gap-x-1.5">
                            <x-icon name="tabler:star" class="size-4"/>
                            <span>{{ $this->stargazers_count }} stars</span>
                        </li>
                        <li class="flex items-center gap-x-1.5">
                            <x-icon name="tabler:tag" class="size-4"/>
                            <span>{{ $this->latest_release }}</span>
                        </li>
                        <li class="flex items-center gap-x-1.5">
                            <x-icon name="tabler:brand-php" class="size-4"/>
                            <span>PHP 8.5+</span>
                        </li>
                    </ul>
                </div>
                <!-- Code -->
                <div class="home min-w-0">
                    <div class="home-code-block">
                        {!! $this->codeBlocks['hero'] !!}
                    </div>
                </div>
            </div>
            <div class="bottom-0 absolute inset-x-0 hidden lg:flex justify-center items-center p-8">
                <a
                        id="scroll-indicator"
                        href="#features"
                        class="flex flex-col items-center gap-2 text-(--ui-text-dimmed) hover:text-(--ui-text) transition"
                >
                    <span class="animate-pulse">Learn more</span>
                    <x-icon name="tabler:arrow-down" class="size-5"/>
                </a>
            </div>
        </section>

        <div class="container mx-auto">
            <!-- Features -->
            <div id="features" class="scroll-mt-(--ui-header-height)">
                <!-- Discovery -->
                <x-home-section
                        variant="wide"
                        snippet-layout="columns"
                        heading="Zero-configuration with code discovery"
                        :paragraphs="[
          'Tempest scans your code and instantly registers routes, view components, console commands, middleware and more. It doesn’t need hand-holding; it just works.',
        ]"
                        link-label="Learn more about discovery"
                        :link-uri="uri(DocumentationController::class, version: Version::default(), category: 'essentials', slug: 'discovery')"
                        :snippets="['controller', 'view-component', 'event-handler']"
                ></x-home-section>

                <!-- Template engine -->
                <x-home-section
                        heading="A refreshing new template engine"
                        :paragraphs="[
          'Tempest reimagines templating in PHP with a clean front-end engine, inspired by modern front-end frameworks.',
          'Whether you love our modern syntax or prefer the battle-tested reliability of Blade and Twig, Tempest has you covered.',
        ]"
                        link-label="Read about tempest/view"
                        :link-uri="uri(DocumentationController::class, version: Version::default(), category: 'essentials', slug: 'views')"
                        :snippets="['templating-view']"
                ></x-home-section>

                <!-- ORM -->
                <x-home-section
                        variant="reversed"
                        heading="A truly decoupled ORM"
                        :paragraphs="[
          'Models in Tempest embrace modern PHP and are designed to be decoupled from the database; they don’t even have to persist to the database and can be mapped to any kind of data source.',
        ]"
                        link-label="Learn more about the ORM"
                        :link-uri="uri(DocumentationController::class, version: Version::default(), category: 'essentials', slug: 'database')"
                        :snippets="['model', 'orm']"
                ></x-home-section>

                <!-- Console -->
                <x-home-section
                        heading="Console applications reimagined"
                        :paragraphs="[
          'Console commands are automatically discovered and use PHP’s type system to define arguments and flags.',
          'No need to search the documentation to remember the syntax, just write PHP.',
        ]"
                        link-label="Learn about console commands"
                        :link-uri="uri(DocumentationController::class, version: Version::default(), category: 'essentials', slug: 'console-commands')"
                        :snippets="['console']"
                ></x-home-section>
            </div>

            <!-- Everything else -->
            <section class="px-6 md:py-12 lg:py-20 tracking-tighter">
                <div class="max-w-2xl">
                    <h2 class="text-2xl md:text-3xl lg:text-4xl leading-tight text-(--ui-text-highlighted)">
                        And much, much more.
                    </h2>
                    <p class="mt-4 lg:mt-5 text-lg lg:text-xl text-(--ui-text-muted) leading-snug">
                        Everything an application needs is already in the box — no plugin hunting, no wiring, no ceremony.
                    </p>
                </div>
                <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-10 tracking-normal">
                    <x-feature-card
                            :foreach="$this->features as $feature"
                            :icon="$feature->icon"
                            :title="$feature->title"
                            :description="$feature->description"
                            :uri="$feature->uri"
                    ></x-feature-card>
                </ul>
            </section>

            <!-- Closing call to action -->
            <section class="px-6 pt-4 pb-20 lg:pt-6 lg:pb-28 tracking-tighter">
                <div class="flex flex-col items-center gap-6 px-6 py-14 lg:py-20 text-center rounded-2xl border border-(--ui-border) bg-(--ui-bg)/40">
                    <h2 class="max-w-xl text-2xl md:text-3xl lg:text-4xl leading-tight text-(--ui-text-highlighted)">
                        Start building in one command.
                    </h2>
                    <p class="max-w-lg text-lg text-(--ui-text-muted) leading-snug tracking-normal">
                        No scaffolding to wade through, no configuration to write. Just a project and your business logic.
                    </p>
                    <div class="max-w-full mt-2">
                        <x-install-command/>
                    </div>
                    <div class="flex flex-wrap justify-center items-center gap-2 mt-2 font-medium">
                        <a :href="uri([DocumentationController::class, 'index'])" class="bg-(--ui-bg-inverted) text-(--ui-bg) hover:bg-(--ui-bg-inverted)/90 rounded-xl px-5 py-2.5 transition">
                            Read the documentation
                        </a>
                        <a :href="uri([RedirectsController::class, 'discord'])" class="flex items-center gap-x-2 rounded-xl px-4 py-2.5 text-(--ui-text) ring ring-inset ring-(--ui-border) hover:bg-(--ui-bg-elevated) transition">
                            <x-icon name="tabler:brand-discord" class="size-5"/>
                            <span>Join the Discord</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
        <!--
          Sits outside the container, like the hero's glow, so it reaches the screen
          edges instead of being clipped to the content width. Pulled down past the
          end of <main> by the footer's height plus its margin, otherwise the glow
          stops on a hard line exactly where the footer starts.
        -->
        <div class="page-glow z-[-1] absolute inset-x-0 -bottom-14 h-[20rem] sm:h-[36rem] overflow-hidden pointer-events-none"></div>
    </main>

    <script>
        document.addEventListener('scroll', function () {
            const indicator = document.getElementById('scroll-indicator')
            if (!indicator) {
                return
            }

            const scrolled = window.scrollY > 100

            indicator.style.opacity = scrolled ? '0' : '1'
            indicator.style.pointerEvents = scrolled ? 'none' : 'auto'
        })
    </script>
</x-base>
