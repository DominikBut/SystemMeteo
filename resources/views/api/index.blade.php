<x-guest-layout>
    <x-slot name="header">
            {{ __('Moje tokeny API') }}
    </x-slot>

    <div class="py-6">
        <div class="mx-auto sm:px-2 lg:px-8 max-w-[100rem]">
            <div class="overflow-hidden">
            @livewire('api.api-token-manager')
            </div>
        </div>
    </div>
</x-guest-layout>
