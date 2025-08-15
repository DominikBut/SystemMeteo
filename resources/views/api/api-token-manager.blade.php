
<div class="p-4 space-y-4 w-full">
    <div class="flex flex-row align-content-center w-full text-xs sm:text-base">

        <div class="flex flex-col  w-full bg-white rounded-md shadow-sm p-4 border">
            <h1 class="w-full text-center text-sm sm:text-xl font-bold tracking-wider pb-2"> <span class="text-red-500">Jak działa proces przesyłania danych na serwer systemu meteo przez API?</span></h1>
            <div class="bg-slate-50 w-full p-2 sm:pb-0 sm:px-4 sm:pt-4 text-xs sm:text-sm  text-pretty text-start font-medium rounded-md shadow-sm">
                1. Dodaj stację w zakładce  <a class="underline text-blue-500" href="{{ route('api-tokens.index') }}">Moje stacje pogodowe</a>.<br>
                2. Skopiuj 12-cyfrowy numer ID utworzonej stacji pogodowej (np. 111122223333).<br>
                3. Utwórz token API poniżej i skopiuj jego odszyfrowaną treść, lub wykorzystaj utworzony wcześniej. <br>
                4. Zaprogramuj stację pogodową, aby przesyłała żądania <span class="underline text-blue-500">HTTP POST</span> co godzinę, o strukturze zgodnej z poniższą, pod adres <a class="underline text-blue-500">{{ url('') }}/api/data</a><br>
                4. Struktura żądania <span class="underline text-blue-500">HTTP POST</span>: <br>
                <div class="text-gray-700 ps-4 text-xs sm:text-sm flex flex-row flex-wrap ">
                    <div class="w-full">
                        Content-Type: application/json,<br>
                    </div>
                    <div class="flex flex-col sm:flex-row w-full">


                        <div>
                            Headers:<br>
                            <div class="p-2 ">
                                <table class="text-xs border-2 w-min min-w-64 bg-blue-50">
                                    <thead >
                                        <tr class="p-1 border font-bold">
                                            <td class="p-1 border ">
                                                Key
                                            </td>
                                            <td class="p-1 border">
                                                Value
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="p-1 border">
                                            <td class="p-1 border">
                                                Authorization
                                            </td>
                                            <td class="p-1 border">
                                                Bearer {token API}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            Przykład:<br>
                            <div class="p-2 ">

                                <table class="text-xs border-2 w-min min-w-64 bg-blue-50">
                                    <thead>
                                        <tr class="p-1 border font-bold">
                                            <td class="p-1 border">
                                                Key
                                            </td>
                                            <td class="p-1 border">
                                                Value
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="p-1 border">
                                            <td class="p-1 border">
                                                Authorization
                                            </td>
                                            <td class="p-1 border sm:text-nowrap text-clip max-w-32 truncate">
                                                Bearer 8JY018d9bY0RuR30oC5GzkaedEYAIikUg0663jSdbe439aa5
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                        </div>
                    <div class="flex flex-col sm:flex-row w-full">
                        <div>
                            Body:
                            <div class="p-2 ">

                            <table class="text-xs border-2 w-min min-w-64 bg-blue-50">
                                <thead>
                                    <tr class="p-1 border font-bold">
                                        <td class="p-1 border ">
                                            Key
                                        </td>
                                        <td class="p-1 border">
                                            Value
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            station_id
                                        </td>
                                        <td class="p-1 border">
                                            {id stacji}
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            temp_air
                                        </td>
                                        <td class="p-1 border">
                                            {wartość dziesiętna}
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            humidity
                                        </td>
                                        <td class="p-1 border">
                                            {wartość dziesiętna}
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            wind_speed
                                        </td>
                                        <td class="p-1 border">
                                            {wartość dziesiętna}
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            wind_direction
                                        </td>
                                        <td class="p-1 border">
                                            {wartość 0-360}
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            rain_10min
                                        </td>
                                        <td class="p-1 border">
                                            {wartość dziesiętna}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <div>
                            Body:
                            <div class="p-2 ">

                            <table class="text-xs border-2 w-min min-w-64 bg-blue-50">
                                <thead>
                                    <tr class="p-1 border font-bold">
                                        <td class="p-1 border">
                                            Key
                                        </td>
                                        <td class="p-1 border">
                                            Value
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            station_id
                                        </td>
                                        <td class="p-1 border">
                                            111122223333
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            temp_air
                                        </td>
                                        <td class="p-1 border">
                                            19.32
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            humidity
                                        </td>
                                        <td class="p-1 border">
                                            83.56
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            wind_speed
                                        </td>
                                        <td class="p-1 border">
                                            1.2
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            wind_direction
                                        </td>
                                        <td class="p-1 border">
                                            235
                                        </td>
                                    </tr>
                                    <tr class="p-1 border">
                                        <td class="p-1 border">
                                            rain_10min
                                        </td>
                                        <td class="p-1 border">
                                            1.3
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="text-xs">Jeżeli stacja nie posiada urządzeń pomiarowych powiązanych z parametrem można pominąć całkowicie parametr lub przesyłać wartość "null".</div>


            </div>
        </div>
    </div>
    <div class="p-4  sm:p-6 bg-white rounded-md shadow-sm text-sm min-h-100 border">

    <!-- Generate API Token -->
    <x-form-section submit="createApiToken">
        <x-slot name="title">
            {{ __('Utwórz token API ') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Tokeny API dla stacji pogodowych zwiększają bezpieczeńśtwo i pozwalają na autoryzację odbioru i zapisu danych w systemie meteo.') }}
        </x-slot>

        <x-slot name="form">
            <!-- Token Name -->
            <div class="col-span-6 sm:col-span-4">
                <x-label for="name" value="{{ __('Nazwa tokenu') }}" />
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="createApiTokenForm.name" autofocus />
                <x-input-error for="name" class="mt-2" />
            </div>

            {{-- <!-- Token Permissions -->
            @if (Laravel\Jetstream\Jetstream::hasPermissions())
                <div class="col-span-6">
                    <x-label for="permissions" value="{{ __('Permissions') }}" />

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                            <label class="flex items-center">
                                <x-checkbox wire:model="createApiTokenForm.permissions" :value="$permission"/>
                                <span class="ms-2 text-sm text-gray-600">{{ $permission }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif --}}
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3" on="created">
                {{ __('Utworzono') }}
            </x-action-message>

            <x-button>
                {{ __('Utwórz') }}
            </x-button>
        </x-slot>
    </x-form-section>

    @if ($this->user->tokens->isNotEmpty())
        <x-section-border />

        <!-- Manage API Tokens -->
        <div class="mt-10 sm:mt-0">
            <x-action-section>
                <x-slot name="title">
                    {{ __('Zarządzaj tokenami API dla stacji pogodowych') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Możesz usuwać wszystkie utworzone tokeny w dowolnym momencie.') }}
                </x-slot>

                <!-- API Token List -->
                <x-slot name="content" >
                    <div class="">
                        @foreach ($this->user->tokens->sortBy('name') as $token)
                            <div class="flex items-center justify-between p-2 sm:p-4 border">
                                <div class="break-all text-blue-500 font-bold ms-4 text-xs sm:text-sm">
                                    {{ $token->name }}
                                </div>

                                <div class="flex items-center ms-2">
                                    @if ($token->last_used_at)
                                        <div class="text-xs sm:text-sm text-gray-400 pe-1 sm:pe-4">
                                            {{ __('Użyto ostatnio') }} {{ $token->last_used_at->diffForHumans() }}
                                        </div>
                                    @endif

                                    {{-- @if (Laravel\Jetstream\Jetstream::hasPermissions())
                                        <button class="cursor-pointer ms-6 text-sm text-gray-400 underline" wire:click="manageApiTokenPermissions({{ $token->id }})">
                                            {{ __('Permissions') }}
                                        </button>
                                    @endif --}}

                                    <button class="whitespace-nowrap rounded-xl bg-red-600 border border-red-600 px-4 py-2 text-sm font-medium tracking-wide text-white transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed " wire:click="confirmApiTokenDeletion({{ $token->id }})">
                                        {{ __('Usuń') }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot>
            </x-action-section>
        </div>
    @endif

    <!-- Token Value Modal -->
    <x-dialog-modal wire:model.live="displayingToken">
        <x-slot name="title">
            {{ __('API Token') }}
        </x-slot>

        <x-slot name="content">
            <div>
                {{ __('Skopiuj ten token API. Po zamknięciu tego okna dla bezpieczeństwa nie będziesz mógł go ponownie odszyfrować.') }}
            </div>

            <x-input x-ref="plaintextToken" type="text" readonly :value="$plainTextToken"
                class="mt-4 bg-lime-200 px-4 py-2 rounded font-mono font-bold text-sm text-gray-700 w-full break-all"
                autofocus autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                @showing-token-modal.window="setTimeout(() => $refs.plaintextToken.select(), 250)"
            />
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('displayingToken', false)" wire:loading.attr="disabled" >
                {{ __('Zamknij') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    {{-- <!-- API Token Permissions Modal -->
    <x-dialog-modal wire:model.live="managingApiTokenPermissions">
        <x-slot name="title">
            {{ __('API Token Permissions') }}
        </x-slot>

        <x-slot name="content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach (Laravel\Jetstream\Jetstream::$permissions as $permission)
                    <label class="flex items-center">
                        <x-checkbox wire:model="updateApiTokenForm.permissions" :value="$permission"/>
                        <span class="ms-2 text-sm text-gray-600">{{ $permission }}</span>
                    </label>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('managingApiTokenPermissions', false)" wire:loading.attr="disabled">
                {{ __('Anuluj') }}
            </x-secondary-button>

            <x-button class="ms-3" wire:click="updateApiToken" wire:loading.attr="disabled">
                {{ __('Zapisz') }}
            </x-button>
        </x-slot>
    </x-dialog-modal> --}}

    <!-- Delete Token Confirmation Modal -->
    <x-confirmation-modal wire:model.live="confirmingApiTokenDeletion">
        <x-slot name="title">
            {{ __('Usuń token API ') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Jesteś pewny, że chcesz usunąć ten token?') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingApiTokenDeletion')" wire:loading.attr="disabled">
                {{ __('Anuluj') }}
            </x-secondary-button>

            <x-danger-button class="ms-3" wire:click="deleteApiToken" wire:loading.attr="disabled">
                {{ __('Usuń') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
     </div>

</div>

