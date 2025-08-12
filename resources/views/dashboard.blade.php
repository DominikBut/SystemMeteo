<x-guest-layout>
    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-welcome />

            </div>
        </div>
    </div> --}}
    <x-slot name="header">
            {{ __('Moje stacje pogodowe') }}
    </x-slot>

    <div class="py-6">
        <div class="mx-auto sm:px-2 lg:px-8 max-w-[100rem]">
            <div class="overflow-hidden">
                @livewire('personal-stations')
            </div>
        </div>
    </div>

</x-guest-layout>
