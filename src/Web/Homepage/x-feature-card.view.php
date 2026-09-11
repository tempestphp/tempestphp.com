<li class="group relative p-0.5 rounded-lg border border-(--ui-border) bg-(--ui-bg-elevated)/30 hover:bg-(--ui-bg-elevated)/75 transition">
  <div class="h-full flex flex-col gap-y-2 p-4 rounded-md border border-dashed border-(--ui-border)">
    <a class="absolute inset-0 rounded-lg" :href="$uri">
      <span class="sr-only">{{ $title }}</span>
    </a>
    <div class="flex items-center gap-x-2">
      <x-icon :name="$icon" class="size-5 text-(--ui-text-dimmed) group-hover:text-(--ui-primary) transition"/>
      <span class="font-medium text-(--ui-text-highlighted)">{{ $title }}</span>
    </div>
    <p class="text-(--ui-text-muted) leading-snug">{{ $description }}</p>
  </div>
</li>
