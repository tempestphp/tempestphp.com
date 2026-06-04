<section class="flex flex-col justify-center mb-20 lg:mb-[7vh] px-6 tracking-tighter">
  <div class="items-center grid grid-cols-1 lg:grid-cols-2">
    <!-- Left -->
    <div class="mr-6 p-2 lg:p-0">
      <div class="max-w-xl text-sans">
      <span class="flex flex-col tracking-tighter text-xl md:text-4xl xl:text-4xl leading-tight text-(--ui-text-toned)">
        {{ $heading }}
      </span>
      <p :foreach="$paragraphs as $paragraph" class="mt-2 md:mt-4 xl:mt-6 text-xl xl:text-xl text-(--ui-text-muted) leading-snug">
        {{ $paragraph }}
      </p>
      </div>
      <div class="mt-8">
        <a :href="$linkUri" class="group no-primary rounded-lg font-medium inline-flex items-center focus:outline-hidden disabled:cursor-not-allowed aria-disabled:cursor-not-allowed disabled:opacity-75 aria-disabled:opacity-75 transition-colors px-3.5 py-2 gap-2.5 ring ring-inset ring-(--ui-border) text-(--ui-text) bg-(--ui-bg) hover:bg-(--ui-bg-elevated) disabled:bg-(--ui-bg) aria-disabled:bg-(--ui-bg) focus-visible:ring-2 focus-visible:ring-(--ui-border-inverted)">
          <div class="relative flex justify-center items-center size-4.5">
            <x-icon :name="$icon" class="absolute inset-0 opacity-100 group-hover:opacity-0 size-full group-hover:scale-80 transition delay-100 group-hover:delay-0"/>
            <x-icon name="tabler:arrow-right" class="absolute inset-0 opacity-0 group-hover:opacity-100 size-full scale-20 group-hover:scale-100 transition -translate-x-1/2 group-hover:translate-x-0 delay-0 group-hover:delay-100"/>
          </div>
          <span class="tracking-wider">{{ $linkLabel }}</span>
        </a>
      </div>
    </div>
    <!-- Right -->
    <div class="flex flex-col gap-2 p-3 rounded-xl text-sm tracking-normal home">
      <div :foreach="$snippets as $snippet" class="home-code-block">
        {!! $this->codeBlocks[$snippet] !!}
      </div>
    </div>
  </div>
</section>
