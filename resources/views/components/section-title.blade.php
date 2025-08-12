<div class="md:col-span-1 flex justify-between">
    <div class="px-2 sm:px-0">
        <h3 class="text-sm  sm:text-lg font-bold text-gray-900">{{ $title }}</h3>

        <p class="mt-1 text-xs sm:text-sm text-gray-600">
            {{ $description }}
        </p>
    </div>

    <div class="px-2 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
