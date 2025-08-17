<div>
    @php
         use Carbon\Carbon;
    @endphp
    <x-slot name="header">
            {{ __('Przeglądasz dane meteorologiczne stacji pogodowych społeczności') }}
    </x-slot>
    <div class="p-4 space-y-4 w-full">
        <div class="flex flex-row align-content-center w-full text-xs sm:text-base">
            <div x-data="stationSelect({
                        initialId: @entangle('stationId'),
                        stations: @js($this->stations['names']),
                        ownedMap: @js($this->stations['owned']),

                    })"
                    class="flex flex-col md:grid  md:grid-cols-2 w-full">
                    <div class="flex flex-col justify-between p-2">
                         @if (!empty($stations['names']) && (count($stations['names'])!=0))
                        <div>
                            {{-- {{ dd($stations['names']) }} --}}
                            {{-- Checkbox to filter only owned --}}


                            <p class="p-1 font-bold text-gray-600">
                                Wybierz jedną z
                                <span
                                    x-text="onlyOwned
                                        ? `{{ collect($stations['owned'])->filter()->count() }} twoich stacji:`
                                        : `{{ count($stations['names']) }} stacji założonych przez społeczność:`">
                                </span>

                                </p>
                            <div class="relative w-full">
                                <input  wire:loading.attr="disabled" wire:target="loadData"
                                    type="search"
                                    class="bg-white rounded-md shadow-sm border-2 border-gray-300 p-2 w-full"
                                    {{-- :value="stations[selectedId] ? `${stations[selectedId]}` : (selectedId ?? '')" --}}
                                    @input="query = $event.target.value"
                                    x-model="query"
                                    {{-- to wyzej powoduje ze jak wpisuje to szuka a jak dostaje z url to nie szuka auto jak jest zle dodatkowy czek 2 kroki zamiast 1 --}}
                                    @focus="open = true"
                                    @blur="setTimeout(() => open = false, 200)"
                                    placeholder="Wpisz ID lub nazwę stacji...">

                                <ul x-cloak x-show="open" class="border rounded border-gray-400 mt-1 bg-white max-h-60 overflow-y-auto shadow z-10 absolute w-full">
                                    <template x-for="[id, name] in filtered()" :key="id">
                                        <li
                                            class="px-4 py-2 hover:bg-gray-200 cursor-pointer"
                                            @click="select(id)"
                                            x-text="`${id} – ${name}`"
                                        ></li>
                                    </template>
                                    <li x-cloak class="px-4 py-2 text-sm text-gray-500 border-t-2  border-gray-200 bg-gray-50 sticky bottom-0 z-10">
                                        Dostępnych opcji: <span x-text="filtered().length"></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="flex flex-row  ms-2 text-xs truncate">
                                    @if (Auth::id())
                                    <label class="my-2 text-xs w-min ps-1 pe-2 font-bold text-blue-500 text-nowrap border-e-2 border-gray-500 content-center">
                                        <input type="checkbox" x-model="onlyOwned" class="form-checkbox">
                                        <span class="ps-1">Tylko moje stacje</span>
                                    </label>

                                    @endif
                                    <p x-cloak class="my-2  w-min ms-2 font-bold text-lime-600 truncate" x-show="selectedId && stations[selectedId]">
                                        Wybrana stacja ID: <span class="truncate" x-text="`${selectedId} – ${stations[selectedId]}`"></span>
                                    </p>
                                    <p x-cloak x-show="selectedId && !stations[selectedId]" class=" font-bold text-red-500 my-2  ms-2 w-auto text-wrap">
                                        Nieprawidłowa stacja (ID: {{ $stationId }})  –<span class="text-nowrap"> Brak wśród stacji społeczności.</span>
                                    </p>
                                    <p  x-show="!selectedId && !stations[selectedId]" class="font-bold text-lime-600 my-2  w-min ms-2 ">
                                        Wybierz najpierw stację!
                                    </p>

                            </div>
                        </div>
                        @else
                            <h1 class="w-full bg-white rounded-md shadow-sm text-sm text-red-500 p-4 mb-2 text-center"><b>Brak dostępnych stacji w bazie danych</b></h1>
                        @endif
                        <div class="w-full">
                            <div class="w-full bg-white rounded-md shadow-sm sm:min-h-56 p-4 flex flex-col justify-between">
                                <div class="w-full">
                                    <p class=""><b>Wybierz zakres odczytu danych:</b></p>
                                </div>
                                        <div  class="flex flex-row flex-wrap gap-2 px-2 py-3 ">
                                            <button wire:click="$set('dateOption', 'today'); $wire.loadData()" wire:loading.attr="disabled" wire:target="loadData"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class=" {{ $dateOption === 'today' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                Dzisiaj
                                            </button>

                                            <button wire:click="$set('dateOption', 'yesterday'); $wire.loadData()" wire:loading.attr="disabled" wire:target="loadData"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class=" {{ $dateOption === 'yesterday' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                Wczoraj
                                            </button>

                                            <button wire:click="$set('dateOption', '7days'); $wire.loadData()" wire:loading.attr="disabled" wire:target="loadData"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class="text-nowrap  {{ $dateOption === '7days' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                7 ostatnich dni
                                            </button>
                                        </div>
                                                <div class="flex flex-col px-2 sm:pt-3 py-3 sm:pb-1 border rounded-md">
                                                     {{-- minDate below set to earliest data that i have --}}
                                                     <div class="flex flex-col md:flex-row w-full order-2 sm:order-none" x-data="{
                                                            start: $wire.entangle('terminoweStartDate'),
                                                            end: $wire.entangle('terminoweEndDate'),
                                                            endMax: '',
                                                            endMin: '',
                                                            minDate: '2025-07-24',
                                                            maxDate: '',

                                                            validateRange() {
                                                                const today = new Date();
                                                                const yesterday = new Date(today);
                                                                const yesterdayStr = yesterday.toLocaleDateString('sv-SE');

                                                                // Set global max date to yesterday
                                                                this.maxDate = yesterdayStr;

                                                                // Clamp start date
                                                                if (this.start < this.minDate) this.start = this.minDate;
                                                                if (this.start > this.maxDate) this.start = this.maxDate;

                                                                // Set endMin to be at least the start date
                                                                this.endMin = this.start;

                                                                // Clamp end to start if invalid
                                                                if (this.start > this.end) this.end = this.start;

                                                                // Enforce same year & month
                                                                const s = new Date(this.start);
                                                                const e = new Date(this.end);

                                                                if (s.getFullYear() !== e.getFullYear() || s.getMonth() !== e.getMonth()) {
                                                                    this.end = this.start;
                                                                }

                                                                // Calculate last valid end date: end of month or yesterday if same month
                                                                const lastDayOfMonth = new Date(s.getFullYear(), s.getMonth() + 1, 0);
                                                                let effectiveEnd = lastDayOfMonth;

                                                                if (
                                                                    s.getFullYear() === today.getFullYear() &&
                                                                    s.getMonth() === today.getMonth()
                                                                ) {
                                                                    // Clamp to yesterday only if it's current month
                                                                    effectiveEnd = yesterday < lastDayOfMonth ? yesterday : lastDayOfMonth;
                                                                }

                                                                this.endMax = effectiveEnd.getFullYear() + '-' +String(effectiveEnd.getMonth() + 1).padStart(2, '0') + '-' + String(effectiveEnd.getDate()).padStart(2, '0')

                                                                // Clamp end value
                                                                if (this.end < this.endMin) this.end = this.endMin;
                                                                if (this.end > this.endMax) this.end = this.endMax;
                                                            }
                                                        }"
                                                        x-init="validateRange()">
                                                        <div class="flex flex-col sm:flex-row flex-wrap  items-end gap-4 w-full ">
                                                            <div class="flex flex-col w-full">
                                                                <label for="start" class="sm:text-sm font-medium text-gray-700">Data początkowa:</label>
                                                                <input type="date" id="start" wire:loading.attr="disabled" wire:target="loadData"
                                                                    x-model="start" x-on:change="validateRange()"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="w-full border border-gray-300 px-2 py-1 rounded" />
                                                            </div>

                                                            <div class="flex flex-col w-full">
                                                                <label for="end" class="sm:text-sm font-medium text-gray-700">Dzień końcowy:</label>
                                                                <input type="date" id="end" wire:loading.attr="disabled" wire:target="loadData"
                                                                    x-model="end" x-on:change="validateRange()"
                                                                    :min="endMin"
                                                                    :max="endMax"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="w-full border border-gray-300 px-2 py-1 rounded" />
                                                            </div>
                                                            <button wire:loading.attr="disabled" wire:target="loadData"
                                                            wire:click="$set('dateOption', 'interval'); $wire.loadData()"
                                                            class="whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                        >
                                                            Wczytaj zakres
                                                        </button>

                                                        </div>
                                                    </div>
                                                      <p class="sm:pt-1 sm:ps-2 pb-2 sm:pb-0 text-gray-500 text-xs order-1 sm:order-none">Zakres musi mieścić się w tym zamym miesiącu.</p>

                                                </div>




                            </div>
                        </div>
                    </div>
                    <div class="p-2 flex flex-col justify-end">

                        @if (!empty($stationData) && !is_null($stationData))

                            <div class=" p-4 bg-white rounded-md shadow-sm sm:text-sm min-h-72">
                                <p class="font-bold  text-sm sm:text-base text-gray-700 ">
                                    Najnowsze dane meteo z dnia dzisiejszgo  dla stacji ID: <span class="text-nowrap text-lime-600">{{ $stationId.' - '.$stations['names'][$stationId] }}</span>
                                </p>
                                <p class="text-xs text-gray-500 pt-1">
                                    Dane pobrano: {{ $askTime ?? '–' }}
                                </p>

                                <ul class="grid grid-cols-2 gap-4 sm:text-sm pt-4">
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Temp. powietrza:</strong> {{ $stationData->temp_air ?? '-' }} °C
                                        </p>

                                    </li>

                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wilg. względna:</strong> {{ $stationData->humidity ?? '-' }} %
                                        </p>

                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 12h16M4 6h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr śr.:</strong> {{ $stationData->wind_speed ?? '-' }} m/s
                                        </p>
                                    </li>

                                    <li>
                                        @php
                                            $rotation = is_numeric($stationData->wind_direction) ? $stationData->wind_direction : 0;
                                        @endphp
                                        <div class="flex flex-row items-start sm:items-center gap-1">
                                            <div class=" w-min sm:w-auto">
                                                <strong class="flex items-center gap-1 w-min sm:w-auto">
                                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                    viewBox="0 0 24 24">
                                                    <path d="M12 3v18M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                    Kierunek wiatru:
                                                </strong>
                                            </div>
                                            <div class="flex items-end sm:items-center gap-1">
                                                 <div class="inline-block transform font-extrabold px-1" style="rotate: {{ $rotation+90 }}deg;">
                                                    ➤
                                                </div>
                                                {{ $stationData->wind_direction ?? '-' }} °
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4v16h16V4H4zM9 9h6v6H9z" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Opad <span class="text-nowrap">(10 min):</span></strong> {{ $stationData->rain_10min ?? '-' }} mm
                                        </p>

                                    </li>
                                    <p class="text-sm text-lime-700 font-medium ps-4 content-center">
                                    Dane zapisano: <span class="text-nowrap">{{ !empty($stationData->created_at) ? Carbon::parse($stationData->created_at)->format('Y-m-d H:i:s') : 'Brak pomiaru' }}</span>
                                </p>
                                </ul>
                            </div>
                        @elseif(!empty($error))
                            <div class="relative p-4 bg-white rounded-md shadow-sm text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center p-4">
                                    <div class="text-sm font-bold text-red-500 text-balance">Wśród stacji społeczności nie znaleziono publicznych danych dla stacji o wybranym ID.</div>
                                    <div class="font-bold text-gray-600 text-sm mt-2">
                                        Wybierz inną stację, spróbuj innej z tego samego regionu lub zaloguj się jeżeli to twoja stacja.
                                    </div>
                                    <br>
                                    @if ($error)
                                        <p class="font-bold text-red-500">{{ $error }}</p>
                                    @endif
                                </div>
                            </div>
                        @elseif(!empty($stationId))
                            <div class="relative p-4 bg-white rounded-md shadow-sm text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center">
                                    <p class="w-full h-auto m-auto px-4 text-lg text-red-500 font-medium">Brak danych dla tej stacji z dnia dzisiejszego</p>
                                </div>
                            </div>
                        @else
                            <div class="relative p-4 bg-white rounded-md shadow-sm text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center">
                                    <p class="w-full h-auto m-auto animate-pulse">Oczekiwanie na wybór stacji...</p>
                                </div>
                            </div>
                        @endif
                    </div>
            </div>
        </div>

        <div class=" m-2 p-4 bg-white rounded-md shadow-sm min-h-80 h-auto">
                            @if (!empty($stations['names'][$stationId]) )
                                <div wire:loading.remove wire:target.except="loadData, setSort">
                                    <div class="flex flex-col sm:flex-row  justify-between w-full">
                                        <div class="truncate font-medium text-xs sm:text-sm   w-auto flex flex-col justify-between">
                                            <div class="flex flex-row font-bold  text-black text-base sm:text-xl pb-2 text-center sm:text-start w-full">
                                                <div class="flex flex-col w-full">Informacje o stacji pogodowej
                                                    <div class="flex flex-row w-full justify-center sm:justify-start sm:ps-2">
                                                        użytkownika&nbsp
                                                        <div class="w-min font-medium text-nowrap text-lime-600">
                                                            @php
                                                                $owner = App\Models\User::where('id',$stationInfo->user_id)->select('name','profile_photo_url')->first()
                                                            @endphp

                                                        {{ $owner->name }}
                                                        </div>
                                                    :
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ps-3 truncate font-medium text-xs sm:text-sm   w-auto flex flex-col gap-2">
                                                <div>ID: <span class="text-nowrap font-medium text-lime-600 ">{{ $stationId }} </span></div>
                                                <div>Nazwa: <span class="text-nowrap font-medium text-lime-600">{{ $stations['names'][$stationId] }} </span></div>
                                                <div>
                                                    <div>Lokalizacja:</div>
                                                    <div class="flex flex-row items-center">

                                                        <a title="Sprawdź na mapie!"href="{{ route('map_community') . '?id=' . $stationId }}" class="text-blue-500 hover:text-blue-900 transition duration-100 hover:font-bold underline size-7 flex flex-row justify-center items-center content-center" title="Sprawdź na mapie">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 hover:font-bold hover:size-7 ">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                        </svg>

                                                        </a>
                                                        <div>
                                                            <div class="ps-1 text-gray-500">Województwo: <span class="text-nowrap font-medium text-lime-600">{{ Str::ucfirst($this->stationInfo->voivodeship)}}</span></div>
                                                            <div class="ps-1 text-gray-500">Powiat grodzki/miejski: <span class=" font-medium text-nowrap text-lime-600 ">{{ Str::ucfirst($this->stationInfo->district) }}</span></div>

                                                        </div>

                                                    </div>
                                                   </div>
                                                @if ($this->stationInfo->description != null)
                                                <div> Opis stacji: <div class="font-medium text-lime-600 max-w-100 text-wrap ps-1">{{Str::ucfirst( $this->stationInfo->description)  }}</div></div>
                                                @endif
                                                @php
                                                $measurements = [
                                                    'temperature' => 'Temp. powietrza',
                                                    'humidity' => 'Wilg. względna',
                                                    'wind' => 'Wiatr',
                                                    'rain' => 'Opad 10 min',
                                                ];
                                                @endphp
                                                </div>
                                                 <div class="ps-3 truncate font-medium text-xs sm:text-sm   w-auto flex flex-col">
                                                    <div class="pt-2">
                                                    Aktualnie wykonywane pomiary:
                                                    </div>
                                                    <div class="flex flex-row flex-wrap gap-2 items-center pt-1">
                                                        @foreach ($measurements as $field => $label)
                                                            @if ($this->stationInfo->$field == true)
                                                                <span class="w-fit inline-flex overflow-hidden rounded-xl border border-lime-600 bg-white text-xs font-normal sm:font-medium text-lime-600">
                                                                    <span class="flex items-center gap-1 bg-lime-600/10 px-1 sm:px-2 sm:py-1">

                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                        </svg>

                                                                        {{ $label }}
                                                                    </span>
                                                                </span>
                                                            @else
                                                                <span class="w-fit inline-flex overflow-hidden rounded-xl border font-normal sm:font-medium border-gray-700 bg-white text-xs text-gray-700">
                                                                    <span class="flex items-center gap-1 bg-indigo-700/10 px-1 sm:px-2 sm:py-1">

                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                                        </svg>

                                                                        {{ $label }}
                                                                    </span>
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>


                                        </div>
                                        <div class="flex flex-col items-end ">
                                            <div class=" flex flex-col items-center w-full sm:items-end pb-1 pe-1">


                                                @if ($this->stationInfo->active === false)
                                                    <p class="text-sm sm:text-base text-red-500 font-bold">Stacja nieaktywna</p>
                                                @else
                                                    <p class="text-sm sm:text-base text-lime-500 font-bold">Stacja aktywna</p>
                                                @endif
                                                <p class="text-xs text-gray-500  w-auto ">
                                                Ostatni pomiar: {{ App\Models\Data::where('station_id',$stationId)->select('created_at')->orderBy('created_at', 'desc')->first()->created_at ?? '–' }}
                                                </p>
                                            </div>
                                            @if(Storage::url($stationInfo->photo))
                                             <div class="flex bg-gray-100 border-2 border-gray-500 rounded-md m-auto size-64 sm:size-72">
                                                <img class="size-64 sm:size-72 rounded-sm object-cover " src="{{ Storage::url($stationInfo->photo) }}" alt="{{ $stationInfo->name }}" title="Zdjęcie stacji"/>
                                             </div>

                                             @else
                                             <div class="flex flex-col items-center font-medium bg-gray-100 text-gray-500 justify-center border-2 border-gray-300 rounded-md m-auto size-64 sm:size-72">
                                                <p>Brak zdjęcia stacji</p>
                                             </div>
                                             @endif

                                        </div>

                                    </div>


                                </div>
                            @elseif(!empty($error))
                                <div wire:loading.remove wire:target.except="loadData, setSort"  class="min-h-80 items-center w-full flex flex-col justify-center text-center ">
                                    <div class=" font-bold  w-full flex flex-col justify-center">
                                        <div class="text-sm font-bold text-red-500">Wśród stacji społeczności nie znaleziono publicznych danych dla stacji o wybranym ID.</div>
                                        <div class="font-bold text-gray-600 text-sm mt-2">
                                            Wybierz inną stację, spróbuj innej z tego samego regionu lub zaloguj się jeżeli to twoja stacja.

                                        </div>
                                        <br>
                                        @if ($error)
                                            <p class="font-bold text-red-500">{{ $error }}</p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div wire:loading.remove wire:target.except="loadData, setSort"  class="min-h-80 items-center  w-full flex flex-col justify-center text-center ">
                                    <div class="font-bold w-full flex flex-col items-center justify-center">
                                        <p class="w-full  m-auto animate-pulse">Oczekiwanie na wybór stacji...</p>
                                    </div>
                                </div>
                            @endif
                           <div wire:loading wire:target.except="loadData, setSort"  class="min-h-80 w-full flex flex-col justify-center items-center  ">
                                    <div class="font-bold w-full flex flex-col justify-center items-center h-full min-h-64">
                                        <p class=" animate-pulse">Pobieranie danych...</p>
                                    </div>
                            </div>

                        </div>
        <div class=" m-2 p-4 bg-white rounded-md shadow-sm">
            @if (!empty($stations['names'][$stationId]) )
                <h1 class="text-sm sm:text-xl pb-2 font-bold">Wyszukano dane meteorologiczne dla stacji: <span class="text-lime-600">{{ $stationId.' - '.$stations['names'][$stationId] }}</span></h1>
                <div wire:loading.remove wire:target.except="setSort" class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                    <span>Wyświetlono na wykresie dane z okresu:
                        <b class="text-nowrap">
                            @switch($this->dateOption)
                                    @case('today')
                                        {{ '['. Carbon::today()->setTimezone('Europe/Warsaw')->format('Y-m-d') .']' }}
                                        @break
                                    @case('yesterday')
                                        {{ '['. Carbon::yesterday()->setTimezone('Europe/Warsaw')->format('Y-m-d') .']' }}
                                        @break
                                    @case('7days')
                                        {{ '['. Carbon::yesterday()->subDays(6)->setTimezone('Europe/Warsaw')->format('Y-m-d') .'] - ['.Carbon::yesterday()->setTimezone('Europe/Warsaw')->format('Y-m-d').']' }}
                                        @break
                                    @case('interval')
                                        {{ '['.$this->terminoweStartDate .'] - ['.  $this->terminoweEndDate .']' }}
                                        @break
                            @default
                        @endswitch
                        </b>
                    </span>

                </div>
                <div wire:loading wire:target.except="setSort" class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                        Ładowanie...
                </div>
            @else
                <h1 class="text-sm sm:text-xl pb-2 font-bold text-white">Brak wybranej stacji</h1>
                <div class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                    Oczekiwanie...
                </div>
            @endif
            @php
                $allNull = collect($weatherData)->every(fn($item) =>
                    collect($item)->except(['station_id', 'id'])
                                ->filter(fn($val) => !is_null($val))
                                ->isEmpty()
                );
            @endphp
            @if(empty($weatherData) || $allNull)

                <div class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">
                        <div wire:loading.remove wire:target.except="setSort" class="absolute left-0 top-0 w-full h-full flex flex-col justify-center text-center ">
                            @if (!empty($stations['names'][$stationId]))
                                <p class="text-sm sm:text-xl font-bold text-red-500">Brak aktualnych danych z tego okresu dla stacji {{ $stationId.' - '.$stations['names'][$stationId] }}</p>
                            @else
                                 <p class="text-sm sm:text-xl font-bold animate-pulse">Oczekiwanie na wybór stacji...</p>
                            @endif
                        </div>
                    <div wire:loading wire:target.except="setSort" class="absolute top-0 left-0 w-full h-full z-20 animate-pulse border">
                        <div class="w-full h-full flex flex-col justify-center animate-pulse text-center">
                            <p class="text-sm sm:text-xl font-bold animate-pulse">Ładowanie...</p>
                        </div>
                    </div>
                </div>
                 <div>
                <div  class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-white flex flex-row justify-between">
                Brak danych...
                </div>
                <div class="h-44 text-xs bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto ">

                </div>
            </div>
            <div class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-white flex flex-row justify-between">
                Brak danych...
            </div>
            <div class="bg-white rounded-md shadow-sm border border-gray-300 min-h-32 w-full">

            </div>
            @else
            <div id="chart" class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">

                <canvas wire:ignore id="weatherChart"  wire:target.except="setSort" class=" w-full h-full z-0 ">
                </canvas>
                <button  wire:target.except="setSort" id="fullscr" onclick="toggleFullscreen()" style="line-height: 0.5rem; font-size: 0.6rem"
                        class="absolute top-1 right-1  z-10  whitespace-nowrap rounded-xl bg-blue-600 border px-2 py-2 text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                        Pełny ekran
                </button>
            </div>
            <div>
                <div wire:loading wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Ładowanie...
                </div>
                <div wire:loading.remove wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Statystyki odczytanych danych:
                </div>
                <div class="h-44 text-xs bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto ">
                    <table wire:loading.remove wire:target.except="setSort" class="w-full text-left h-full">
                         <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. <span class="text-nowrap">powietrza [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Wilg. <span class="text-nowrap">względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Opad <span class="text-nowrap">10 min [mm]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>

                                </thead>
                                <tbody class="divide-y divide-slate-100  text-xs h-full">
                                    <tr class="bg-red-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MAKS.</td>
                                        <td>{{ $minMaxStats['temp_air']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['humidity']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['rain_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wind_speed']['max'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['temp_air']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['humidity']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['rain_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wind_speed']['avg'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['temp_air']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['humidity']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['rain_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wind_speed']['min'] ?? '-' }}</td>
                                    </tr>
                                </tbody>
                    </table>
                </div>
            </div>
            <div wire:loading.remove wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Zestawienie tabelaryczne danych meteorologicznych stacji:
            </div>
            <div wire:loading wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Ładowanie...
            </div>
            <div wire:loading wire:target.except="setSort" class="bg-white rounded-md shadow-sm border border-gray-300 min-h-32 w-full">

            </div>
            <div wire:loading.remove wire:target.except="setSort" class="min-h-32">

                               <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'created_at' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('created_at')">
                                                        <div class="flex flex-col">Data wykonania <span class="text-nowrap">pomiarów
                                                        @if($sortBy === 'created_at')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temp_air' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temp_air')">
                                                        <div class="flex flex-col">Temp. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temp_air')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'humidity' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('humidity')">
                                                        <div class="flex flex-col">Wilg. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'humidity')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>

                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer transition hover:opacity-75 {{$sortBy === 'rain_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('rain_10min')">
                                                        <div class="flex flex-col">Opad <span class="text-nowrap">10 min [mm]
                                                        @if($sortBy === 'rain_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                     <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wind_speed' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wind_speed')">
                                                        <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wind_speed')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 text-gray-600">
                                                        <div class="flex flex-col">Wiatr <span class="text-nowrap">kierunek [°]
                                                        </span></div>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">

                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'created_at' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['created_at']) ? Carbon::parse($data['created_at'])->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temp_air' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temp_air'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'humidity' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['humidity'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'rain_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['rain_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wind_speed' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wind_speed'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wind_direction' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['wind_direction']))
                                                                @php
                                                                    $rotation = is_numeric($data['wind_direction']) ? $data['wind_direction'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation+90 }}deg;">
                                                                        ➤
                                                                    </div>
                                                                </span>
                                                                {{ $data['wind_direction'] ?? '-' }}
                                                            @else
                                                                <span>{{ $data['wind_direction'] ?? '-' }}</span>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
            </div>

        @endif
        </div>
    </div>

    <script>
        //chart fullscreen
        function toggleFullscreen(elem) {
            elem = elem || document.getElementById('chart');
            if (!document.fullscreenElement && !document.mozFullScreenElement &&
                !document.webkitFullscreenElement && !document.msFullscreenElement) {
                if (elem.requestFullscreen) {
                elem.requestFullscreen();}
                if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();}
                if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();}
                if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                }
            } else {
                if (document.exitFullscreen) {
                document.exitFullscreen();}
                if (document.msExitFullscreen) {
                document.msExitFullscreen();}
                if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();}
                if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
                }
            }
        }
        //chart title label for times
        function getLabelFromContext(type, ctx) {

            const formatDate = (date) => date.toLocaleDateString('sv-SE'); // yyyy-mm-dd
            if (!ctx || !type) return '';

                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);

                    switch (type) {
                        case 'today':
                            return `[${formatDate(today)}]`;
                        case 'yesterday':
                            return `[${formatDate(yesterday)}]`;
                        case '7days': {
                            const sevenDaysAgo = new Date(yesterday);
                            sevenDaysAgo.setDate(yesterday.getDate() - 6);
                            return `[${formatDate(sevenDaysAgo)}] - [${formatDate(yesterday)}]`;
                        }
                        case 'interval':
                            return `[${ctx.terminoweStartDate}] - [${ctx.terminoweEndDate}]`;
                        default:
                            return '';
                    }

        }
        //parse date for chart to without year
        function parseUtcDAY(dateStr) {
            // dateStr format: "2025-08-12T23:12:41.000000Z"
            const d = new Date(dateStr); // JS converts UTC to local
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const hour = String(d.getHours()).padStart(2, '0');
            const minute = String(d.getMinutes()).padStart(2, '0');

            return `${day}.${month}, ${hour}:${minute}`;
        }

        document.addEventListener('livewire:init', () => {

        var chartInstance = null;
        if (chartInstance) {
                chartInstance.clear();
                chartInstance.destroy(); // Clear existing chart
            }

        function renderChart(weatherData, type, name) {
            if (chartInstance) {
                chartInstance.clear();
                chartInstance.destroy(); // Clear existing chart
            }
            let axisLabel = 'Data zapisu';
            const tmplabels = weatherData.map(item => {

                     return item.created_at ? parseUtcDAY(item.created_at) : null;
            });

            let titleLabel = 'Dane meteorologiczne stacji ' + name + ' ' + titlelabel;
            let tmpPowAxisLabel = 'Temp. powietrza [°C]';
            let humAxisLabel = 'Wilg. względna [%]';
            let rainAxisLabel = 'Opad 10 min - suma [mm]';
            let meanWindAxisLabel = 'Wiatr - śr.prędkość [m/s]';

            let datasetsM = [];

            const Powtemperatures = weatherData.map(item => parseFloat(item.temp_air ) || null);
            const humidities = weatherData.map(item => parseFloat(item.humidity ) || null);
            const rain10s = weatherData.map(item => parseFloat(item.rain_10min ) || null);
            const meanWind = weatherData.map(item => parseFloat(item.wind_speed ) || null);

             datasetsM.push(
                                    {
                                        label: tmpPowAxisLabel,
                                        data: Powtemperatures,
                                        borderColor: 'rgb(242, 135, 41)',
                                        backgroundColor: 'rgb(242, 135, 41, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 1,
                                        yAxisID: 'y', // ← attach to left axis
                                    },
                                    {
                                        label: humAxisLabel,
                                        data: humidities,
                                        borderColor: 'rgb(26, 199, 149)',
                                        backgroundColor: 'rgb(26, 199, 149, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y1', // ← attach to right axis
                                        order: 2,
                                    },
                                    {
                                        label: rainAxisLabel,
                                        data: rain10s,
                                        type: 'bar',
                                        stack: 'combined',
                                        // borderDash: [2, 2],
                                        // pointStyle: 'circle',
                                        // pointRadius: 3,
                                        // pointHoverRadius: 4,
                                        borderColor: 'rgb(10, 90, 280)',
                                        backgroundColor: 'rgb(143, 175, 235)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y2', // ← attach to right axis
                                        order: 4,
                                    },
                                    {
                                        label: meanWindAxisLabel,
                                        data: meanWind,
                                        borderColor: 'rgb(189, 185, 175)',
                                        backgroundColor: 'rgb(189, 185, 175, 0.5)',
                                        borderWidth: 1,
                                        pointRadius: 1,
                                        pointHoverRadius: 2,
                                        tension: 0.1,
                                        spanGaps: false,
                                        pointStyle: 'circle',
                                        yAxisID: 'y3', // ← attach to right axis
                                        order: 5,
                                        hidden: true,
                                    },

            );


            const ctx = document.getElementById('weatherChart');
            const plugin = {
                id: 'customCanvasBackgroundColor',
                beforeDraw: (chart, args, options) => {
                    const {ctx} = chart;
                    ctx.save();
                    ctx.globalCompositeOperation = 'destination-over';
                    ctx.fillStyle = options.color || '#99ffff';
                    ctx.fillRect(0, 0, chart.width, chart.height);
                    ctx.restore();
                }
                };
            if (!ctx) {
                    console.error('Brak canvasu!');
                    return;
            }
            Chart.defaults.plugins.legend.position = 'bottom';

                chartInstance = new Chart(ctx, {
                                type: 'line',
                                plugins: [plugin],
                                data: {
                                    labels: tmplabels,
                                    datasets: datasetsM,
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                plugins: {
                                    customCanvasBackgroundColor: {
                                        color: '#FFF',
                                    },
                                    title: {
                                        display: true,
                                        text: titleLabel,
                                        font: {weight: 'normal'},
                                        padding: {
                                            top: 5,
                                            bottom: 10,
                                        }
                                    },
                                },
                                    // scales: {
                                    //     x: { title: { display: true, text: axisLabel }},
                                    //     y: { title: { display: true, text: '' }}
                                    // },
                                    scales: {
                                        x: {
                                            title: {
                                                display: true,
                                                text: axisLabel
                                            }
                                        },
                                        y: {
                                            type: 'linear',
                                            display: true,
                                            position: 'left',
                                            suggestedMin: 0,
                                            suggestedMax: 40,
                                            title: {
                                                display: true,
                                                text: 'Temperatura [°C]',
                                                color: 'rgb(252, 198, 3)',
                                            },
                                            grid: {
                                                drawOnChartArea: true // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y1: {
                                            type: 'linear',
                                            display: true,
                                            position: 'left',
                                            min: 0,
                                            max: 100,
                                            title: {
                                                display: true,
                                                text: 'Wilgotność względna [%]',
                                                color: 'rgb(26, 199, 149)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y2: {
                                            type: 'linear',
                                            display: true,
                                            position: 'right',
                                            min: 0,
                                            suggestedMax: 1,
                                            title: {
                                                display: true,
                                                text: 'Opad 10 min [mm]',
                                                color: 'rgb(10, 90, 280)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y3: {
                                            type: 'linear',
                                            display: true,
                                            position: 'right',
                                            min: 0,
                                            suggestedMax: 10,
                                            title: {
                                                display: true,
                                                text: 'Wiatr [m/s]',
                                                color: 'rgb(51, 51, 51)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                    },

                                },

                });

            console.log('Odświeżono wykres');

        }
        var titlelabel = '';

        Livewire.on('weatherDataUpdated', (newData) => {
            Alpine.nextTick(() => {
                const weatherData = newData[0][0];
                const type = newData[0][1];
                const name = newData[0][2];

                if (Array.isArray(weatherData) && weatherData.length > 0) {

                    //  Generate label using fresh values
                    titlelabel = getLabelFromContext(type, newData[1]);
                    //console.log(weatherData);
                    renderChart(weatherData, type, name);
                } else {
                    console.log('Nie ładuję wykresu – Brak danych');
                }
            });
        });

    });
    </script>
</div>
