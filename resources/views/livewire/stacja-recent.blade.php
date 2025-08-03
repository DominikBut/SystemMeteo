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
                    <h1 class="col-span-2 bg-white rounded-md shadow-sm py-4 px-2 mx-2 text-center text-sm sm:text-2xl font-bold tracking-wider">Przeglądasz gromadzone przez system dane meteorologiczne API IMGW</h1>
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
                            <div class=" p-4 bg-white rounded-md shadow-sm sm:text-sm min-h-72">
                                <p class="font-bold  text-sm sm:text-base text-lime-600">
                                    Najnowsze dane meteo dla stacji ID: <span class="text-nowrap">{{ $stationData['kod_stacji'] }} ({{ $stationData['nazwa_stacji'] ?? '-' }})</span>
                                </p>
                                <p class="text-xs text-gray-500 mb-2">
                                    Dane pobrano: {{ $askTime ?? '–' }}
                                </p>
                                <ul class="grid grid-cols-2 gap-4 sm:text-sm">
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Temp. gruntu:</strong> {{ $stationData['temperatura_gruntu'] ?? '-' }} °C
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['temperatura_gruntu_data']) ? Carbon::parse($stationData['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wilg. względna:</strong> {{ $stationData['wilgotnosc_wzgledna'] ?? '-' }} %
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wilgotnosc_wzgledna_data']) ? Carbon::parse($stationData['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 12h16M4 6h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr śr.:</strong> {{ $stationData['wiatr_srednia_predkosc'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_srednia_predkosc_data']) ? Carbon::parse($stationData['wiatr_srednia_predkosc_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr maks.:</strong> {{ $stationData['wiatr_predkosc_maksymalna'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($stationData['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>


                                        @php
                                            $rotation = is_numeric($stationData['wiatr_kierunek']) ? $stationData['wiatr_kierunek'] : 0;
                                        @endphp
                                        <div class="flex flex-row items-start gap-1">
                                            <div>
                                                <strong class="flex items-center gap-1">
                                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                    viewBox="0 0 24 24">
                                                    <path d="M12 3v18M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                    Kierunek wiatru:
                                                </strong>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                {{ $stationData['wiatr_kierunek'] ?? '-' }} °
                                                [
                                                <div class="inline-block transform font-extrabold px-1" style="rotate: {{ $rotation }}deg;">
                                                    ↑
                                                </div>]
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_kierunek_data']) ? Carbon::parse($stationData['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4l16 16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr poryw (10 min):</strong> {{ $stationData['wiatr_poryw_10min'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_poryw_10min_data']) ? Carbon::parse($stationData['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4v16h16V4H4zM9 9h6v6H9z" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Opad (10 min):</strong> {{ $stationData['opad_10min'] ?? '-' }} mm
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['opad_10min_data']) ? Carbon::parse($stationData['opad_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        @elseif(!empty($error))
                            <div class="relative p-4 bg-white rounded-md shadow-sm text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center">
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
                <div wire:loading.remove wire:target.except="setSort" class="ms-2 text-xs sm:text-sm py-2 font-semibold text-gray-500 flex flex-row justify-between">
                    <span>Wyświetlono na wykresie dane z okresu:
                        <b class="text-nowrap">
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
                                {{ '['.$this->terminoweStartDate .'] - ['.  $this->terminoweEndDate .']' }} <span class="text-xs">(3 pomiary na dzień - około godziny 6:00, 12:00, 18:00)</span>
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
                    collect($item)->except(['kod_stacji', 'nazwa_stacji', 'lon', 'lat', 'data'])
                                ->filter(fn($val) => !is_null($val))
                                ->isEmpty()
                );
            @endphp
            @if(empty($weatherData) || $allNull)

                <div class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">
                        <div wire:loading.remove wire:target.except="setSort" class="absolute left-0 top-0 w-full h-full flex flex-col justify-center text-center ">
                            @if (!empty($stations[$stationId]))
                                <p class="text-sm sm:text-xl font-bold text-red-500">Brak aktualnych danych z tego okresu dla stacji {{ $stationId.' - '.$stations[$stationId] }}</p>
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
                        @switch($aggregation)
                            @case('dobowe')
                                <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. <span class="text-nowrap">śr.  gruntu [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp.  <span class="text-nowrap">min. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Temp. <span class="text-nowrap">maks. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Wilg.  <span class="text-nowrap">śr. względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. min.<span class="text-nowrap"> względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. maks.<span class="text-nowrap"> względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Suma opad <span class="text-nowrap">10 min [mm]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr poryw <span class="text-nowrap">maks. 10 min [m/s]</span>
                                    </th>
                                </thead>
                                <tbody class="divide-y divide-slate-100  text-xs h-full">
                                    <tr class="bg-red-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MAKS.</td>
                                        <td>{{ $minMaxStats['mean_temp_gruntu_dobowa']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_temp_gruntu_dobowa']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_opad_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_predkosc_maksymalna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_poryw_10min']['max'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['mean_temp_gruntu_dobowa']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_temp_gruntu_dobowa']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_opad_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_predkosc_maksymalna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_poryw_10min']['avg'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['mean_temp_gruntu_dobowa']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_temp_gruntu_dobowa']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_opad_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_predkosc_maksymalna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_wiatr_poryw_10min']['min'] ?? '-' }}</td>
                                    </tr>
                                </tbody>
                                @break
                            @case('miesieczne')
                                <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. <span class="text-nowrap">śr.  gruntu [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp.  <span class="text-nowrap">min. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. min. <span class="text-nowrap">śr. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Temp. <span class="text-nowrap">maks. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Temp. maks. <span class="text-nowrap">śr. gruntu [°C]</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Wilg.  <span class="text-nowrap">śr. względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. min.<span class="text-nowrap"> względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. min. <span class="text-nowrap"> śr. względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. maks.<span class="text-nowrap"> względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wilg. maks. <span class="text-nowrap"> śr. względna [%]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Suma opad <span class="text-nowrap">10 min [mm]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Suma opad <span class="text-nowrap">maks. 10 min [mm]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr poryw <span class="text-nowrap">maks. 10 min [m/s]</span>
                                    </th>
                                </thead>
                                <tbody class="divide-y divide-slate-100  text-xs h-full">
                                    <tr class="bg-red-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MAKS.</td>
                                        <td>{{ $minMaxStats['mean_mean_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_sum_opad_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_sum_opad_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_predkosc_maksymalna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_poryw_10min']['max'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['mean_mean_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_sum_opad_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_sum_opad_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_predkosc_maksymalna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_poryw_10min']['avg'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['mean_mean_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['min_min_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_min_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_max_wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['sum_sum_opad_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_sum_opad_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['mean_mean_wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_predkosc_maksymalna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['max_max_wiatr_poryw_10min']['min'] ?? '-' }}</td>
                                    </tr>
                                </tbody>
                                @break
                            @default
                                <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. <span class="text-nowrap">gruntu [°C]</span>
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
                                    <th class="p-2 text-gray-600">
                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]</span>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        Wiatr poryw <span class="text-nowrap">10 min [m/s]</span>
                                    </th>
                                </thead>
                                <tbody class="divide-y divide-slate-100  text-xs h-full">
                                    <tr class="bg-red-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MAKS.</td>
                                        <td>{{ $minMaxStats['temperatura_gruntu']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['max'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['temperatura_gruntu']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['avg'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['temperatura_gruntu']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['min'] ?? '-' }}</td>
                                    </tr>
                                </tbody>
                        @endswitch
                    </table>
                </div>
            </div>
            <div wire:loading.remove wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                @switch($aggregation)
                    @case('terminowe')
                        Zestawienie tabelaryczne terminowych danych meteorologicznych stacji:
                        @break
                    @case('dobowe')
                        Zestawienie tabelaryczne dobowych danych meteorologicznych stacji:
                        @break
                    @case('miesieczne')
                        Zestawienie tabelaryczne miesiecznych danych meteorologicznych stacji:
                        @break
                    @default
                        Zestawienie tabelaryczne 30-minutowych danych meteorologicznych stacji:
                @endswitch

            </div>
            <div wire:loading wire:target.except="setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Ładowanie...
            </div>
            <div wire:loading wire:target.except="setSort" class="bg-white rounded-md shadow-sm border border-gray-300 min-h-32 w-full">

            </div>
            <div wire:loading.remove wire:target.except="setSort" class="min-h-32">

                    @switch($aggregation)

                            @case('dobowe')
                                <div class="min-h-28 bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-center text-xs text-wrap">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        Data pomiaru <span class="">
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_temp_gruntu_dobowa')">
                                                        Temp. <span class="text-nowrap">śr.  gruntu [°C]
                                                        @if($sortBy === 'mean_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_temp_gruntu_dobowa')">
                                                        Temp.  <span class="text-nowrap">min. gruntu [°C]
                                                        @if($sortBy === 'min_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_temp_gruntu_dobowa')">
                                                        Temp. <span class="text-nowrap">maks. gruntu [°C]
                                                        @if($sortBy === 'max_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_wilgotnosc_wzgledna')">
                                                        Wilg.  <span class="text-nowrap">śr. względna [%]
                                                        @if($sortBy === 'mean_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_wilgotnosc_wzgledna')">
                                                        Wilg. min.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'min_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_wilgotnosc_wzgledna')">
                                                        Wilg. maks.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'max_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('sum_opad_10min')">
                                                        Suma opad <span class="text-nowrap">10 min [mm]
                                                        @if($sortBy === 'sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'mean_wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_wiatr_srednia_predkosc')">
                                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'mean_wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_wiatr_predkosc_maksymalna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_wiatr_predkosc_maksymalna')">
                                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'max_wiatr_predkosc_maksymalna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_wiatr_poryw_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_wiatr_poryw_10min')">
                                                        Wiatr poryw <span class="text-nowrap">maks. 10 min [m/s]
                                                        @if($sortBy === 'max_wiatr_poryw_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_wiatr_kierunek' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_wiatr_kierunek')">
                                                        Wiatr śr. <span class="text-nowrap">kierunek [°]
                                                        @if($sortBy === 'mean_wiatr_kierunek')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                        {{ $data['data'] ?? 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_temp_gruntu_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_temp_gruntu_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_temp_gruntu_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_temp_gruntu_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_temp_gruntu_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_temp_gruntu_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_wiatr_predkosc_maksymalna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_wiatr_predkosc_maksymalna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_wiatr_poryw_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_wiatr_poryw_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-left  {{$sortBy === 'mean_wiatr_kierunek' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['mean_wiatr_kierunek']))
                                                                @php
                                                                    $rotation = is_numeric($data['mean_wiatr_kierunek']) ? $data['mean_wiatr_kierunek'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation }}deg;">
                                                                        ↑
                                                                    </div>
                                                                </span>
                                                                <span class="w-16">{{ $data['mean_wiatr_kierunek'] ?? '-' }}</span>
                                                            @else
                                                                <span class="w-16">{{ $data['mean_wiatr_kierunek'] ?? '-' }}</span>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                </div>
                                @break
                            @case('miesieczne')
                                <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-center text-xs  text-wrap">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        Data pomiaru <span class="">
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_mean_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_mean_temp_gruntu_mies')">
                                                        Temp. <span class="text-nowrap">śr. gruntu [°C]
                                                        @if($sortBy === 'mean_mean_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_min_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_min_temp_gruntu_mies')">
                                                        Temp.  <span class="text-nowrap">min. gruntu [°C]
                                                        @if($sortBy === 'min_min_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_min_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_min_temp_gruntu_mies')">
                                                        Temp. min. <span class="text-nowrap">śr. gruntu [°C]
                                                        @if($sortBy === 'mean_min_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_max_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_max_temp_gruntu_mies')">
                                                        Temp. <span class="text-nowrap">maks. gruntu [°C]
                                                        @if($sortBy === 'max_max_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_max_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_max_temp_gruntu_mies')">
                                                        Temp. maks. <span class="text-nowrap">śr. gruntu [°C]
                                                        @if($sortBy === 'mean_max_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_mean_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_mean_wilgotnosc_wzgledna')">
                                                        Wilg.  <span class="text-nowrap">śr. względna [%]
                                                        @if($sortBy === 'mean_mean_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_min_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_min_wilgotnosc_wzgledna')">
                                                        Wilg. min.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'min_min_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_min_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_min_wilgotnosc_wzgledna')">
                                                        Wilg. min. <span class="text-nowrap"> śr. względna [%]
                                                        @if($sortBy === 'mean_min_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_max_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_max_wilgotnosc_wzgledna')">
                                                        Wilg. maks.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'max_max_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_max_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_max_wilgotnosc_wzgledna')">
                                                        Wilg. maks. <span class="text-nowrap"> śr. względna [%]
                                                        @if($sortBy === 'mean_max_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'sum_sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('sum_sum_opad_10min')">
                                                        Suma opad <span class="text-nowrap">10 min [mm]
                                                        @if($sortBy === 'sum_sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'max_sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_sum_opad_10min')">
                                                        Suma opad <span class="text-nowrap">maks. 10 min [mm]
                                                        @if($sortBy === 'max_sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'mean_mean_wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_mean_wiatr_srednia_predkosc')">
                                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'mean_mean_wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_max_wiatr_predkosc_maksymalna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_max_wiatr_predkosc_maksymalna')">
                                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'max_max_wiatr_predkosc_maksymalna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_max_wiatr_poryw_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_max_wiatr_poryw_10min')">
                                                        Wiatr poryw <span class="text-nowrap">maks. 10 min [m/s]
                                                        @if($sortBy === 'max_max_wiatr_poryw_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_mean_wiatr_kierunek' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_mean_wiatr_kierunek')">
                                                        Wiatr śr. <span class="text-nowrap">kierunek [°]
                                                        @if($sortBy === 'mean_mean_wiatr_kierunek')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                        {{ $data['data'] ?? 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_mean_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_mean_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_min_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_min_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_min_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_min_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_max_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_max_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_max_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_max_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_mean_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_mean_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_min_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_min_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_min_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_min_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_max_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_max_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_max_wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_max_wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'sum_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['sum_sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_mean_wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_mean_wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_max_wiatr_predkosc_maksymalna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_max_wiatr_predkosc_maksymalna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_max_wiatr_poryw_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_max_wiatr_poryw_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-left  {{$sortBy === 'mean_mean_wiatr_kierunek' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['mean_mean_wiatr_kierunek']))
                                                                @php
                                                                    $rotation = is_numeric($data['mean_mean_wiatr_kierunek']) ? $data['mean_mean_wiatr_kierunek'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation }}deg;">
                                                                        ↑
                                                                    </div>
                                                                </span>
                                                                <span class="w-16">{{ $data['mean_mean_wiatr_kierunek'] ?? '-' }}</span>
                                                            @else
                                                                <span class="w-16">{{ $data['mean_mean_wiatr_kierunek'] ?? '-' }}</span>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                </div>
                                @break
                            @default
                                    <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'temperatura_gruntu_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('temperatura_gruntu_data')">
                                                        Pomiar <span class="text-nowrap">temp.
                                                        @if($sortBy === 'temperatura_gruntu_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temperatura_gruntu' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_gruntu')">
                                                        Temp. <span class="text-nowrap">gruntu [°C]
                                                        @if($sortBy === 'temperatura_gruntu')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wilgotnosc_wzgledna_data')">
                                                        Pomiar <span class="text-nowrap">wilg.
                                                        @if($sortBy === 'wilgotnosc_wzgledna_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wilgotnosc_wzgledna')">
                                                        Wilg. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>

                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'opad_10min_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('opad_10min_data')">
                                                        Pomiar <span class="text-nowrap">opadu
                                                        @if($sortBy === 'opad_10min_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('opad_10min')">
                                                        Opad <span class="text-nowrap">10 min [mm]
                                                        @if($sortBy === 'opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_srednia_predkosc_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wiatr_srednia_predkosc_data')">
                                                        Pomiar <span class="text-nowrap">śr. wiatru
                                                        @if($sortBy === 'wiatr_srednia_predkosc_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_srednia_predkosc')">
                                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_predkosc_maksymalna_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wiatr_predkosc_maksymalna_data')">
                                                        Pomiar <span class="text-nowrap">maks. wiatru
                                                        @if($sortBy === 'wiatr_predkosc_maksymalna_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_predkosc_maksymalna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_predkosc_maksymalna')">
                                                        Wiatr maks. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wiatr_predkosc_maksymalna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_poryw_10min_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wiatr_poryw_10min_data')">
                                                        Pomiar <span class="text-nowrap">porywu wiatru
                                                        @if($sortBy === 'wiatr_poryw_10min_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_poryw_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_poryw_10min')">
                                                        Wiatr poryw <span class="text-nowrap">10 min [m/s]
                                                        @if($sortBy === 'wiatr_poryw_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_kierunek_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('wiatr_kierunek_data')">
                                                        Pomiar <span class="text-nowrap">kierunku wiatru
                                                        @if($sortBy === 'wiatr_kierunek_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_kierunek' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_kierunek')">
                                                        Wiatr <span class="text-nowrap">kierunek [°]
                                                        @if($sortBy === 'wiatr_kierunek')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'temperatura_gruntu_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['temperatura_gruntu_data']) ? Carbon::parse($data['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temperatura_gruntu' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_gruntu'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'wilgotnosc_wzgledna_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wilgotnosc_wzgledna_data']) ? Carbon::parse($data['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'opad_10min_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['opad_10min_data']) ? Carbon::parse($data['opad_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'wiatr_srednia_predkosc_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wiatr_srednia_predkosc_data']) ? Carbon::parse($data['wiatr_srednia_predkosc_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'wiatr_predkosc_maksymalna_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($data['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_predkosc_maksymalna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_predkosc_maksymalna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'wiatr_poryw_10min_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wiatr_poryw_10min_data']) ? Carbon::parse($data['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_poryw_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_poryw_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'wiatr_kierunek_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['wiatr_kierunek_data']) ? Carbon::parse($data['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_kierunek' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['wiatr_kierunek']))
                                                                @php
                                                                    $rotation = is_numeric($data['wiatr_kierunek']) ? $data['wiatr_kierunek'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    [
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation }}deg;">
                                                                        ↑
                                                                    </div>]
                                                                </span>

                                                                {{ $data['wiatr_kierunek'] ?? '-' }}
                                                            @else
                                                                <span>{{ $data['wiatr_kierunek'] ?? '-' }}</span>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                        @endswitch
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
        //parse date for chart to without year
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
        //parse date for chart to only time
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
                chartInstance.clear();
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
                // const raw = item.temperatura_gruntu_data ?? item.wilgotnosc_wzgledna_data ?? item.opad_10min_data
                // ?? item.wiatr_srednia_predkosc_data ?? item.wiatr_predkosc_maksymalna_data ?? item.wiatr_poryw_10min_data ?? item.wiatr_kierunek_data;
                if(aggr === '30min' || aggr === 'terminowe'){
                    axisLabel = 'Godzina zapisu';
                    // Collect all date strings from the item
                    let dateFields = [
                        item.temperatura_gruntu_data,
                        item.wilgotnosc_wzgledna_data,
                        item.opad_10min_data,
                        item.wiatr_srednia_predkosc_data,
                        item.wiatr_predkosc_maksymalna_data,
                        item.wiatr_poryw_10min_data,
                        item.wiatr_kierunek_data
                    ];

                    // Filter out null/undefined and sort by newest
                    let newestDateStr = dateFields
                        .filter(Boolean)
                        .sort((a, b) => new Date(b) - new Date(a))[0];
                    if(typ === '7days' || aggr === 'terminowe'){
                        return newestDateStr ? parseUtcDAY(newestDateStr) : null;
                    }
                    return newestDateStr ? parseUtcTIME(newestDateStr) : null;

                }else{
                    let raw = item.data;
                    return raw ? raw : null;
                }

            });

            let titleLabel = 'Dane meteorologiczne stacji ' + weatherData[0].nazwa_stacji + ' ' + titlelabel;
            let tmpAxisLabel = 'Temp. gruntu [°C]';
            let humAxisLabel = 'Wilg. względna [%]';
            let rainAxisLabel = 'Opad 10 min - suma [mm]';
            let meanWindAxisLabel = 'Wiatr - śr.prędkość [m/s]';
            let maxWindAxisLabel = 'Wiatr - maks. prędkość [m/s]';
            let porywWindAxisLabel = 'Wiatr - poryw 10 min [m/s]';

            //dobowe
            let mintmpAxisLabel = 'Temp. gruntu - min. dobowa [°C]';
            let maxtmpAxisLabel = 'Temp. gruntu - maks. dobowa [°C]';
            let minhumAxisLabel = 'Wilg. względna - min. dobowa [%]';
            let maxhumAxisLabel = 'Wilg. względna - maks. dobowa [%]';



            if(aggr === 'dobowe'){
                tmpAxisLabel = 'Temp. gruntu - śr. dobowa [°C]';
                humAxisLabel = 'Wilg. względna - śr. dobowa [%]';
                rainAxisLabel = 'Opad 10 min - suma dobowa [mm]';
                maxWindAxisLabel = 'Wiatr - maks. prędkość dobowa [m/s]';
                meanWindAxisLabel = 'Wiatr - śr. prędkość dobowa [m/s]';
                porywWindAxisLabel = 'Wiatr - maks. dobowy poryw 10 min [m/s]';
            }
            if(aggr === 'miesieczne')
            {
                //miesieczne exclusive
                var MeanmintmpAxisLabel = 'Temp. gruntu - min. śr.  miesięczna [°C]';
                var MeanmaxtmpAxisLabel = 'Temp. gruntu - maks. śr.  miesięczna [°C]';
                var MeanminhumAxisLabel = 'Wilg. względna - min. śr.  miesięczna [%]';
                var MeanmaxhumAxisLabel = 'Wilg. względna - maks. śr.  miesięczna [%]';
                var maxrainAxisLabel = 'Opad 10 min - maks. suma [mm]';
                tmpAxisLabel = 'Temp. gruntu - śr. miesięczna [°C]';
                mintmpAxisLabel = 'Temp. gruntu - min. miesięczna [°C]';
                maxtmpAxisLabel = 'Temp. gruntu - maks. miesięczna [°C]';
                humAxisLabel = 'Wilg. względna - śr. miesięczna [%]';
                minhumAxisLabel = 'Wilg. względna - min. miesięczna [%]';
                maxhumAxisLabel = 'Wilg. względna - maks. miesięczna [%]';
                rainAxisLabel = 'Opad 10 min - suma miesięczna [mm]';
                maxWindAxisLabel = 'Wiatr - maks. prędkość miesięczna [m/s]';
                meanWindAxisLabel = 'Wiatr - śr. prędkość miesięczna [m/s]';
                porywWindAxisLabel = 'Wiatr - maks. miesięczny poryw 10 min [m/s]';
                mintmpAxisLabel = 'Temp. gruntu - min. miesięczna [°C]';
                maxtmpAxisLabel = 'Temp. gruntu - maks. miesięczna [°C]';
                minhumAxisLabel = 'Wilg. względna - min. miesięczna [%]';
                maxhumAxisLabel = 'Wilg. względna - maks. miesięczna [%]';
            }

            let datasetsM = [];

            const temperatures = weatherData.map(item => parseFloat(item.temperatura_gruntu ?? item.mean_temp_gruntu_dobowa ?? item.mean_mean_temp_gruntu_mies) || null);
            const humidities = weatherData.map(item => parseFloat(item.wilgotnosc_wzgledna ?? item.mean_wilgotnosc_wzgledna ?? item.mean_mean_wilgotnosc_wzgledna) || null);
            const rain10s = weatherData.map(item => parseFloat(item.opad_10min ?? item.sum_opad_10min ?? item.sum_sum_opad_10min) || null);
            const meanWind = weatherData.map(item => parseFloat(item.wiatr_srednia_predkosc ?? item.mean_wiatr_srednia_predkosc ?? item.mean_mean_wiatr_srednia_predkosc) || null);
            const maxWind = weatherData.map(item => parseFloat(item.wiatr_predkosc_maksymalna ?? item.max_wiatr_predkosc_maksymalna ?? item.max_max_wiatr_predkosc_maksymalna) || null);
            const porywWind = weatherData.map(item => parseFloat(item.wiatr_poryw_10min ?? item.max_wiatr_poryw_10min ?? item.max_max_wiatr_poryw_10min) || null);

             datasetsM.push({
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
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        yAxisID: 'y3', // ← attach to right axis
                                        order: 6,
                                        hidden: true,
                                    },
            );

            if( aggr==='dobowe'|| aggr === 'miesieczne')
            {
                const Mintemperatures = weatherData.map(item => parseFloat(item.min_temp_gruntu_dobowa ?? item.min_min_temp_gruntu_mies ) || null);
                const Maxtemperatures = weatherData.map(item => parseFloat(item.max_temp_gruntu_dobowa ?? item.max_max_temp_gruntu_mies ) || null);
                const Minhumidities = weatherData.map(item => parseFloat(item.min_wilgotnosc_wzgledna ?? item.min_min_wilgotnosc_wzgledna ) || null);
                const Maxhumidities = weatherData.map(item => parseFloat(item.max_wilgotnosc_wzgledna ?? item.max_max_wilgotnosc_wzgledna ) || null);
                datasetsM.push({
                                        label: mintmpAxisLabel,
                                        data: Mintemperatures,
                                        borderColor: 'rgb(255, 223, 107)',
                                        backgroundColor: 'rgb(255, 223, 107, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 1,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: maxtmpAxisLabel,
                                        data: Maxtemperatures,
                                        borderColor: 'rgb(161, 126, 2)',
                                        backgroundColor: 'rgb(161, 126, 2, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 1,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: minhumAxisLabel,
                                        data: Minhumidities,
                                        borderColor: 'rgb(98, 240, 199)',
                                        backgroundColor: 'rgb(98, 240, 199, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y1', // ← attach to right axis
                                        order: 2,
                                        hidden: true,
                                    },
                                    {
                                        label: maxhumAxisLabel,
                                        data: Maxhumidities,
                                        borderColor: 'rgb(1, 140, 100)',
                                        backgroundColor: 'rgb(1, 140, 100, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y1', // ← attach to right axis
                                        order: 2,
                                        hidden: true,
                                    },
                );
            }
            if(aggr === 'miesieczne')
            {
                const MeanMintemperatures = weatherData.map(item => parseFloat(item.mean_min_temp_gruntu_mies) || null);
                const MeanMaxtemperatures = weatherData.map(item => parseFloat(item.mean_max_temp_gruntu_mies) || null);
                const MeanMinhumidities = weatherData.map(item => parseFloat(item.mean_min_wilgotnosc_wzgledna ) || null);
                const MeanMaxhumidities = weatherData.map(item => parseFloat(item.mean_max_wilgotnosc_wzgledna) || null);
                const Maxrain10s = weatherData.map(item => parseFloat(item.max_sum_opad_10min) || null);
                datasetsM.push({
                                        label: MeanmintmpAxisLabel,
                                        data: MeanMintemperatures,
                                        borderColor: 'rgb(252, 233, 162)',
                                        backgroundColor: 'rgb(252, 233, 162, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 7,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: MeanmaxtmpAxisLabel,
                                        data: MeanMaxtemperatures,
                                        borderColor: 'rgb(105, 82, 1)',
                                        backgroundColor: 'rgb(105, 82, 1, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 7,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: MeanminhumAxisLabel,
                                        data: MeanMinhumidities,
                                        borderColor: 'rgb(168, 255, 230)',
                                        backgroundColor: 'rgb(168, 255, 230, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y1', // ← attach to right axis
                                        order: 7,
                                        hidden: true,
                                    },
                                    {
                                        label: MeanmaxhumAxisLabel,
                                        data: MeanMaxhumidities,
                                        borderColor: 'rgb(3, 69, 50)',
                                        backgroundColor: 'rgb(3, 69, 50, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y1', // ← attach to right axis
                                        order: 7,
                                        hidden: true,
                                    },
                                    {
                                        label: maxrainAxisLabel,
                                        data: rain10s,
                                        type: 'bar',
                                        stack: 'combined',
                                        // borderDash: [2, 2],
                                        // pointStyle: 'circle',
                                        // pointRadius: 3,
                                        // pointHoverRadius: 4,
                                        borderColor: 'rgb(1, 48, 135)',
                                        backgroundColor: 'rgb(37, 104, 230)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y2', // ← attach to right axis
                                        order: 3,
                                    },
                );

            }

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
                                                text: 'Wiatr - prędkość [m/s]',
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
                const aggregation = newData[0][1];
                const type = newData[0][2];

                if (Array.isArray(weatherData) && weatherData.length > 0) {

                    //  Generate label using fresh values
                    titlelabel = getLabelFromContext(newData[1]);
                    // console.log(weatherData);
                    renderChart(weatherData, aggregation, type);
                } else {
                    console.log('Nie ładuję wykresu – brak danych');
                }
            });
        });

    });
</script>
</div>
