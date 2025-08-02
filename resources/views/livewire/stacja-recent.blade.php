<div>
    @php
         use Carbon\Carbon;
    @endphp
    <div class="p-4 space-y-4 w-full">
        <div class="flex flex-row align-content-center w-full text-xs sm:text-base">
            <div x-data="stationSelect({
                        initialId: @entangle('stationId'),
                        stations: {{ Js::from($this->stations) }},
                    })"
                    class="flex flex-col md:grid  md:grid-cols-2 w-full">
                    <h1 class="col-span-2 bg-white rounded-md shadow-sm py-4 px-2 mx-2 text-center text-sm sm:text-2xl font-bold tracking-wider">Przeglądasz bieżące dane meteorologiczne IMGW API</h1>
                    <div class="flex flex-col justify-between p-2">
                         @if (!empty($stations))
                        <div>
                            <p class="p-1 font-bold text-gray-600">Wybierz jedną z {{ count($stations) }} oficjalnych stacji IMGW:</p>
                            <div class="relative w-full">
                                <input
                                    type="search"
                                    class="bg-white rounded-md shadow-sm border-2 border-gray-300 p-2 w-full"
                                    :value="stations[selectedId] ? `${stations[selectedId]}` : (selectedId ?? '')"
                                    @input="query = $event.target.value"
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

                            <div class="flex flex-row ms-2 text-xs">
                                <div>
                                    <p x-cloak class="my-2  w-auto ms-2 font-bold text-lime-600" x-show="selectedId && stations[selectedId]">
                                        Wybrana stacja ID: <span x-text="`${selectedId} – ${stations[selectedId]}`"></span>
                                    </p>
                                    <p x-cloak x-show="selectedId && !stations[selectedId]" class=" font-bold text-red-500 my-2 w-auto">
                                        ❌ Nieprawidłowa stacja (ID: {{ $stationId }}) – brak wśród oficjalnych stacji IMGW.
                                    </p>
                                    <p  x-show="!selectedId && !stations[selectedId]" class="font-bold text-lime-600 my-2 w-auto">
                                        Wybierz najpierw stację!.
                                    </p>
                                </div>
                            </div>
                        </div>
                        @else
                            <h1><b>Błąd połączenia z API spróbuj ponownie za godzinę, aby móc przeglądać dane (również innych stacji)!</b></h1>
                        @endif
                        <div class="w-full">
                            <div
                                x-data="{
                                    selectedTab: '30min',
                                    selectTab(tab, $el) {
                                        this.selectedTab = tab;
                                        $wire.set('aggregation', tab);
                                        $wire.loadData();
                                    }
                                }"
                                class="w-full bg-white rounded-md shadow-sm sm:min-h-56 p-4 flex flex-col justify-between">
                                <div class="w-full">
                                    <p class=""><b>Wybierz typ agregacji danych:</b></p>
                                     <div x-ref="tabButtons"
                                        class="mt-2 overflow-x-auto sm:text-sm flex sm:inline-grid items-center sm:justify-center w-auto sm:w-full h-10 flex-row sm:grid-cols-4 p-1 text-gray-500 bg-gray-200 rounded-lg select-none"
                                        role="tablist">

                                        <!-- Buttons -->
                                        <button data-tab="30min" x-on:click="selectTab('30min');"
                                        :class="selectedTab === '30min' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                         class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">30-minutowa</button>

                                        <button data-tab="terminowe" x-on:click="selectTab('terminowe');"
                                        :class="selectedTab === 'terminowe' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                        class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Terminowa</button>

                                        <button data-tab="dobowe" x-on:click="selectTab('dobowe'); "
                                        :class="selectedTab === 'dobowe' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                         class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Dobowa</button>

                                        <button data-tab="miesieczne" x-on:click="selectTab('miesieczne');"
                                        :class="selectedTab === 'miesieczne' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                         class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Miesieczna</button>
                                    </div>
                                </div>

                                <div class="px-2 py-3 ">
                                    <div  x-show="selectedTab === '30min'"  role="tabpanel" aria-label="30min" x-cloak>
                                        <p class="py-2 ">Wybierz okres odczytu danych:</p>
                                        <div x-cloak class="flex gap-2">
                                            <button wire:click="$set('dateOption', 'today'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class=" {{ $dateOption === 'today' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                Dzisiaj
                                            </button>

                                            <button wire:click="$set('dateOption', 'yesterday'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class=" {{ $dateOption === 'yesterday' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                Wczoraj
                                            </button>

                                            <button wire:click="$set('dateOption', '7days'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class="text-nowrap  {{ $dateOption === '7days' ? '' : 'opacity-50' }} whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                                                7 ostatnich dni
                                            </button>
                                        </div>
                                    </div>
                                    <div x-cloak x-show="selectedTab === 'terminowe'"  role="tabpanel" aria-label="terminowe">
                                        <p class="py-2">Wybierz zakres dni odczytu danych:</p>
                                                <div class="flex gap-2">
                                                     {{-- minDate below set to earliest data that i have --}}
                                                     <div class="flex flex-col md:flex-row w-full" x-data="{
                                                            start: $wire.entangle('terminoweStartDate'),
                                                            end: $wire.entangle('terminoweEndDate'),
                                                            endMax: '',
                                                            endMin: '',
                                                            minDate: '2025-07-24',
                                                            maxDate: '',

                                                            validateRange() {
                                                                const today = new Date();
                                                                const yesterday = new Date(today);
                                                                yesterday.setDate(today.getDate() - 1);
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
                                                        <div class="flex flex-col sm:flex-row items-end gap-4 w-full">
                                                            <div class="flex flex-col w-full">
                                                                <label for="start" class="sm:text-sm font-medium text-gray-700">Data początkowa:</label>
                                                                <input type="date" id="start"
                                                                    x-model="start" x-on:change="validateRange()"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="w-full border border-gray-300 px-2 py-1 rounded" />
                                                            </div>

                                                            <div class="flex flex-col w-full">
                                                                <label for="end" class="sm:text-sm font-medium text-gray-700">Dzień Końcowy:</label>
                                                                <input type="date" id="end"
                                                                    x-model="end" x-on:change="validateRange()"
                                                                    :min="endMin"
                                                                    :max="endMax"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="w-full border border-gray-300 px-2 py-1 rounded" />
                                                            </div>
                                                            <button
                                                            wire:click="loadData"
                                                            class="whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                        >
                                                            Odśwież dane
                                                        </button>
                                                        </div>
                                                    </div>
                                                </div>
                                    </div>

                                    <div x-cloak x-show="selectedTab === 'dobowe'"  role="tabpanel" aria-label="dobowe">
                                        <p class="py-2 ">Wybierz miesiąc odczytu danych:</p>
                                                    <div class="flex flex-col w-full sm:flex-row justify-center sm:justify-start">
                                                        <div x-data="{
                                                            start: $wire.entangle('doboweDate'),
                                                            maxDate: '',
                                                            validateRange() {
                                                                const today = new Date();
                                                                const yesterday = new Date(today);
                                                                yesterday.setDate(today.getDate() - 1);
                                                                const year = yesterday.getFullYear();
                                                                const month = String(yesterday.getMonth() + 1).padStart(2, '0');
                                                                this.maxDate = `${year}-${month}`;
                                                            }
                                                        }"
                                                        x-init="validateRange()">
                                                            <div class="flex flex-col w-full   justify-center">
                                                                <div class="flex flex-col w-full  justify-center ">
                                                                    <label for="start" class="sm:text-sm font-medium text-gray-700">Miesiąc z roku:</label>
                                                                    <input type="month" id="start"
                                                                        x-model="start" x-on:change="validateRange(); $wire.loadData()"
                                                                        min="2025-07"
                                                                        :max="maxDate"
                                                                        :disabled="!selectedId || !stations[selectedId] || !query"
                                                                        :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                        :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                        class="border border-gray-300 px-2 py-1 rounded" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                    </div>

                                    <div class="w-full" x-cloak x-show="selectedTab === 'miesieczne'" role="tabpanel" aria-label="miesieczne">
                                        <p class="py-2 ">Wybierz rok odczytu danych:</p>
                                        <div class="flex gap-2 w-full">
                                            <div
                                                x-data="{
                                                    start: $wire.entangle('miesieczneDate'),
                                                    years: [],
                                                    init() {
                                                        const today = new Date();
                                                        const yesterday = new Date(today);
                                                        yesterday.setDate(today.getDate() - 1);
                                                        const currentYear = yesterday.getFullYear();
                                                        const years = [];
                                                        for (let y = currentYear; y >= 2025; y--) {
                                                            years.push(y.toString());
                                                        }
                                                        this.years = years;
                                                    }

                                                }"
                                                x-init="init()"
                                            >
                                                <div class="flex flex-col items-center gap-4 w-full">
                                                    <div class="flex flex-col w-full">
                                                        <label for="year" class="sm:text-sm font-medium text-gray-700">Rok:</label>
                                                        <select
                                                            id="year"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                            x-model="start"
                                                            x-on:change="$wire.loadData()"
                                                            class="border border-gray-300 px-2 py-1 rounded w-64 sm:w-24">
                                                            <template x-for="year in years" :key="year">
                                                                <option :value="year" x-text="year"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-2 flex flex-col justify-end">
                        @if (!empty($stationData))
                            <div  class=" p-4 bg-white rounded-md shadow-sm sm:text-sm min-h-72">
                                <p class="font-bold  text-sm sm:text-base text-lime-600">
                                    Najnowsze dane meteo dla stacji ID: <span class="text-nowrap">{{ $stationData['kod_stacji'] }} ({{ $stationData['nazwa_stacji'] ?? '-' }})</span>
                                </p>
                                <p class="text-xs text-gray-500 mb-2">
                                    Dane pobrano: {{ $askTime ?? '–' }}
                                </p>
                                <ul class="grid grid-cols-2 gap-4 sm:text-sm">
                                    <li>
                                        <strong>Temp. gruntu:</strong> {{ $stationData['temperatura_gruntu'] ?? '-' }} °C
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['temperatura_gruntu_data']) ? Carbon::parse($stationData['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Wilg. względna:</strong> {{ $stationData['wilgotnosc_wzgledna'] ?? '-' }} %
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wilgotnosc_wzgledna_data']) ? Carbon::parse($stationData['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Wiatr śr.:</strong> {{ $stationData['wiatr_srednia_predkosc'] ?? '-' }} km/h
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_srednia_predkosc_data']) ? Carbon::parse($stationData['wiatr_srednia_predkosc_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Wiatr maks.:</strong> {{ $stationData['wiatr_predkosc_maksymalna'] ?? '-' }} km/h
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($stationData['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Kierunek wiatru:</strong> {{ $stationData['wiatr_kierunek'] ?? '-' }} °
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_kierunek_data']) ? Carbon::parse($stationData['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Wiatr poryw (10 min):</strong> {{ $stationData['wiatr_poryw_10min'] ?? '-' }} km/h
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_poryw_10min_data']) ? Carbon::parse($stationData['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Opad (10 min):</strong> {{ $stationData['opad_10min'] ?? '-' }} mm
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['opad_10min_data']) ? Carbon::parse($stationData['opad_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        @elseif(!empty($error))
                            <div class="relative p-4 bg-white rounded-md shadow-sm text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-fullflex flex-col justify-center">
                                    <div class="text-sm font-bold text-red-500">W oficjalnym API IMGW nie znaleziono danych dla stacji o wybranym ID.</div>
                                    <div class="font-bold text-gray-600 text-sm mt-2">
                                        Wybierz inną stację lub spróbuj innej z tego samego regionu.
                                    </div>
                                    <br>
                                    @if ($error)
                                        <p class="font-bold text-red-500">{{ $error }}</p>
                                    @endif
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

        <div class=" m-2 p-4 bg-white rounded-md shadow-sm">
            @if (!empty($stations[$stationId]) )
                <h1 class="text-sm sm:text-xl pb-2 font-bold">Wyszukano dane meteorologiczne dla stacji: <span class="text-lime-600">{{ $stationId.' - '.$stations[$stationId] }}</span></h1>
                <div wire:loading.remove class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                    <span>Wyświetlono na wykresie dane z okresu:
                        <b>
                        @switch($this->aggregation)
                            @case('30min')
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
                                    @default

                                @endswitch
                                @break
                            @case('terminowe')
                                {{ '['.$this->terminoweEndDate .'] - ['.  $this->terminoweStartDate .']' }} <span class="text-xs">(3 pomiary na dzień - około godziny 6:00, 12:00, 18:00)</span>
                                @break
                            @case('dobowe')
                                {{ '['.$this->doboweDate.']' }}
                                @break
                            @case('miesieczne')
                                {{ '['.$this->miesieczneDate.']' }}
                                @break
                            @default

                        @endswitch
                        </b>
                    </span>
                     <button id="fullscr" onclick="toggleFullscreen()"
                        class="text-xs whitespace-nowrap rounded-xl bg-blue-600 border  px-2 py-1  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                        Pełny ekran
                    </button>
                </div>
                <div wire:loading class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">

                    <button
                        class="text-xs whitespace-nowrap rounded-xl bg-white border  px-3 py-1 font-medium  transition ">
                        Ładowanie...
                    </button>
                </div>
            @else
                <h1 class="text-sm sm:text-xl pb-2 font-bold text-white">Brak wybranej stacji</h1>
                <div class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                    <button
                        class="text-xs whitespace-nowrap rounded-xl bg-white border  px-3 py-1 font-medium  transition ">
                        Oczekiwanie...
                    </button>
                </div>

            @endif

            @if(empty($this->weatherData))

                <div class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">
                        <div wire:loading.remove class="absolute left-0 top-0 w-full h-full flex flex-col justify-center text-center ">
                            @if (!empty($stations[$stationId]))
                                <p class="text-sm sm:text-xl font-bold text-red-500">Brak aktualnych danych z tego okresu dla stacji {{ $stationId.' - '.$stations[$stationId] }}</p>
                            @else
                                 <p class="text-sm sm:text-xl font-bold animate-pulse">Oczekiwanie na wybór stacji...</p>
                            @endif
                        </div>
                    <div wire:loading class="absolute top-0 left-0 w-full h-full z-20 animate-pulse border">
                        <div class="w-full h-full flex flex-col justify-center animate-pulse text-center">
                            <p class="text-sm sm:text-xl font-bold animate-pulse">Ładowanie...</p>
                        </div>
                    </div>
                </div>
            <div class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-white flex flex-row justify-between">
                Oczekiwanie...
            </div>

            @else
            <div id="chart" class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">
                <div wire:loading class="absolute top-0 left-0 w-full h-full z-20 animate-pulse ">
                    <div class="w-full h-full flex flex-col justify-center animate-pulse text-center">
                        <p class="text-sm sm:text-xl font-bold animate-pulse">Ładowanie...</p>
                    </div>
                </div>
                <canvas x-claok id="weatherChart" wire:loading.remove class="w-full h-full z-0 "></canvas>
            </div>

            <div wire:loading.remove class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Zestawienie tabelaryczne danych meteorologicznych stacji:
            </div>
            <div wire:loading class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Ładowanie...
            </div>
                    @switch($this->aggregation)
                            @case('30min')
                                    <div class="bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-screen">
                                        <table class="w-full text-left text-sm ">
                                            <thead class="border-b-2 border-gray-300 bg-slate-100 text-sm text-black ">
                                                <tr class="even:bg-blue-600/5 text-nowrap text-center">
                                                    <th title="Sortuj" class="p-2 cursor-pointer  text-sm transition hover:opacity-75 {{$sortBy === 'temperatura_gruntu_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('temperatura_gruntu_data')">
                                                        Pomiar temp.
                                                        @if($sortBy === 'temperatura_gruntu_data')
                                                            <span>{{ $sortDirection === 'asc' ? '(↑)' : '(↓)' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="p-2 cursor-pointer  text-sm transition hover:opacity-75 {{$sortBy === 'temperatura_gruntu' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_gruntu')">
                                                        Temp. gruntu [°C]
                                                        @if($sortBy === 'temperatura_gruntu')
                                                            <span>{{ $sortDirection === 'asc' ? '(↑)' : '(↓)' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="p-2 cursor-pointer  text-sm transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wilgotnosc_wzgledna_data')">
                                                        Pomiar wilg.
                                                        @if($sortBy === 'wilgotnosc_wzgledna_data')
                                                            <span>{{ $sortDirection === 'asc' ? '(↑)' : '(↓)' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="p-2 cursor-pointer  text-sm transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wilgotnosc_wzgledna')">
                                                        Wilg. względna [%]
                                                        @if($sortBy === 'wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '(↑)' : '(↓)' }}</span>
                                                        @endif
                                                    </th>
                                                    <th class="p-4">wiatr_kierunek</th>
                                                    <th class="p-4">wiatr_kierunek_data</th>
                                                    <th class="p-4">wiatr_srednia_predkosc</th>
                                                    <th class="p-4">wiatr_srednia_predkosc_data</th>
                                                    <th class="p-4">wiatr_predkosc_maksymalna</th>
                                                    <th class="p-4">wiatr_predkosc_maksymalna_data</th>

                                                    <th class="p-4">wiatr_poryw_10min</th>
                                                    <th class="p-4">wiatr_poryw_10min_data</th>
                                                    <th class="p-4">opad_10min</th>
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('opad_10min_data')">
                                                        opad_10min_data
                                                        @if($sortBy === 'opad_10min_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($this->sortedWeatherData as $data)
                                                    <tr class="even:bg-gray-700/5 text-center">
                                                        <td class="p-2 text-xs text-nowrap {{$sortBy === 'temperatura_gruntu_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['temperatura_gruntu_data']) ? Carbon::parse($data['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('H:i Y-m-d') : 'Brak pomiaru' }}
                                                        </td>
                                                        <td class="p-2 text-sm {{$sortBy === 'temperatura_gruntu' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_gruntu'] ?? 'Brak pomiaru' }}
                                                        </td>
                                                        <td class="p-2 text-xs text-nowrap {{$sortBy === 'wilgotnosc_wzgledna_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wilgotnosc_wzgledna_data']) ? Carbon::parse($data['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('H:i Y-m-d') : 'Brak pomiaru' }}
                                                        </td>
                                                        <td class="p-2 text-sm {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wilgotnosc_wzgledna'] ?? 'Brak pomiaru' }}
                                                        </td>
                                                        <td class="p-4">{{ $data['wiatr_kierunek'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_kierunek_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_srednia_predkosc'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_srednia_predkosc_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_predkosc_maksymalna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_predkosc_maksymalna_data'] ?? '-' }}</td>

                                                        <td class="p-4">{{ $data['wiatr_poryw_10min'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_poryw_10min_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['opad_10min'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['opad_10min_data'] ?? '-' }}</td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @break
                            @case('terminowe')

                                    <div class="overflow-hidden w-full overflow-x-auto overflow-y-auto rounded-xl border border-slate-300 max-h-screen">
                                        <table class="w-full text-left text-sm ">
                                            <thead class="border-b border-slate-300 bg-slate-100 text-sm text-black ">
                                                <tr class="even:bg-blue-700/5 ">
                                                     <th class="p-4 cursor-pointer" wire:click="setSort('temperatura_gruntu_data')">
                                                        temperatura_gruntu_data
                                                        @if($sortBy === 'temperatura_gruntu_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th class="p-4">temperatura_gruntu</th>

                                                    <th class="p-4">wiatr_kierunek</th>
                                                    <th class="p-4">wiatr_kierunek_data</th>
                                                    <th class="p-4">wiatr_srednia_predkosc</th>
                                                    <th class="p-4">wiatr_srednia_predkosc_data</th>
                                                    <th class="p-4">wiatr_predkosc_maksymalna</th>
                                                    <th class="p-4">wiatr_predkosc_maksymalna_data</th>
                                                    <th class="p-4">wilgotnosc_wzgledna</th>
                                                    <th class="p-4">wilgotnosc_wzgledna_data</th>
                                                    <th class="p-4">wiatr_poryw_10min</th>
                                                    <th class="p-4">wiatr_poryw_10min_data</th>
                                                    <th class="p-4">opad_10min</th>
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('opad_10min_data')">
                                                        opad_10min_data
                                                        @if($sortBy === 'opad_10min_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
                                                @foreach($this->sortedWeatherData as $data)
                                                    <tr class="even:bg-blue-700/5 ">
                                                        <td class="p-4">{{ $data['temperatura_gruntu_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['temperatura_gruntu'] ?? '-' }}</td>

                                                        <td class="p-4">{{ $data['wiatr_kierunek'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_kierunek_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_srednia_predkosc'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_srednia_predkosc_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_predkosc_maksymalna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_predkosc_maksymalna_data'] ?? '-' }}</td>

                                                        <td class="p-4">{{ $data['wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wilgotnosc_wzgledna_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_poryw_10min'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['wiatr_poryw_10min_data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['opad_10min'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['opad_10min_data'] ?? '-' }}</td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @break
                            @case('dobowe')

                                    <div class="overflow-hidden w-full overflow-x-auto overflow-y-auto rounded-xl border border-slate-300 max-h-screen">
                                        <table class="w-full text-left text-sm ">
                                            <thead class="border-b border-slate-300 bg-slate-100 text-sm text-black ">
                                                <tr class="even:bg-blue-700/5 ">
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('data')">
                                                        data
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th class="p-4">max_temp_gruntu_dobowa</th>
                                                    <th class="p-4">min_temp_gruntu_dobowa</th>
                                                    <th class="p-4">mean_temp_gruntu_dobowa</th>
                                                    <th class="p-4">mean_wiatr_kierunek</th>
                                                    <th class="p-4">mean_wiatr_srednia_predkosc</th>
                                                    <th class="p-4">max_wiatr_predkosc_maksymalna</th>
                                                    <th class="p-4">max_wiatr_poryw_10min</th>
                                                    <th class="p-4">mean_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">min_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">max_wilgotnosc_wzgledna</th>
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('sum_opad_10min')">
                                                        sum_opad_10min
                                                        @if($sortBy === 'sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
                                                @foreach($this->sortedWeatherData as $data)
                                                    <tr class="even:bg-blue-700/5 ">
                                                        <td class="p-4">{{ $data['data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_temp_gruntu_dobowa'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['min_temp_gruntu_dobowa'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_temp_gruntu_dobowa'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_wiatr_kierunek'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_wiatr_srednia_predkosc'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_wiatr_predkosc_maksymalna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_wiatr_poryw_10min'] ?? '-' }}</td>

                                                        <td class="p-4">{{ $data['mean_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['min_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['sum_opad_10min'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                    @break
                            @case('miesieczne')
                                    z roku {{ $this->miesieczneDate }}

                                    <div class="overflow-hidden w-full overflow-x-auto overflow-y-auto rounded-xl border border-slate-300 max-h-screen">
                                        <table class="w-full text-left text-sm ">
                                            <thead class="border-b border-slate-300 bg-slate-100 text-sm text-black ">
                                                <tr class="even:bg-blue-700/5 ">

                                                    <th class="p-4 cursor-pointer" wire:click="setSort('data')">
                                                        data
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th class="p-4">max_max_temp_gruntu_mies</th>
                                                    <th class="p-4">mean_max_temp_gruntu_mies</th>
                                                    <th class="p-4">min_min_temp_gruntu_mies</th>
                                                    <th class="p-4">mean_min_temp_gruntu_mies</th>
                                                    <th class="p-4">mean_mean_temp_gruntu_mies</th>
                                                    <th class="p-4">mean_mean_wiatr_kierunek</th>
                                                    <th class="p-4">mean_mean_wiatr_srednia_predkosc</th>
                                                    <th class="p-4">max_max_wiatr_predkosc_maksymalna</th>
                                                    <th class="p-4">min_min_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">mean_min_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">max_max_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">mean_max_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">mean_mean_wilgotnosc_wzgledna</th>
                                                    <th class="p-4">max_sum_opad_10min</th>
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('sum_sum_opad_10min')">
                                                        sum_sum_opad_10min
                                                        @if($sortBy === 'sum_sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
                                                @foreach($this->sortedWeatherData as $data)
                                                    <tr class="even:bg-blue-700/5 ">
                                                        <td class="p-4">{{ $data['data'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_max_temp_gruntu_mies'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_max_temp_gruntu_mies'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['min_min_temp_gruntu_mies'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_min_temp_gruntu_mies'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_mean_temp_gruntu_mies'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_mean_wiatr_kierunek'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_mean_wiatr_srednia_predkosc'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_max_wiatr_predkosc_maksymalna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['min_min_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_min_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_max_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_max_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['mean_mean_wilgotnosc_wzgledna'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['max_sum_opad_10min'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['sum_sum_opad_10min'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @break
                            @default

                        @endswitch

            @endif

        </div>
    </div>

    <script>
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



        function getLabelFromContext(ctx) {

            const formatDate = (date) => date.toLocaleDateString('sv-SE'); // yyyy-mm-dd
            if (!ctx || !ctx.aggregation) return '';

            switch (ctx.aggregation) {
                case '30min': {
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);

                    switch (ctx.dateOption) {
                        case 'today':
                            return `[${formatDate(today)}]`;
                        case 'yesterday':
                            return `[${formatDate(yesterday)}]`;
                        case '7days': {
                            const sevenDaysAgo = new Date(yesterday);
                            sevenDaysAgo.setDate(yesterday.getDate() - 6);
                            return `[${formatDate(sevenDaysAgo)}] - [${formatDate(yesterday)}]`;
                        }
                        default:
                            return '';
                    }
                }

                case 'terminowe':
                    return `[${ctx.terminoweStartDate}] - [${ctx.terminoweEndDate}] (3 pomiary na dzień)`;

                case 'dobowe':
                    return `[${ctx.doboweDate}]`;

                case 'miesieczne':
                    return `[${ctx.miesieczneDate}]`;

                default:
                    return '';
            }
        }



        function parseUtcDAY(dateStr) {
            // Split manually
            const [datePart, timePart] = dateStr.split(' ');
            const [year, month, day] = datePart.split('-');
            const [hour, minute, second] = timePart.split(':');

            // Create UTC date
            const utcDate = new Date(Date.UTC(year, month - 1, day, hour, minute, second));

            // Format to Europe/Warsaw
            return new Intl.DateTimeFormat('pl-PL', {
                timeZone: 'Europe/Warsaw',

                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).format(utcDate);
        }

        function parseUtcTIME(dateStr) {
            // Split manually
            const [datePart, timePart] = dateStr.split(' ');
            const [year, month, day] = datePart.split('-');
            const [hour, minute, second] = timePart.split(':');

            // Create UTC date
            const utcDate = new Date(Date.UTC(year, month - 1, day, hour, minute, second));

            // Format to Europe/Warsaw
            return new Intl.DateTimeFormat('sv-SE', {
                timeZone: 'Europe/Warsaw',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).format(utcDate);
        }

        document.addEventListener('livewire:init', () => {

         var chartInstance = null;
        if (chartInstance) {
                chartInstance.destroy(); // Clear existing chart
            }

        function renderChart(weatherData, aggr, typ) {
            if (chartInstance) {
                chartInstance.clear();
                chartInstance.destroy(); // Clear existing chart
            }
            let axisLabel = 'Data zapisu';
            const tmplabels = weatherData.map(item => {
                //cos z datami jest nie tak sprawdzic co jest nowsze?
                const raw = item.temperatura_gruntu_data ?? item.wilgotnosc_wzgledna_data ?? item.opad_10min_data
                ?? item.wiatr_srednia_predkosc_data ?? item.wiatr_predkosc_maksymalna_data ?? item.wiatr_poryw_10min_data ?? item.data;
                if(aggr === '30min' && (typ === 'today' || typ === 'yesterday')){
                    axisLabel = 'Godzina zapisu';
                    return raw ? parseUtcTIME(raw) : null;
                }
                else if(typ === '7days' || aggr === 'terminowe'){
                    return raw ? parseUtcDAY(raw) : null;
                }else{
                    return raw ? raw : null;
                }

            });

            let titleLabel = 'Dane meteorologiczne stacji ' + weatherData[0].nazwa_stacji + ' ' + titlelabel;
            let tmpAxisLabel = 'Temperatura gruntu [°C]';
            let humAxisLabel = 'Wilgotność względna [%]';
            let rainAxisLabel = 'Opad 10 min - suma [mm]';
            let meanWindAxisLabel = 'Wiatr - średnia prędkość [km/h]';
            let maxWindAxisLabel = 'Wiatr - maks. prędkość [km/h]';
            let porywWindAxisLabel = 'Wiatr - poryw 10 min [km/h]';

            if(aggr === 'dobowe'){
                tmpAxisLabel = 'Temperatura gruntu - średnia dobowa [°C]';
                humAxisLabel = 'Wilgotność względna - średnia dobowa [%]';
                rainAxisLabel = 'Opad 10 min - suma dobowa [mm]';
                maxWindAxisLabel = 'Wiatr - maks. prędkość dobowa [km/h]';
                meanWindAxisLabel = 'Wiatr - średnia prędkość dobowa [km/h]';
                porywWindAxisLabel = 'Wiatr - maks. dobowy poryw 10 min [km/h]';
            }
            if(aggr === 'miesieczne')
            {
                tmpAxisLabel = 'Temperatura gruntu - średnia miesieczna [°C]';
                humAxisLabel = 'Wilgotność względna - średnia miesieczna [%]';
                rainAxisLabel = 'Opad 10 min - suma miesieczna [mm]';
                maxWindAxisLabel = 'Wiatr - maks. prędkość miesieczna [km/h]';
                meanWindAxisLabel = 'Wiatr - średnia prędkość miesieczna [km/h]';
                porywWindAxisLabel = 'Wiatr - maks. miesieczny poryw 10 min [km/h]';
            }


            const temperatures = weatherData.map(item => parseFloat(item.temperatura_gruntu ?? item.mean_temp_gruntu_dobowa ?? item.mean_mean_temp_gruntu_mies) || null);
            const humidities = weatherData.map(item => parseFloat(item.wilgotnosc_wzgledna ?? item.mean_wilgotnosc_wzgledna ?? item.mean_mean_wilgotnosc_wzgledna) || null);
            const rain10s = weatherData.map(item => parseFloat(item.opad_10min ?? item.sum_opad_10min ?? item.sum_sum_opad_10min) || null);
            const meanWind = weatherData.map(item => parseFloat(item.wiatr_srednia_predkosc ?? item.mean_wiatr_srednia_predkosc ?? item.mean_mean_wiatr_srednia_predkosc) || null);
            const maxWind = weatherData.map(item => parseFloat(item.wiatr_predkosc_maksymalna ?? item.max_wiatr_predkosc_maksymalna ?? item.max_max_wiatr_predkosc_maksymalna) || null);
            const porywWind = weatherData.map(item => parseFloat(item.wiatr_poryw_10min ?? item.max_wiatr_poryw_10min ?? item.max_max_wiatr_poryw_10min) || null);

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
                                    datasets: [{
                                        label: tmpAxisLabel,
                                        data: temperatures,
                                        borderColor: 'rgb(252, 198, 3)',
                                        backgroundColor: 'rgb(252, 198, 3, 0.5)',
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
                                        // stack: 'combined',
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
                                        order: 3,
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
                                    {
                                        label: maxWindAxisLabel,
                                        data: maxWind,
                                        borderColor: 'rgb(51, 51, 51)',
                                        backgroundColor: 'rgb(51, 51, 51, 0.5)',
                                        tension: 0.1,
                                        borderWidth: 1,
                                        spanGaps: false,
                                        pointStyle: 'circle',
                                        pointRadius: 1,
                                        pointHoverRadius: 2,
                                        yAxisID: 'y3', // ← attach to right axis
                                        order: 6,
                                        hidden: true,
                                    },
                                    {
                                        label: porywWindAxisLabel,
                                        data: porywWind,
                                        borderColor: 'rgb(212, 19, 45)',
                                        backgroundColor: 'rgb(212, 19, 45, 0.5)',
                                        tension: 0.1,
                                        borderWidth: 2,
                                        spanGaps: false,
                                        pointStyle: 'circle',
                                        pointRadius: 0.5,
                                        pointHoverRadius: 2,
                                        yAxisID: 'y3', // ← attach to right axis
                                        order: 6,
                                        hidden: true,
                                    },
                                ]
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
                                                text: 'Temperatura gruntu [°C]',
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
                                                text: 'Wiatr - prędkość [km/h]',
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

        // Livewire.on('weatherDataUpdated', (newData) => {
        //     Alpine.nextTick(() => {
        //         var weatherData = newData[0][0];
        //         var aggregation = newData[0][1];
        //         var type = newData[0][2];

        //         if(Array.isArray(weatherData) && weatherData.length > 0){
        //             //console.log(weatherData);
        //             renderChart(weatherData, aggregation, type);
        //         }
        //         else{
        //             console.log('Nie ładuję wykresu');
        //         }
        //     });
        // });

        Livewire.on('weatherDataUpdated', (newData) => {
            Alpine.nextTick(() => {
                const weatherData = newData[0][0];
                const aggregation = newData[0][1];
                const type = newData[0][2];

                if (Array.isArray(weatherData) && weatherData.length > 0) {

                    //  Generate label using fresh values
                    titlelabel = getLabelFromContext(newData[1]);

                    renderChart(weatherData, aggregation, type);
                } else {
                    console.log('Nie ładuję wykresu – brak danych');
                }
            });
        });

    });
</script>

</div>
