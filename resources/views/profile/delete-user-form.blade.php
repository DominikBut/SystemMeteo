<x-action-section>
    <x-slot name="title">
        {{ __('Usuń konto') }}
    </x-slot>

    <x-slot name="description">

    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-xs sm:text-sm text-gray-600">
            {{ __('Jeżeli usuniesz konto, wszystkie stacje i dane powiązane z nimi zostaną usunięte!') }}
        </div>

        <div class="mt-5 text-end">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('Usuń konto') }}
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                {{ __('Usuń konto') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Jesteś pewny usunięcia konta? Jeżeli usuniesz konto, wszystkie stacje i dane powiązane z nimi zostaną usunięte! Wpisz aktualne hasło, aby potwierdzić chęć usunięcia konta na zawsze.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="{{ __('Aktualne hasło') }}"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Anuluj') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Usuń konto') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
