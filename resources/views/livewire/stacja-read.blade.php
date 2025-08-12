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
                    <h1 class="col-span-2 bg-white rounded-md shadow-sm pt-4 pb-1 px-2 mx-2 text-center text-sm sm:text-2xl font-bold tracking-wider">Przeglądasz zweryfikowane archiwalne dane meteorologiczne - klimatyczne IMGW <br>(od 2001.01.01) </h1>
                    <h2 class="col-span-2 bg-white rounded-md shadow-sm py-2 px-2 mx-2 text-center tyext-xs sm:text-sm text-gray-500 tracking-wider">Aktualne dane bieżącego miesiąca są udostępnianie po jego zakończeniu 10-ego dnia 2 miesiące później.</h2>
                    <div class="flex flex-col justify-between p-2">
                         @if (!empty($stations) && (count($stations)!=0))
                        <div>
                            <p class="p-1 font-bold text-gray-600">Wybierz jedną z {{ count($stations) }} oficjalnych stacji IMGW:</p>
                            <div class="relative w-full">
                                <input wire:loading.attr="disabled" wire:targetr="loadData"
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
                            <h1 class="w-full bg-white rounded-md shadow-sm text-sm text-red-500 p-4 mb-2 text-center"><b>Błąd połączenia z API spróbuj ponownie za godzinę, aby móc przeglądać dane!</b></h1>
                        @endif
                        <div class="w-full">
                            <div
                                x-data="{
                                    selectedTab: 'terminowe',
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

                                        <button
                                         wire:loading.attr="disabled" wire:targetr="loadData"
                                        data-tab="terminowe" x-on:click="selectTab('terminowe');"
                                        :class="selectedTab === 'terminowe' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                        class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Terminowa</button>

                                        <button data-tab="dobowe" x-on:click="selectTab('dobowe'); " wire:loading.attr="disabled" wire:targetr="loadData"
                                        :class="selectedTab === 'dobowe' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                         class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Dobowa</button>

                                        <button data-tab="miesieczne" x-on:click="selectTab('miesieczne');" wire:loading.attr="disabled" wire:targetr="loadData"
                                        :class="selectedTab === 'miesieczne' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                         class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                                        type="button" role="tab">Miesięczna</button>
                                    </div>
                                </div>

                                <div class="px-2 py-3 ">

                                    <div x-show="selectedTab === 'terminowe'"  role="tabpanel" aria-label="terminowe">
                                        <p class="py-2">Wybierz zakres dni odczytu danych:</p>
                                                <div class="flex gap-2">
                                                     {{-- minDate below set to earliest data that i have --}}
                                                     <div class="flex flex-col md:flex-row w-full" x-data="{
                                                            start: $wire.entangle('terminoweStartDate'),
                                                            end: $wire.entangle('terminoweEndDate'),
                                                            endMax: '',
                                                            endMin: '',
                                                            minDate: '2001-01-01',
                                                            maxDate: '',

                                                            validateRange() {
                                                                const today = new Date();

                                                                const formatDate = (d) => {
                                                                    const year = d.getFullYear();
                                                                    const month = String(d.getMonth() + 1).padStart(2, '0');
                                                                    const day = String(d.getDate()).padStart(2, '0');
                                                                    return `${year}-${month}-${day}`;
                                                                };

                                                                // 1. Global min
                                                                this.minDate = '2001-01-01';

                                                                // 2. Max start = last day of the month 2 or 3 months ago depending on today’s day
                                                                const offset = today.getDate() >= 10 ? 2 : 3;
                                                                const maxStart = new Date(today.getFullYear(), today.getMonth() - offset + 1, 0); // Last day of that month
                                                                this.maxDate = formatDate(maxStart);

                                                                // 3. Clamp start
                                                                if (this.start < this.minDate) this.start = this.minDate;
                                                                if (this.start > this.maxDate) this.start = this.maxDate;

                                                                // 4. Set endMin to start
                                                                this.endMin = this.start;

                                                                // 5. Clamp end to not be before start
                                                                if (this.end < this.endMin) this.end = this.endMin;

                                                                // 6. Enforce same month & year
                                                                const s = new Date(this.start);
                                                                const e = new Date(this.end);

                                                                if (s.getFullYear() !== e.getFullYear() || s.getMonth() !== e.getMonth()) {
                                                                    this.end = this.start;
                                                                }

                                                                // 7. endMax = last day of start's month
                                                                const lastDayOfStartMonth = new Date(s.getFullYear(), s.getMonth() + 1, 0);
                                                                this.endMax = formatDate(lastDayOfStartMonth);

                                                                // 8. Clamp end
                                                                if (this.end > this.endMax) this.end = this.endMax;
                                                            }

                                                        }"
                                                        x-init="validateRange()">
                                                        <div class="flex flex-col sm:flex-row items-end gap-4 w-full">
                                                            <div class="flex flex-col w-full">
                                                                <label for="start" class="sm:text-sm font-medium text-gray-700">Data początkowa:</label>
                                                                <input type="date" id="start" wire:loading.attr="disabled" wire:targetr="loadData"
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
                                                                <input type="date" id="end" wire:loading.attr="disabled" wire:targetr="loadData"
                                                                    x-model="end" x-on:change="validateRange()"
                                                                    :min="endMin"
                                                                    :max="endMax"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="w-full border border-gray-300 px-2 py-1 rounded" />
                                                            </div>
                                                            <button
                                                            wire:click="loadData"  wire:loading.attr="disabled" wire:targetr="loadData"
                                                            class="whitespace-nowrap rounded-xl bg-blue-600 border  px-4 py-2  font-medium text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                        >
                                                            Załaduj dane
                                                        </button>
                                                        </div>
                                                    </div>
                                                </div>
                                    </div>

                                    <div x-cloak x-show="selectedTab === 'dobowe'"  role="tabpanel" aria-label="dobowe">
                                        <p class="py-2 ">Wybierz miesiąc odczytu danych:</p>
                                                    <div class="flex flex-row gap-3 w-auto sm:flex-row justify-center items-start h-full sm:justify-between">
                                                        <div x-data="{
                                                            start: $wire.entangle('doboweDate'),
                                                            maxDate: '',
                                                            validateRange() {
                                                                const today = new Date();
                                                                const day = today.getDate();

                                                                // Subtract 2 or 3 months depending on the day
                                                                const monthsToSubtract = day >= 10 ? 2 : 3;

                                                                today.setDate(10); // Set to 10th (safe for rollover)
                                                                today.setMonth(today.getMonth() - monthsToSubtract);

                                                                const year = today.getFullYear();
                                                                const month = String(today.getMonth() + 1).padStart(2, '0');

                                                                this.maxDate = `${year}-${month}`;
                                                            }
                                                        }"
                                                        x-init="validateRange()">
                                                            <div class="flex flex-col w-full   justify-center">
                                                                <div class="flex flex-col w-full  justify-center ">
                                                                    <label for="start" class="sm:text-sm font-medium text-gray-700">Miesiąc z roku:</label>
                                                                    <input type="month" id="start" wire:loading.attr="disabled" wire:targetr="loadData"
                                                                        x-model="start" x-on:change="validateRange(); $wire.loadData()"
                                                                        min="2001-01"
                                                                        :max="maxDate"
                                                                        :disabled="!selectedId || !stations[selectedId] || !query"
                                                                        :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                        :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                        class="border border-gray-300 px-2 py-1 rounded" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Toggle Primary -->
                                                        <div class="h-full flex flex-col items-start justify-between">
                                                            <label for="togglePrimary" class="inline-flex flex-col items-end sm:items-end ">
                                                                <span class="text-xs sm:text-sm text-nowrap sm:text-wrap mb-1 font-medium text-slate-700 peer-checked:text-black peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                                                Drugi zestaw danych:</span>
                                                            <input wire:model="doboweType" x-on:change="$wire.loadData()" id="togglePrimary" type="checkbox" class=" peer sr-only" role="switch" checked
                                                            :disabled="!selectedId || !stations[selectedId] || !query" wire:loading.attr="disabled" wire:targetr="loadData"
                                                                        :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                        :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"/>

                                                            <div class="cursor-pointer relative h-6 w-16 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full border border-slate-300 bg-slate-100
                                                            after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full
                                                            after:bg-slate-700 after:transition-all after:content-[''] peer-checked:bg-blue-700 peer-checked:after:bg-slate-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-slate-800 peer-focus:peer-checked:outline-blue-700 peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 " aria-hidden="true"></div>
                                                        </label>
                                                        </div>

                                                    </div>

                                    </div>

                                    <div class="w-full" x-cloak x-show="selectedTab === 'miesieczne'" role="tabpanel" aria-label="miesieczne">
                                        <p class="py-2 ">Wybierz rok odczytu danych:</p>
                                        <div class="flex flex-row gap-3 w-full items-start h-full justify-between">
                                            <div
                                                x-data="{
                                                    start: $wire.entangle('miesieczneDate'),
                                                    years: [],
                                                    init() {
                                                        const today = new Date();
                                                        const year = today.getFullYear();
                                                        const month = today.getMonth(); // 0 = Jan, 1 = Feb
                                                        const day = today.getDate();

                                                        // Determine the last complete year
                                                        const cutoffYear = (month > 1 || (month === 1 && day >= 10))
                                                            ? year - 1
                                                            : year - 2;

                                                        const years = [];
                                                        for (let y = cutoffYear; y >= 2001; y--) {
                                                            years.push(y.toString());
                                                        }

                                                        this.years = years;
                                                    }

                                                }"
                                                x-init="init()"
                                            >
                                                <div class="flex flex-col items-center gap-4 w-auto">
                                                    <div class="flex flex-col w-auto">
                                                        <label for="year" class="sm:text-sm font-medium text-gray-700">Rok:</label>
                                                        <select
                                                            id="year" wire:loading.attr="disabled" wire:targetr="loadData"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                            x-model="start"
                                                            x-on:change="$wire.loadData()"
                                                            class="border border-gray-300 px-2 py-1 rounded w-24">
                                                            <template x-for="year in years" :key="year">
                                                                <option :value="year" x-text="year"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggle Primary -->
                                                        <div class="h-full flex flex-col items-start justify-between">
                                                            <label for="togglePrimary2" class="inline-flex flex-col items-end sm:items-end ">
                                                                <span class="text-xs sm:text-sm text-nowrap sm:text-wrap mb-1 font-medium text-slate-700 peer-checked:text-black peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                                                Drugi zestaw danych:</span>
                                                            <input wire:model="miesieczneType" x-on:change="$wire.loadData()" id="togglePrimary2" type="checkbox" class=" peer sr-only" role="switch" checked
                                                            :disabled="!selectedId || !stations[selectedId] || !query" wire:loading.attr="disabled" wire:targetr="loadData"
                                                                        :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                        :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"/>

                                                            <div class="cursor-pointer relative h-6 w-16 after:h-5 after:w-5 peer-checked:after:translate-x-5 rounded-full border border-slate-300 bg-slate-100
                                                            after:absolute after:bottom-0 after:left-[0.0625rem] after:top-0 after:my-auto after:rounded-full
                                                            after:bg-slate-700 after:transition-all after:content-[''] peer-checked:bg-blue-700 peer-checked:after:bg-slate-100 peer-focus:outline-2 peer-focus:outline-offset-2 peer-focus:outline-slate-800 peer-focus:peer-checked:outline-blue-700 peer-active:outline-offset-0 peer-disabled:cursor-not-allowed peer-disabled:opacity-70 " aria-hidden="true"></div>
                                                        </label>
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
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Temp. powietrza:</strong> {{ $stationData['temperatura_powietrza'] ?? '-' }} °C
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['temperatura_powietrza_data']) ? Carbon::parse($stationData['temperatura_powietrza_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Temp. gruntu:</strong> {{ $stationData['temperatura_gruntu'] ?? '-' }} °C
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['temperatura_gruntu_data']) ? Carbon::parse($stationData['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wilg. względna:</strong> {{ $stationData['wilgotnosc_wzgledna'] ?? '-' }} %
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wilgotnosc_wzgledna_data']) ? Carbon::parse($stationData['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
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
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wiatr maks.:</strong> {{ $stationData['wiatr_predkosc_maksymalna'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($stationData['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>

                                        @php
                                            $rotation = is_numeric($stationData['wiatr_kierunek']) ? $stationData['wiatr_kierunek'] : 0;
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
                                                {{ $stationData['wiatr_kierunek'] ?? '-' }} °
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_kierunek_data']) ? Carbon::parse($stationData['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4l16 16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wiatr poryw <span class="text-nowrap">(10 min):</span></strong> {{ $stationData['wiatr_poryw_10min'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_poryw_10min_data']) ? Carbon::parse($stationData['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4v16h16V4H4zM9 9h6v6H9z" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Opad <span class="text-nowrap">(10 min):</span></strong> {{ $stationData['opad_10min'] ?? '-' }} mm
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

                            @case('terminowe')
                                {{ '['.$this->terminoweStartDate .'] - ['.  $this->terminoweEndDate .']' }} <span class="text-xs">(3 pomiary na dzień - 6:00, 12:00, 18:00)</span>
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

                <canvas wire:ignore id="weatherChart"  wire:target.except="setSort" class="relative w-full h-full z-0 ">
                </canvas>
                <button  wire:target.except="setSort" id="fullscr" onclick="toggleFullscreen()" style="line-height: 0.5rem; font-size: 0.6rem"
                        class="absolute top-1 right-1  z-10  whitespace-nowrap rounded-xl bg-blue-600 border px-2 py-2 text-slate-100 transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed">
                        Pełny ekran
                </button>
                 {{-- <div wire:loading wire:target.except="setSort" class="absolute top-0 left-0 w-full h-full z-20 flex flex-col justify-center">
                        <div class="w-full h-full flex flex-col justify-center text-center items-center">
                            <p class="w-min text-sm sm:text-xl font-bold  p-6 bg-white rounded-md ">Ładowanie...</p>
                        </div>
                </div> --}}
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
                                @if ($this->doboweType === false)
                                    <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                        <th class="p-2 text-gray-600">
                                            <span class="text-nowrap">Typ wartości</span>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. min. przy<span class="text-nowrap"> gruncie [°C]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. śr.<span class="text-nowrap"> powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. min.<span class="text-nowrap"> powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Temp. maks.<span class="text-nowrap"> powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Suma ogólna <span class="text-nowrap"> opadów [mm]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Suma opadów<span class="text-nowrap"> deszczu  [mm]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Suma opadów<span class="text-nowrap"> śniegu  [mm]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                        <div class="flex flex-col">Wysokość pokrywy <span class="text-nowrap">śnieżnej [cm]</span></div>
                                        </th>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100  text-xs h-full">
                                        <tr class="bg-red-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MAKS.</td>
                                            <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_temp_powietrza_dobowa']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_temp_powietrza_dobowa']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_temp_powietrza_dobowa']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_rain']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_snow']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['max'] ?? '-' }}</td>

                                        </tr>
                                        <tr class="bg-green-50 text-center h-auto">
                                            <td class="p-2 text-gray-700">ŚR.</td>
                                            <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_temp_powietrza_dobowa']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_temp_powietrza_dobowa']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_temp_powietrza_dobowa']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_rain']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_snow']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['avg'] ?? '-' }}</td>
                                        </tr>
                                        <tr class="bg-blue-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MIN.</td>
                                            <td>{{ $minMaxStats['min_temp_gruntu_dobowa']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_temp_powietrza_dobowa']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_temp_powietrza_dobowa']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_temp_powietrza_dobowa']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_rain']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_opad_10min_snow']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['min'] ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                @else
                                    <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                        <th class="p-2 text-gray-600">
                                            <span class="text-nowrap">Typ wartości</span>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. śr.<span class="text-nowrap">powietrza [°C]</span></div>
                                        </th>

                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Wilg. śr.<span class="text-nowrap">względna [%]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap"> ogólnego śr. [0-8 oktany]</span></div>
                                        </th>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100  text-xs h-full">
                                        <tr class="bg-red-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MAKS.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['max'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['max'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>

                                        </tr>
                                        <tr class="bg-green-50 text-center h-auto">
                                            <td class="p-2 text-gray-700">ŚR.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['avg'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['avg'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                                <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>
                                        </tr>
                                        <tr class="bg-blue-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MIN.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['min'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['min'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                @endif

                                @break
                            @case('miesieczne')
                            @if ($this->miesieczneType === false)
                                     <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                        <th class="p-2 text-gray-600">
                                            <span class="text-nowrap">Typ wartości</span>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. min. <span class="text-nowrap">przy gruncie [°C]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. śr.<span class="text-nowrap"> powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. absolutna <span class="text-nowrap">min. powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. min. <span class="text-nowrap">śr. powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Temp. absolutna <span class="text-nowrap">maks. powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Temp. maks. <span class="text-nowrap">śr. powietrza [°C]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Suma opadów <span class="text-nowrap"> miesięczna [mm]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Suma opadów <span class="text-nowrap"> maks. dobowa [mm]</span></div>
                                        </th>

                                        <th class="p-2 text-wrap text-gray-600">
                                            <div class="flex flex-col">Liczba dni z<span class="text-nowrap">opadem deszczu</span></div>
                                        </th>
                                        <th class="p-2 text-wrap text-gray-600">
                                            <div class="flex flex-col">Liczba dni z<span class="text-nowrap">opadem śniegu</span></div>
                                        </th>

                                        <th class="p-2 text-wrap text-gray-600">
                                            <div class="flex flex-col">Wysokość maks. <span class="text-nowrap"> pokrywy śnieżnej [cm]</span></div>
                                        </th>
                                        <th class="p-2 text-wrap text-gray-600">
                                            <div class="flex flex-col">Liczba dni z<span class="text-nowrap">pokrywą śnieżną</span></div>
                                        </th>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100  text-xs h-full">
                                        <tr class="bg-red-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MAKS.</td>
                                            <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_mean_temp_powietrza_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_min_min_temp_powietrza_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_min_temp_powietrza_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_max_max_temp_powietrza_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_max_temp_powietrza_mies']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_sum_opad_10min']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_sum_opad_10min']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_deszcz_opad_10min']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_snieg_opad_10min']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_pokrywa_sniezna_wys']['max'] ?? '-' }}</td>

                                        </tr>
                                        <tr class="bg-green-50 text-center h-auto">
                                            <td class="p-2 text-gray-700">ŚR.</td>
                                            <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_mean_temp_powietrza_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_min_min_temp_powietrza_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_min_temp_powietrza_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_max_max_temp_powietrza_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_max_temp_powietrza_mies']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_sum_opad_10min']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_sum_opad_10min']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_deszcz_opad_10min']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_snieg_opad_10min']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_pokrywa_sniezna_wys']['avg'] ?? '-' }}</td>
                                        </tr>
                                        <tr class="bg-blue-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MIN.</td>
                                            <td>{{ $minMaxStats['min_min_temp_gruntu_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['mean_mean_temp_powietrza_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_min_min_temp_powietrza_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['min_min_temp_powietrza_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['abs_max_max_temp_powietrza_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_max_temp_powietrza_mies']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['sum_sum_opad_10min']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['max_sum_opad_10min']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_deszcz_opad_10min']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_snieg_opad_10min']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['pokrywa_sniezna_wys']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['dni_pokrywa_sniezna_wys']['min'] ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                            @else
                                    <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                        <th class="p-2 text-gray-600">
                                            <span class="text-nowrap">Typ wartości</span>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Temp. śr.<span class="text-nowrap">powietrza [°C]</span></div>
                                        </th>

                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Wilg. śr.<span class="text-nowrap">względna [%]</span></div>
                                        </th>
                                        <th class="p-2 text-gray-600">
                                            <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span></div>
                                        </th>
                                        <th class="p-2  text-gray-600">
                                            <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap"> ogólnego śr. [0-8 oktany]</span></div>
                                        </th>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100  text-xs h-full">
                                        <tr class="bg-red-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MAKS.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['max'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['max'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>

                                        </tr>
                                        <tr class="bg-green-50 text-center h-auto">
                                            <td class="p-2 text-gray-700">ŚR.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['avg'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['avg'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                                <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>
                                        </tr>
                                        <tr class="bg-blue-50 text-center font-semibold h-auto">
                                            <td class="p-2 text-gray-700">MIN.</td>
                                            <td>{{ $minMaxStats['temperatura_powietrza']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                            <td>{{ $minMaxStats['wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                            <td class="flex flex-col">
                                            <strong>{{ $minMaxStats['zachmurzenie']['min'] ?? '-' }}</strong>
                                                                @php
                                                                    $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                    $zachmurzenie = $minMaxStats['zachmurzenie']['min'] ?? null;

                                                                    $zachmurzenieMapOktanty = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => 'Bardzo małe zachmurzenie',
                                                                        2 => 'Małe zachmurzenie',
                                                                        3 => 'Umiarkowane zachmurzenie',
                                                                        4 => 'Połowiczne zachmurzenie',
                                                                        5 => 'Umiarkowanie duże',
                                                                        6 => 'Duże zachmurzenie',
                                                                        7 => 'Prawie całkowite',
                                                                        8 => 'Całkowite zachmurzenie',
                                                                        9 => 'Nie można określić',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieMapDziesiatki = [
                                                                        0 => 'Bezchmurnie',
                                                                        1 => '1/10 nieba',
                                                                        2 => '2/10 nieba',
                                                                        3 => '3/10 nieba',
                                                                        4 => '4/10 nieba',
                                                                        5 => 'Połowiczne',
                                                                        6 => '6/10 nieba',
                                                                        7 => '7/10 nieba',
                                                                        8 => '8/10 nieba',
                                                                        9 => '9/10 nieba',
                                                                        10 => 'Całkowite zachmurzenie',
                                                                        99 => 'Brak danych',
                                                                    ];

                                                                    $zachmurzenieOpis = '-';

                                                                    if (!is_null($zachmurzenie)) {
                                                                        if (!is_null($rok) && (int)$rok < 1989) {
                                                                            $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                        } else {
                                                                            $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                        }
                                                                    }
                                                                @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                            @endif

                                @break
                            @default
                                <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        <div class="flex flex-col">Temp. <span class="text-nowrap">powietrza [°C]</span></div>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        <div class="flex flex-col">Temp. zwilżonego <span class="text-nowrap">powietrza [°C]</span></div>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        <div class="flex flex-col">Wilg. <span class="text-nowrap">względna [%]</span></div>
                                    </th>
                                    <th class="p-2 text-gray-600">
                                        <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]</span></div>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap"> [0-8 oktany]</span></div>
                                    </th>
                                </thead>
                                <tbody class="divide-y divide-slate-100  text-xs h-full">
                                    <tr class="bg-red-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MAKS.</td>
                                        <td>{{ $minMaxStats['temperatura_powietrza']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['temp_term_zw']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                        <td class="flex flex-col">
                                        <strong>{{ $minMaxStats['zachmurzenie']['max'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $minMaxStats['zachmurzenie']['max'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                           <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                        </td>

                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['temperatura_powietrza']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['temp_term_zw']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                        <td class="flex flex-col">
                                        <strong>{{ $minMaxStats['zachmurzenie']['avg'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $minMaxStats['zachmurzenie']['avg'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                        </td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['temperatura_powietrza']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['temp_term_zw']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                        <td class="flex flex-col">
                                        <strong>{{ $minMaxStats['zachmurzenie']['min'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($weatherData[0]['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $minMaxStats['zachmurzenie']['min'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                         <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                        </td>

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
                                {{-- <div class="min-h-28 bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
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
                                                        <div class="flex flex-col">Temp. śr.<span class="text-nowrap"> gruntu [°C]
                                                        @if($sortBy === 'mean_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_temp_gruntu_dobowa')">
                                                        <div class="flex flex-col">Temp. min.<span class="text-nowrap"> gruntu [°C]
                                                        @if($sortBy === 'min_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_temp_gruntu_dobowa')">
                                                        <div class="flex flex-col">Temp. maks.<span class="text-nowrap"> gruntu [°C]
                                                        @if($sortBy === 'max_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. śr.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'mean_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. min.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'min_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. maks.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'max_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. śr.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'mean_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. min.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'min_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. maks.<span class="text-nowrap"> względna [%]
                                                        @if($sortBy === 'max_wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
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
                                                        <td class="p-2  {{$sortBy === 'mean_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_temp_powietrza_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_temp_powietrza_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_temp_powietrza_dobowa'] ?? '-' }}
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
                                                                        ↓
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
                                </div> --}}
                                @if ($this->doboweType === false)
                                    <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        <div class="flex flex-col">Data wykonania <span class="text-nowrap">pomiarów
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_temp_gruntu_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_temp_gruntu_dobowa')">
                                                        <div class="flex flex-col">Temp. min. przy<span class="text-nowrap"> gruncie [°C]
                                                        @if($sortBy === 'min_temp_gruntu_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. śr.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'mean_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. min.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'min_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_temp_powietrza_dobowa' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_temp_powietrza_dobowa')">
                                                        <div class="flex flex-col">Temp. maks.<span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'max_temp_powietrza_dobowa')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('sum_opad_10min')">
                                                        Suma <span class="text-nowrap">opadów [mm]
                                                        @if($sortBy === 'sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class=" p-2 transition  text-gray-600">
                                                        <div class="flex flex-col">Rodzaj opadu <span class="text-nowrap">
                                                            {{-- [W/N] --}}
                                                        </span></div>
                                                    </th>
                                                   <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'pokrywa_sniezna_wys' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('pokrywa_sniezna_wys')">
                                                        <div class="flex flex-col">Wysokość pokrywy <span class="text-nowrap">śnieżnej [cm]</span><span class="text-nowrap"> (jeżeli wystapowała)
                                                        @if($sortBy === 'pokrywa_sniezna_wys')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">

                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['data']) ? Carbon::parse($data['data'], 'UTC')->format('Y-m-d') : 'Brak' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'min_temp_gruntu_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_temp_gruntu_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_temp_powietrza_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_temp_powietrza_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_temp_powietrza_dobowa' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_temp_powietrza_dobowa'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2">
                                                            @php
                                                                    $rodzajOpis = '-';
                                                                    switch ($data['rodzaj_opadu']) {
                                                                        case 'W':
                                                                            $rodzajOpis ='Deszcz';
                                                                            break;
                                                                        case 'S':
                                                                            $rodzajOpis ='Śnieg';
                                                                            break;
                                                                        case 'S/W':
                                                                            $rodzajOpis ='Deszcz ze śniegiem';
                                                                            break;
                                                                        case 'W/S':
                                                                            $rodzajOpis ='Deszcz ze śniegiem';
                                                                            break;
                                                                        default:
                                                                            $rodzajOpis = '-';
                                                                            break;
                                                                    }

                                                            @endphp
                                                             {{  $rodzajOpis }}
                                                        </td>
                                                        <td class="p-2">
                                                           {{ $data['pokrywa_sniezna_wys'] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        <div class="flex flex-col">Data wykonania <span class="text-nowrap">pomiarów
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temperatura_powietrza' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_powietrza')">
                                                        <div class="flex flex-col">Temp. śr. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temperatura_powietrza')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. śr. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>

                                                    </th>
                                                     <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_srednia_predkosc')">
                                                        <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'zachmurzenie' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('zachmurzenie')">
                                                        <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap">ogólnego śr. [0-8 oktany]
                                                        @if($sortBy === 'zachmurzenie')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">

                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['data']) ? Carbon::parse($data['data'], 'UTC')->format('Y-m-d') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temperatura_powietrza' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_powietrza'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 flex flex-col  {{$sortBy === 'zachmurzenie' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            <strong>{{ $data['zachmurzenie'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($data['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $data['zachmurzenie'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @break
                            @case('miesieczne')
                                @if ($this->miesieczneType === false)
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
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_min_temp_gruntu_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_min_temp_gruntu_mies')">
                                                       <div class="flex flex-col"><span class="text-nowrap">Temp. min.</span><span class="text-nowrap"> przy gruncie [°C]
                                                        @if($sortBy === 'min_min_temp_gruntu_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="text-wrap hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'mean_mean_temp_powietrza_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('mean_mean_temp_powietrza_mies')">
                                                        <div class="flex flex-col"><span class="text-nowrap">Temp. śr.</span><span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'mean_mean_temp_powietrza_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="text-wrap hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'abs_min_min_temp_powietrza_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('abs_min_min_temp_powietrza_mies')">
                                                        <div class="flex flex-col"><span class="text-nowrap">Temp. absolutna </span><span class="text-nowrap">min. powietrza [°C]
                                                        @if($sortBy === 'abs_min_min_temp_powietrza_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="text-wrap hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'min_min_temp_powietrza_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('min_min_temp_powietrza_mies')">
                                                        <div class="flex flex-col"><span class="text-nowrap">Temp. min. śr.</span><span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'min_min_temp_powietrza_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="text-wrap hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'abs_max_max_temp_powietrza_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('abs_max_max_temp_powietrza_mies')">
                                                        <div class="flex flex-col"><span class="text-nowrap">Temp. absolutna </span><span class="text-nowrap">maks. powietrza [°C]
                                                        @if($sortBy === 'abs_max_max_temp_powietrza_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class=" hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'max_max_temp_powietrza_mies' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_max_temp_powietrza_mies')">
                                                        <div class="flex flex-col"><span class="text-nowrap">Temp. maks. śr.</span><span class="text-nowrap"> powietrza [°C]
                                                        @if($sortBy === 'max_max_temp_powietrza_mies')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                        </div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'sum_sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('sum_sum_opad_10min')">
                                                        Suma opadów <span class="text-nowrap">miesięczna [mm]
                                                        @if($sortBy === 'sum_sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'max_sum_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('max_sum_opad_10min')">
                                                        Suma opadów maks.<span class="text-nowrap"> dobowa [mm]
                                                        @if($sortBy === 'max_sum_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class=" p-2 transition  text-gray-600">
                                                        <div class="flex flex-col">Pierwszy dzień<span class="text-nowrap"> opadu maks.

                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class=" p-2 transition  text-gray-600">
                                                        <div class="flex flex-col">Ostatni dzień<span class="text-nowrap"> opadu maks.

                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'dni_deszcz_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('dni_deszcz_opad_10min')">
                                                        <div class="flex flex-col">Liczba dni z<span class="text-nowrap">opadem deszczu
                                                        @if($sortBy === 'dni_deszcz_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'dni_snieg_opad_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('dni_snieg_opad_10min')">
                                                        <div class="flex flex-col">Liczba dni z<span class="text-nowrap">opadem śniegu
                                                        @if($sortBy === 'dni_snieg_opad_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                   <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'pokrywa_sniezna_wys' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('pokrywa_sniezna_wys')">
                                                        <div class="flex flex-col">Wys. maks. pokrywy <span class="text-nowrap">śnieżnej [cm]</span><span class="text-nowrap"> (jeżeli wystapowała)
                                                        @if($sortBy === 'pokrywa_sniezna_wys')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'dni_pokrywa_sniezna_wys' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('dni_pokrywa_sniezna_wys')">
                                                        <div class="flex flex-col">Liczba dni z<span class="text-nowrap">pokrywą śnieżną</span><span class="text-nowrap"> (jeżeli wystapowała)
                                                        @if($sortBy === 'dni_pokrywa_sniezna_wys')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                        {{ $data['data'] ?? 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_min_temp_gruntu_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_min_temp_gruntu_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'mean_mean_temp_powietrza_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['mean_mean_temp_powietrza_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'abs_min_min_temp_powietrza_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['abs_min_min_temp_powietrza_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'min_min_temp_powietrza_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['min_min_temp_powietrza_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'abs_max_max_temp_powietrza_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['abs_max_max_temp_powietrza_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_max_temp_powietrza_mies' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_max_temp_powietrza_mies'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'sum_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['sum_sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'max_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['max_sum_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'first_max_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">

                                                            {{ $data['first_max_sum_opad_10min'] ?  ($data['data'] ?? '????-??').'-'.$data['first_max_sum_opad_10min'] : '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'last_max_sum_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['last_max_sum_opad_10min'] ?  ($data['data'] ?? '????-??').'-'.$data['last_max_sum_opad_10min'] : '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'dni_deszcz_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['dni_deszcz_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'dni_snieg_opad_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['dni_snieg_opad_10min'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'pokrywa_sniezna_wys' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['pokrywa_sniezna_wys'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'dni_pokrywa_sniezna_wys' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['dni_pokrywa_sniezna_wys'] ?? '-' }}
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        <div class="flex flex-col">Data wykonania <span class="text-nowrap">pomiarów
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temperatura_powietrza' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_powietrza')">
                                                        <div class="flex flex-col">Temp. śr. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temperatura_powietrza')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. śr. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>

                                                    </th>
                                                     <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_srednia_predkosc')">
                                                        <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'zachmurzenie' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('zachmurzenie')">
                                                        <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap">ogólnego śr. [0-8 oktany]
                                                        @if($sortBy === 'zachmurzenie')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">

                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['data']) ? Carbon::parse($data['data'], 'UTC')->format('Y-m') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temperatura_powietrza' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_powietrza'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 flex flex-col  {{$sortBy === 'zachmurzenie' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            <strong>{{ $data['zachmurzenie'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($data['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $data['zachmurzenie'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                @break

                        @default
                                     <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('data')">
                                                        <div class="flex flex-col">Data wykonania <span class="text-nowrap">pomiarów
                                                        @if($sortBy === 'data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temperatura_powietrza' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_powietrza')">
                                                        <div class="flex flex-col">Temp. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temperatura_powietrza')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temp_term_zw' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temp_term_zw')">
                                                        <div class="flex flex-col">Temp. zwilżonego <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temp_term_zw')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wilgotnosc_wzgledna')">
                                                        <div class="flex flex-col">Wilg. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'wilgotnosc_wzgledna')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>

                                                    </th>
                                                     <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_srednia_predkosc')">
                                                        <div class="flex flex-col">Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wiatr_srednia_predkosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wiatr_kierunek_kod' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wiatr_kierunek_kod')">
                                                        <div class="flex flex-col">Wiatr <span class="text-nowrap">kierunek [kod]
                                                        @if($sortBy === 'wiatr_kierunek_kod')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'zachmurzenie' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('zachmurzenie')">
                                                        <div class="flex flex-col">Stopień zachmurzenia<span class="text-nowrap"> [0-8 oktany]
                                                        @if($sortBy === 'zachmurzenie')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'widzialnosc' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('widzialnosc')">
                                                        <div class="flex flex-col">Stopień <span class="text-nowrap"> widzialności
                                                        @if($sortBy === 'widzialnosc')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class=" p-2 transition  text-gray-600">
                                                        <div class="flex flex-col">Zakłócenie - wskaźnik <span class="text-nowrap">wentylacji
                                                            {{-- [W/N] --}}

                                                        </span></div>
                                                    </th>
                                                    <th title="Sortuj" class=" p-2 transition  text-gray-600"
                                                        >
                                                        <div class="flex flex-col">Zakłócenie - wskaźnik <span class="text-nowrap">lodu
                                                            {{-- [L/W] --}}

                                                        </span></div>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">

                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 text-nowrap {{$sortBy === 'data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['data']) ? Carbon::parse($data['data'], 'UTC')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temperatura_powietrza' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_powietrza'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temp_term_zw' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temp_term_zw'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wilgotnosc_wzgledna' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wilgotnosc_wzgledna'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_srednia_predkosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['wiatr_srednia_predkosc'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'wiatr_kierunek_kod' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['wiatr_kierunek_kod']))
                                                                {{-- @php
                                                                    $rotation = is_numeric($data['wiatr_kierunek']) ? $data['wiatr_kierunek'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    [
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation }}deg;">
                                                                        ↓
                                                                    </div>]
                                                                </span> --}}

                                                                {{ $data['wiatr_kierunek_kod'] ?? '-' }}
                                                            @else
                                                                <span>{{ $data['wiatr_kierunek_kod'] ?? '-' }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="p-2 flex flex-col  {{$sortBy === 'zachmurzenie' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            <strong>{{ $data['zachmurzenie'] ?? '-' }}</strong>
                                                            @php
                                                                $rok = Carbon::parse($data['data'], 'UTC')->format('Y') ?? null;
                                                                $zachmurzenie = $data['zachmurzenie'] ?? null;

                                                                $zachmurzenieMapOktanty = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => 'Bardzo małe zachmurzenie',
                                                                    2 => 'Małe zachmurzenie',
                                                                    3 => 'Umiarkowane zachmurzenie',
                                                                    4 => 'Połowiczne zachmurzenie',
                                                                    5 => 'Umiarkowanie duże',
                                                                    6 => 'Duże zachmurzenie',
                                                                    7 => 'Prawie całkowite',
                                                                    8 => 'Całkowite zachmurzenie',
                                                                    9 => 'Nie można określić',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieMapDziesiatki = [
                                                                    0 => 'Bezchmurnie',
                                                                    1 => '1/10 nieba',
                                                                    2 => '2/10 nieba',
                                                                    3 => '3/10 nieba',
                                                                    4 => '4/10 nieba',
                                                                    5 => 'Połowiczne',
                                                                    6 => '6/10 nieba',
                                                                    7 => '7/10 nieba',
                                                                    8 => '8/10 nieba',
                                                                    9 => '9/10 nieba',
                                                                    10 => 'Całkowite zachmurzenie',
                                                                    99 => 'Brak danych',
                                                                ];

                                                                $zachmurzenieOpis = '-';

                                                                if (!is_null($zachmurzenie)) {
                                                                    if (!is_null($rok) && (int)$rok < 1989) {
                                                                        $zachmurzenieOpis = $zachmurzenieMapDziesiatki[(int)$zachmurzenie] ?? '-';
                                                                    } else {
                                                                        $zachmurzenieOpis = $zachmurzenieMapOktanty[(int)$zachmurzenie] ?? '-';
                                                                    }
                                                                }
                                                            @endphp

                                                            <span class="font-normal text-gray-500">{{ $zachmurzenieOpis }}</span>
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'widzialnosc' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{-- {{ $data['widzialnosc'] ?? '-' }} --}}
                                                            @php
                                                                $widzialnosc = $data['widzialnosc'] ?? null;

                                                                if (is_numeric($widzialnosc)) {
                                                                    $kod = (int) $widzialnosc;

                                                                    if ($kod >= 0 && $kod <= 49) {
                                                                        $widzialnoscText = ($kod * 100) . ' m';
                                                                    } elseif ($kod >= 50 && $kod <= 80) {
                                                                        $widzialnoscText = ($kod - 45) . ' km';
                                                                    } elseif ($kod >= 81 && $kod <= 89) {
                                                                        $widzialnoscText = (($kod - 80) * 5 + 30) . ' km';
                                                                    } elseif ($kod === 99) {
                                                                        $widzialnoscText = 'brak danych';
                                                                    } else {
                                                                        $widzialnoscText = '-';
                                                                    }
                                                                } else {
                                                                    $widzialnoscText = '-';
                                                                }
                                                            @endphp
                                                             {{ $widzialnoscText }}
                                                        </td>
                                                        <td class="p-2">
                                                            {{-- {{ $data['wskaz_wentylacji']}} --}}
                                                            {{ $data['wskaz_wentylacji'] ? ($data['wskaz_wentylacji'] =="W" ? 'Wentylowana' : 'Niewentylowana') : '-' }}
                                                        </td>
                                                        <td class="p-2">
                                                            {{-- {{ $data['wskaz_lodu'] ?? '-' }} --}}
                                                             {{ $data['wskaz_lodu'] ? ($data['wskaz_lodu'] =="W" ? 'Wolna od lodu' : 'Lód') : '-' }}
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

            const formatDate = (date) => date; // yyyy-mm-dd
            if (!ctx || !ctx.aggregation) return '';

            switch (ctx.aggregation) {
                case 'dobowe':
                    return `[${ctx.doboweDate}]`;

                case 'miesieczne':
                    return `[${ctx.miesieczneDate}]`;

                default:
                      return `[${ctx.terminoweStartDate}] - [${ctx.terminoweEndDate}] (3 pomiary na dzień - 6:00, 12:00, 18:00)`;
            }
        }

        function parseUtcDAY(dateStr) {
            // Example input: "2025-08-06 14:30:00"
            // Remove the year and keep the rest: "08-06 14:30"

            // Split the input date string into date and time parts
            const [datePart, timePart] = dateStr.split(' ');

            // Split the date part into year, month, day
            const [, month, day] = datePart.split('-');

            // Split the time part into hour, minute, second
            const [hour, minute] = timePart.split(':');

            // Return formatted string without year
            return `${day}.${month}, ${hour}:${minute}`;
        }

        document.addEventListener('livewire:init', () => {

        var chartInstance = null;
        if (chartInstance) {
                chartInstance.clear();
                chartInstance.destroy(); // Clear existing chart
            }

        function renderChart(weatherData, aggr, type) {
            if (chartInstance) {
                chartInstance.clear();
                chartInstance.destroy(); // Clear existing chart
            }
            let axisLabel = 'Data zapisu';
            const tmplabels = weatherData.map(item => {
                    if(aggr === 'terminowe'){
                        axisLabel = 'Godzina zapisu';

                        return item.data ? parseUtcDAY(item.data) : null;
                    }
                    let raw = item.data;
                    return raw ? raw : null;
            });
            let displayAxesTerminowe = true;
            let displayAxesDobowe = false;

            let titleLabel = 'Dane meteorologiczne stacji ' + weatherData[0].nazwa_stacji + ' ' + titlelabel;
            let tmpzwAxisLabel = 'Temp. zwilżonego powietrza [°C]';
            let tmpPowAxisLabel = 'Temp. powietrza [°C]';
            let humAxisLabel = 'Wilg. względna [%]';
            let cloudsAxisLabel = 'Stopień zachmurzenia [0-8 okatny]';
            let meanWindAxisLabel = 'Wiatr - śr. prędkość [m/s]';

            let mintmpAxisLabel = 'Temp. gruntu - min. dobowa [°C]';
            let minPowtmpAxisLabel = 'Temp. powietrza - min. dobowa [°C]';
            let maxPowtmpAxisLabel = 'Temp. powietrza - maks. dobowa [°C]';
            let pokrySniegAxisLabel = 'Wys. pokrywy śnieżej [cm]';
            let rainAxisLabel = 'Opad deszczu - suma dobowa [mm]';
            let snowAxisLabel = 'Opad śniegu - suma dobowa [mm]';


            if(aggr === 'dobowe' && type===false){
                tmpPowAxisLabel = 'Temp. powietrza - śr. dobowa [°C]';
            }
            if(aggr === 'dobowe' && type===true){
                tmpPowAxisLabel = 'Temp. powietrza - śr. dobowa [°C]';
                humAxisLabel = 'Wilg. śr. względna dobowa [%]';
                cloudsAxisLabel = 'Stopień zachmurzenia ogólnego śr. dobowa [0-8 okatny]';
                meanWindAxisLabel = 'Wiatr - śr. prędkość dobowa [m/s]';
            }
            if(aggr === 'miesieczne' && type === false)
            {
                var MeanmintmpPowAxisLabel = 'Temp. powietrza - min. śr. miesięczna [°C]';
                var MeanmaxtmpPowAxisLabel = 'Temp. powietrza - maks. śr. miesięczna [°C]';
                var maxrainAxisLabel = 'Opad - maks. suma dobowa [mm]';
                mintmpAxisLabel = 'Temp. gruntu - min. miesięczna [°C]';
                tmpPowAxisLabel = 'Temp. powietrza - śr. miesięczna [°C]';
                minPowtmpAxisLabel = 'Temp. powietrza - abs. min. miesięczna [°C]';
                maxPowtmpAxisLabel = 'Temp. powietrza - abs. maks. miesięczna [°C]';
                rainAxisLabel = 'Opad - suma miesięczna [mm]';
                pokrySniegAxisLabel = 'Maks. wys. pokrywy śnieżej [cm]';
            }
            if(aggr === 'miesieczne' && type===true){
                tmpPowAxisLabel = 'Temp. powietrza - śr. miesięczna [°C]';
                humAxisLabel = 'Wilg. śr. względna miesięczna [%]';
                cloudsAxisLabel = 'Stopień zachmurzenia ogólnego śr. miesięczny [0-8 okatny]';
                meanWindAxisLabel = 'Wiatr - śr. prędkość miesięczna [m/s]';
            }

            let datasetsM = [];

            //ogolne
            const Powtemperatures = weatherData.map(item => {
                const val = parseFloat(item.temperatura_powietrza ?? item.mean_temp_powietrza_dobowa ?? item.mean_mean_temp_powietrza_mies);
                return isNaN(val) ? null : val;
            });
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
             );
            if(aggr==='terminowe')
            {
                displayAxesDobowe = false;

                const zwtemperatures = weatherData.map(item => {
                 const val = parseFloat(item.temp_term_zw);
                return isNaN(val) ? null : val;
                });

                const humidities = weatherData.map(item => {
                    const val = parseFloat(item.wilgotnosc_wzgledna);
                    return isNaN(val) ? null : val;
                });
                const clouds = weatherData.map(item => {
                    const val = parseFloat(item.zachmurzenie);
                    return isNaN(val) ? null : val;
                });
                const meanWind = weatherData.map(item => {
                    const val = parseFloat(item.wiatr_srednia_predkosc);
                    return isNaN(val) ? null : val;
                });
                datasetsM.push({
                                        label: tmpzwAxisLabel,
                                        data: zwtemperatures,
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
                                        hidden: false,
                                    },
                                    {
                                        label: cloudsAxisLabel,
                                        data: clouds,
                                        borderColor: 'rgb(98, 3, 145)',
                                        backgroundColor: 'rgb(98, 3, 145, 0.5)',
                                        borderWidth: 1,
                                        pointRadius: 1,
                                        pointHoverRadius: 2,
                                        tension: 0.1,
                                        spanGaps: false,
                                        pointStyle: 'circle',
                                        yAxisID: 'y2', // ← attach to right axis
                                        order: 5,
                                        hidden: true,
                                    },
                );
            }
            if( aggr==='dobowe' && type===false)
            {
                displayAxesDobowe = true;
                displayAxesTerminowe = false;

                const Mintemperatures = weatherData.map(item => {
                    const val = parseFloat(item.min_temp_gruntu_dobowa);
                    return isNaN(val) ? null : val;
                });
                const PowMintemperatures = weatherData.map(item => {
                    const val = parseFloat(item.min_temp_powietrza_dobowa);
                    return isNaN(val) ? null : val;
                });
                const PowMaxtemperatures = weatherData.map(item => {
                    const val = parseFloat(item.max_temp_powietrza_dobowa);
                    return isNaN(val) ? null : val;
                });
                const snowHeights = weatherData.map(item => {
                    const val = parseFloat(item.pokrywa_sniezna_wys);
                    return isNaN(val) ? null : val;
                });
                const snows = weatherData.map(item => {
                    if (typeof item.rodzaj_opadu === 'string' && item.rodzaj_opadu.includes('S')) {
                        const val = parseFloat(item.sum_opad_10min);
                        return isNaN(val) ? null : val;
                    }
                    return null;
                });

                const rains = weatherData.map(item => {
                    if (typeof item.rodzaj_opadu === 'string' && item.rodzaj_opadu.includes('W')) {
                        const val = parseFloat(item.sum_opad_10min);
                        return isNaN(val) ? null : val;
                    }
                    return null;
                });

                datasetsM.push(
                                    {
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
                                        label: minPowtmpAxisLabel,
                                        data: PowMintemperatures,
                                        borderColor: 'rgb(242, 170, 107)',
                                        backgroundColor: 'rgb(242, 170, 107, 0.5)',
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
                                        label: maxPowtmpAxisLabel,
                                        data: PowMaxtemperatures,
                                        borderColor: 'rgb(199, 95, 4)',
                                        backgroundColor: 'rgb(199, 95, 4, 0.5)',
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
                                        label: rainAxisLabel,
                                        data: rains,
                                        type: 'bar',
                                        stack: 'combined',
                                        // borderDash: [2, 2],
                                        // pointStyle: 'circle',
                                        // pointRadius: 3,
                                        // pointHoverRadius: 4,
                                        borderColor: 'rgb(1, 48, 135)',
                                        backgroundColor: 'rgb(115, 149, 217)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y4', // ← attach to right axis
                                        order: 5,
                                    },
                                    {
                                        label: snowAxisLabel,
                                        data: snows,
                                        type: 'bar',
                                        stack: 'combined',

                                        borderColor: 'rgb(202, 213, 235)',
                                        backgroundColor: 'rgb(172, 179, 191,0.7)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y4', // ← attach to right axis
                                        order: 5,
                                    },
                                    {
                                        label: pokrySniegAxisLabel,
                                        data: snowHeights,
                                        borderColor: 'rgb(115, 112, 109)',
                                        backgroundColor: 'rgb(115, 112, 109, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y5', // ← attach to right axis
                                        order: 2,
                                        hidden: true,
                                    },

                );
            }
            if((aggr==='dobowe' && type===true) || (aggr==='miesieczne' && type===true))
            {
                displayAxesDobowe = false;

                const humidities = weatherData.map(item => {
                    const val = parseFloat(item.wilgotnosc_wzgledna);
                    return isNaN(val) ? null : val;
                });
                const clouds = weatherData.map(item => {
                    const val = parseFloat(item.zachmurzenie);
                    return isNaN(val) ? null : val;
                });
                const meanWind = weatherData.map(item => {
                    const val = parseFloat(item.wiatr_srednia_predkosc);
                    return isNaN(val) ? null : val;
                });
                datasetsM.push(
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
                                        hidden: false,
                                    },
                                    {
                                        label: cloudsAxisLabel,
                                        data: clouds,
                                        borderColor: 'rgb(98, 3, 145)',
                                        backgroundColor: 'rgb(98, 3, 145, 0.5)',
                                        borderWidth: 1,
                                        pointRadius: 1,
                                        pointHoverRadius: 2,
                                        tension: 0.1,
                                        spanGaps: false,
                                        pointStyle: 'circle',
                                        yAxisID: 'y2', // ← attach to right axis
                                        order: 5,
                                        hidden: true,
                                    },
                );
            }
            if(aggr === 'miesieczne' && type===false)
            {
                displayAxesDobowe = true;
                displayAxesTerminowe = false;


                const Mintemperatures = weatherData.map(item => {
                    const val = parseFloat(item.min_min_temp_gruntu_mies);
                    return isNaN(val) ? null : val;
                });
                const PowMeanMintemperatures = weatherData.map(item => {
                    const val = parseFloat(item.min_min_temp_powietrza_mies);
                    return isNaN(val) ? null : val;
                });
                const PowMeanMaxtemperatures = weatherData.map(item => {
                    const val = parseFloat(item.max_max_temp_powietrza_mies);
                    return isNaN(val) ? null : val;
                });
                const snowHeights = weatherData.map(item => {
                    const val = parseFloat(item.pokrywa_sniezna_wys);
                    return isNaN(val) ? null : val;
                });
                const rains = weatherData.map(item => {
                    const val = parseFloat(item.sum_sum_opad_10min);
                    return isNaN(val) ? null : val;
                });
                const Maxrains = weatherData.map(item => {
                    const val = parseFloat(item.max_sum_opad_10min);
                    return isNaN(val) ? null : val;
                });

                const PowMintemperatures = weatherData.map(item => {
                    const val = parseFloat(item.abs_min_min_temp_powietrza_mies);
                    return isNaN(val) ? null : val;
                });
                const PowMaxtemperatures = weatherData.map(item => {
                    const val = parseFloat(item.abs_max_max_temp_powietrza_mies);
                    return isNaN(val) ? null : val;
                });

                datasetsM.push(
                    {
                                        label: minPowtmpAxisLabel,
                                        data: PowMintemperatures,
                                        borderColor: 'rgb(200, 160, 107)',
                                        backgroundColor: 'rgb(242, 170, 107, 0.1)',
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
                                        label: maxPowtmpAxisLabel,
                                        data: PowMaxtemperatures,
                                        borderColor: 'rgb(199, 95, 4)',
                                        backgroundColor: 'rgb(199, 95, 4, 0.5)',
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
                                        label: MeanmintmpPowAxisLabel,
                                        data: PowMeanMintemperatures,
                                        borderColor: 'rgb(232, 175, 125)',
                                        backgroundColor: 'rgb(232, 175, 125, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 3,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: MeanmaxtmpPowAxisLabel,
                                        data: PowMeanMaxtemperatures,
                                        borderColor: 'rgb(112, 53, 1)',
                                        backgroundColor: 'rgb(112, 53, 1, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        order: 3,
                                        yAxisID: 'y', // ← attach to left axis
                                        hidden: true,
                                    },
                                    {
                                        label: pokrySniegAxisLabel,
                                        data: snowHeights,
                                        borderColor: 'rgb(115, 112, 109)',
                                        backgroundColor: 'rgb(115, 112, 109, 0.5)',
                                        borderWidth: 2,
                                        pointRadius: 2,
                                        pointHoverRadius: 3,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y5', // ← attach to right axis
                                        order: 2,
                                        hidden: true,
                                    },
                                    {
                                        label: rainAxisLabel,
                                        data: rains,
                                        type: 'bar',
                                        stack: 'combined',
                                        // borderDash: [2, 2],
                                        // pointStyle: 'circle',
                                        // pointRadius: 3,
                                        // pointHoverRadius: 4,
                                        borderColor: 'rgb(1, 48, 135)',
                                        backgroundColor: 'rgb(115, 149, 217)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y4', // ← attach to right axis
                                        order: 3,
                                    },
                                    {
                                        label: maxrainAxisLabel,
                                        data: Maxrains,
                                        type: 'bar',
                                        stack: 'combined',

                                        borderColor: 'rgb(1, 48, 135)',
                                        backgroundColor: 'rgb(37, 104, 230)',
                                        borderWidth: 2,
                                        tension: 0.3,
                                        spanGaps: false,
                                        yAxisID: 'y4', // ← attach to right axis
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
                                                color: 'rgb(256, 150, 120)',
                                            },
                                            grid: {
                                                drawOnChartArea: true // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y1: {
                                            type: 'linear',
                                            display: displayAxesTerminowe,
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
                                            display: displayAxesTerminowe,
                                            position: 'right',
                                            min: 0,
                                            max: 10,
                                            title: {
                                                display: true,
                                                text: 'Zachmurzenie [0-8]',
                                                color: 'rgb(98, 3, 145)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y4: {
                                            type: 'linear',
                                            display: displayAxesDobowe,
                                            position: 'right',
                                            min: 0,
                                            suggestedMax: 1,
                                            title: {
                                                display: true,
                                                text: 'Opad [mm]',
                                                color: 'rgb(40, 90, 150)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y3: {
                                            type: 'linear',
                                            display: displayAxesTerminowe,
                                            position: 'right',
                                            min: 0,
                                            suggestedMax: 8,
                                            title: {
                                                display: true,
                                                text: 'Wiatr [m/s]',
                                                color: 'rgb(51, 51, 51)',
                                            },
                                            grid: {
                                                drawOnChartArea: false // ← optional: don't draw both grid lines
                                            },
                                        },
                                        y5: {
                                            type: 'linear',
                                            display: displayAxesDobowe,
                                            position: 'right',
                                            min: 0,
                                            suggestedMax: 15,
                                            title: {
                                                display: true,
                                                text: 'Wysokość pokrywy śnieżnej [cm]',
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

        Livewire.on('weatherDataUpdated2', (newData) => {
            Alpine.nextTick(() => {
                const weatherData = newData[0][0];
                const aggregation = newData[0][1];
                const type = newData[0][2];

                if (Array.isArray(weatherData) && weatherData.length > 0) {

                    //  Generate label using fresh values
                    titlelabel = getLabelFromContext(newData[1]);
                    // console.log(newData);
                    renderChart(weatherData, aggregation, type);
                } else {
                    console.log('Nie ładuję wykresu – brak danych');
                }
            });
        });

    });
    </script>
</div>
