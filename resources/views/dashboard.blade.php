<x-guest-layout>
    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <x-welcome />

            </div>
        </div>
    </div> --}}
    <x-slot name="header">
            {{ __('Moje stacje') }}
    </x-slot>

    <div class="py-6">
        <div class="mx-auto sm:px-2 lg:px-8 max-w-[100rem]">
            <div class="overflow-hidden">
                @livewire('personal-stations')
            </div>
        </div>
    </div>
    {{-- <div class="py-8">
        <div class="max-w-screen-2xl mx-auto h-full">
            <div class="overflow-hidden h-full">
                <div class="grid grid-cols-1 md:grid-cols-12 space-y-4 md:space-y-0 md:space-x-4 xl:space-x-8 mx-2">
                    <div class="md:col-span-3 lg:col-span-2 justify-between shadow-sm border rounded-lg bg-white md:min-h-[50rem] p-3 ">

                    </div>
                    <div class="md:col-span-9 lg:col-span-10 min-h-[50rem] bg-white shadow-sm border rounded-lg">
                       @livewire('personal-stations')
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</x-guest-layout>
