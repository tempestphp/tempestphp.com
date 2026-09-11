<?php

// Rendered more than once per page, so the copy target needs its own id. Don't
// take this as a prop: Tempest also forwards an `id` attribute onto the root
// element, which would put the same id on two nodes.
$id = 'install-command-' . substr(md5(uniqid('', true)), 0, 8);

?>

<button
  :data-copy="'#' . $id"
  class="group flex items-center gap-x-3 max-w-full px-4 py-3 font-mono text-sm sm:text-base text-left rounded-xl border border-(--ui-border) bg-(--ui-bg)/60 hover:bg-(--ui-bg) hover:border-(--ui-border-accented) transition cursor-pointer"
>
  <x-icon name="tabler:terminal-2" class="shrink-0 size-5 text-(--ui-primary)"/>
  <span :id="$id" class="min-w-0 overflow-x-auto whitespace-nowrap text-(--ui-text-muted) group-hover:text-(--ui-text) transition">
    composer create-project tempest/app
  </span>
  <span class="relative shrink-0 flex items-center justify-center size-5 text-(--ui-text-dimmed) group-hover:text-(--ui-text) transition">
    <x-icon name="tabler:copy" class="absolute inset-0 size-full group-data-copied:opacity-0 transition"/>
    <x-icon name="tabler:copy-check-filled" class="absolute inset-0 size-full opacity-0 group-data-copied:opacity-100 transition text-(--ui-success)"/>
  </span>
</button>
