<button toggle-palette class="hidden sm:flex items-center gap-2 min-w-0 cursor-pointer rounded-lg border border-(--ui-border) bg-(--ui-bg)/50 dark:bg-transparent px-3 py-1.5 text-sm text-(--ui-text-muted) hover:bg-(--ui-bg-elevated) hover:border-(--ui-border-accented) transition group-[[data-scrolling]]:bg-(--ui-bg)">
    <label for="search" class="sr-only">Search</label>
    <x-icon name="tabler:search" class="size-4 shrink-0 text-(--ui-text-dimmed)"/>
    <span class="truncate">Search docs, blog...</span>
    <kbd class="ml-2 inline-flex items-center gap-0.5 rounded border border-(--ui-border) bg-(--ui-bg-muted) px-1.5 py-0.5 text-xs text-(--ui-text-dimmed) font-medium">
        <x-icon name="tabler:command" class="size-3"/>
        K
    </kbd>
</button>
