<x-guest-layout>
    <x-slot name="header">

            {{ __('Moje konto') }}

    </x-slot>
    <div class="py-6">
        <div class="mx-auto sm:px-2 lg:px-8 max-w-[100rem]">
            <div class="overflow-hidden">
                <div class="p-4 space-y-4 ">
                    <div class="flex flex-row align-content-center  text-xs sm:text-base">
                        <div class="p-4  sm:p-6 bg-white rounded-md shadow-sm text-sm min-h-100 border">
                            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                    @livewire('profile.update-profile-information-form')

                                    <x-section-border />
                                @endif

                                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                    <div class="mt-10 sm:mt-0">
                                        @livewire('profile.update-password-form')
                                    </div>

                                    <x-section-border />
                                @endif

                                {{-- @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                    <div class="mt-10 sm:mt-0">
                                        @livewire('profile.two-factor-authentication-form')
                                    </div>

                                    <x-section-border />
                                @endif --}}

                                <div class="mt-10 sm:mt-0">
                                    @livewire('profile.logout-other-browser-sessions-form')
                                </div>

                                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                    <x-section-border />

                                    <div class="mt-10 sm:mt-0">
                                        @livewire('profile.delete-user-form')
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
