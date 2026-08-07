<x-base-code :blank="$blank">
    <div class="top-0 right-0 z-[99] fixed flex gap-2 bg-red p-4" :if="!$clean && !$blank">
        <a
                :href="$blankUrl"
                class="bg-white hover:bg-tempest-blue-500 shadow-xl p-2 border-2 hover:border-white border-transparent rounded-full cursor-pointer">
            <x-icon name="line-md:document"/>
        </a>
        <a
                :href="$cleanUrl"
                class="bg-white hover:bg-tempest-blue-500 shadow-xl p-2 border-2 hover:border-white border-transparent rounded-full cursor-pointer">
            <x-icon name="material-symbols:add-a-photo"/>
        </a>
        <a
                :href="$editUrl"
                class="bg-white hover:bg-tempest-blue-500 shadow-xl p-2 border-2 hover:border-white border-transparent rounded-full cursor-pointer">
            <x-icon name="material-symbols:edit-square-outline"/>
        </a>
    </div>

    <div class="top-0 left-0 absolute flex justify-center items-center w-full h-full">
        <div
                class="bg-(--ui-bg) border-(--ui-border) rounded-md p-8 max-h-[75%] overflow-auto"
                :class="implode([
                    $center ? 'text-center' : '',
                    ($clean || $blank) ?: 'border',
                ])"
                id="screenshot"
        >
            <pre data-lang="<?= $language ?>"><?= $code ?></pre>
        </div>
    </div>

</x-base-code>
