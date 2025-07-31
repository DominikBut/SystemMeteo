<div>
    @php
         use Carbon\Carbon;
    @endphp
    <div class="p-4 space-y-4 w-full">
        <div class="flex flex-row align-content-center w-full">

            <div x-data="stationSelect({
                        initialId: @entangle('stationId'),
                        stations: {{ Js::from($this->stations) }},
                    })"
                    class="flex flex-col md:grid  md:grid-cols-2 w-full">
                    <div class="flex flex-col justify-between p-4">
                         @if (!empty($stations))
                        <div>
                            <p class="p-1 font-bold text-gray-600">Wybierz stację z {{ count($stations) }} oficjalnych stacji IMGW:</p>
                            <div class="relative w-full">
                            <input
                                type="search"
                                class="rounded border-2 border-gray-200 p-2 w-full"
                                :value="stations[selectedId] ? `${stations[selectedId]}` : (selectedId ?? '')"
                                @input="query = $event.target.value"
                                {{-- to wyzej powoduje ze jak wpisuje to szuka a jak dostaje z url to nie szuka auto jak jest zle dodatkowy czek 2 kroki zamiast 1 --}}
                                @focus="open = true"
                                @blur="setTimeout(() => open = false, 200)"
                                placeholder="Wpisz ID lub nazwę stacji..."
                            >

                            <ul x-cloak x-show="open" class="border-2 rounded border-gray-400 mt-1 bg-white max-h-60 overflow-y-auto shadow z-10 absolute w-full">
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

                            <div class="flex flex-row">
                                <div>
                                    <p x-cloak class="my-2 text-sm w-auto ms-2 font-bold text-lime-600" x-show="selectedId && stations[selectedId]">
                                        Wybrana stacja ID: <span x-text="`${selectedId} – ${stations[selectedId]}`"></span>
                                    </p>
                                    <p x-cloak x-show="selectedId && !stations[selectedId]" class="text-sm font-bold text-red-500 my-2 w-auto">
                                        ❌ Nieprawidłowa stacja (ID: {{ $stationId }}) – brak wśród oficjalnych stacji IMGW.
                                    </p>
                                    <p  x-show="!selectedId && !stations[selectedId]" class="text-sm font-bold text-lime-600 my-2 w-auto">
                                        Wybierz najpierw stację!.
                                    </p>
                                </div>

                            </div>


                        </div>
                        @else
                            <h1><b>Błąd połączenia z API spróbuj ponownie za godzinę, aby móc przeglądać dane (również innych stacji)!</b></h1>
                        @endif
                        <div class="w-full">
                            <div x-data="{ selectedTab: '30min' }" class="w-full bg-slate-50 min-h-56 p-4 flex flex-col justify-between rounded border-2 border-gray-200">
                                <div class="w-full">
                                    <p><b>Wybierz typ agregacji danych:</b></p>
                                    <div x-cloak class="mt-2 flex overflow-x-auto bg-gray-200 md:w-fit rounded" role="tablist" >
                                        <button x-on:click="selectedTab = '30min'; $wire.set('aggregation', '30min'); $wire.loadData()"
                                        x-bind:tabindex="selectedTab === '30min' ? '0' : '-1'"
                                        x-bind:class="selectedTab === '30min' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                        class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                        30-minutowa</button>
                                        <button x-on:click="selectedTab = 'terminowe'; $wire.set('aggregation', 'terminowe'); $wire.loadData()"
                                        x-bind:tabindex="selectedTab === 'terminowe' ? '0' : '-1'"
                                        x-bind:class="selectedTab === 'terminowe' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                        class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                        Terminowa</button>
                                        <button x-on:click="selectedTab = 'dobowe'; $wire.set('aggregation', 'dobowe'); $wire.loadData()"
                                        x-bind:class="selectedTab === 'dobowe' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                        class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                        Dobowa</button>
                                        <button x-on:click="selectedTab = 'miesieczne'; $wire.set('aggregation', 'miesieczne'); $wire.loadData()"
                                        x-bind:tabindex="selectedTab === 'miesieczne' ? '0' : '-1'"
                                        x-bind:class="selectedTab === 'miesieczne' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                        class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                        Miesieczna</button>
                                    </div>
                                </div>

                                <div class="px-2 py-3 bg-gray-50 ">
                                    <div  x-show="selectedTab === '30min'"  role="tabpanel" aria-label="30min" x-cloak>
                                        <p>Wybierz okres odczytu danych:</p>
                                        <div x-cloak class="flex gap-2">
                                            <button wire:click="$set('dateOption', 'today'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class="btn {{ $dateOption === 'today' ? '' : 'opacity-50' }} border border-gray-300 mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                Dzisiaj
                                            </button>

                                            <button wire:click="$set('dateOption', 'yesterday'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class="btn {{ $dateOption === 'yesterday' ? '' : 'opacity-50' }} border border-gray-300 mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                Wczoraj
                                            </button>

                                            <button wire:click="$set('dateOption', '7days'); $wire.loadData()"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                class="btn {{ $dateOption === '7days' ? '' : 'opacity-50' }} border border-gray-300 mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                7 ostatnich dni
                                            </button>
                                        </div>
                                    </div>
                                    <div x-cloak x-show="selectedTab === 'terminowe'"  role="tabpanel" aria-label="terminowe">

                                        <p>Wybierz zakres dni odczytu danych:</p>
                                                <div class="flex gap-2">

                                                     {{-- minDate below set to earliest data that i have --}}
                                                     <div class="flex flex-col md:flex-row gap-2" x-data="{
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
                                                                const yesterdayStr = yesterday.toISOString().split('T')[0];

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
                                                        <div class="flex items-center gap-4">
                                                            <div class="flex flex-col">
                                                                <label for="start" class="text-sm font-medium text-gray-700">Data początkowa:</label>
                                                                <input type="date" id="start"
                                                                    x-model="start" x-on:change="validateRange()"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="border border-gray-300 px-2 py-1 rounded" />
                                                            </div>

                                                            <div class="flex flex-col">
                                                                <label for="end" class="text-sm font-medium text-gray-700">Dzień Końcowy:</label>
                                                                <input type="date" id="end"
                                                                    x-model="end" x-on:change="validateRange()"
                                                                    :min="endMin"
                                                                    :max="endMax"
                                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                                    class="border border-gray-300 px-2 py-1 rounded" />
                                                            </div>
                                                        </div>
                                                        <button
                                                            wire:click="loadData"
                                                            class="mt-5 bg-blue-500 text-white px-4 py-2 border border-gray-300 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                                            :disabled="!selectedId || !stations[selectedId] || !query"
                                                            :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                            :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                        >
                                                            Odśwież dane
                                                        </button>
                                                    </div>

                                                </div>

                                    </div>


                                    <div x-cloak x-show="selectedTab === 'dobowe'"  role="tabpanel" aria-label="dobowe">
                                        <p>Wybierz miesiąc odczytu danych:</p>
                                                    <div class="flex gap-2">
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
                                                            <div class="flex items-center gap-4">
                                                                <div class="flex flex-col">
                                                                    <label for="start" class="text-sm font-medium text-gray-700">Miesiąc z roku:</label>
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

                                    <div x-cloak x-show="selectedTab === 'miesieczne'" role="tabpanel" aria-label="miesieczne">
                                        <p>Wybierz rok odczytu danych:</p>
                                        <div class="flex gap-2">
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
                                                <div class="flex items-center gap-4">
                                                    <div class="flex flex-col">
                                                        <label for="year" class="text-sm font-medium text-gray-700">Rok:</label>
                                                        <select
                                                            id="year"
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
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col justify-end">

                        @if (!empty($stationData))

                            <div  class=" p-4 bg-slate-50 border-2 rounded border-gray-200 text-sm min-h-72">
                                <p class="font-bold mb-2 text-base truncate text-lime-600">
                                    Najnowsze dane meteo dla stacji ID {{ $stationData['kod_stacji'] }} ({{ $stationData['nazwa_stacji'] ?? '-' }})
                                </p>

                                <p class="text-xs text-gray-500 mb-2">
                                    Dane pobrano: {{ $askTime ?? '–' }}
                                </p>

                                <ul class="grid grid-cols-2 gap-4 text-sm">
                                    <li>
                                        <strong>Temp. gruntu:</strong> {{ $stationData['temperatura_gruntu'] ?? '-' }} °C
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['temperatura_gruntu_data']) ? Carbon::parse($stationData['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <strong>Wilgotność:</strong> {{ $stationData['wilgotnosc_wzgledna'] ?? '-' }} %
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
                                        <strong>Wiatr max:</strong> {{ $stationData['wiatr_predkosc_maksymalna'] ?? '-' }} km/h
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
                            <div class="relative p-4 bg-slate-50 border-gray-200 border-2 rounded text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full bg-slate-50 flex flex-col justify-center">
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
                            <div class="relative p-4 bg-slate-50 border-gray-200 border-2 rounded text-sm min-h-72 text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full bg-slate-50 flex flex-col justify-center">
                                    <p class="w-auto h-auto m-auto animate-pulse">Oczekiwanie na wybór stacji...</p>
                                </div>
                            </div>
                        @endif
                    </div>
            </div>

        </div>

        <div class=" bg-slate-50 m-4 p-4 rounded border-2 border-gray-200">

            @if (!empty($stations[$stationId]) )
                <h1 class="text-xl pb-2 font-bold">Wyszukano dane meteorologiczne dla stacji: {{ $stationId.' - '.$stations[$stationId] }}</h1>
            @else
                <h1 class="text-xl pb-2 font-bold text-slate-50"> Brak wybranej stacji</h1>
            @endif

            @if(empty($this->weatherData))
                <div class="relative w-full h-[24rem] flex justify-center p-4  bg-gray-200 border-2 rounded border-gray-300">
                        <div wire:loading.remove class="absolute left-0 top-0 w-full h-full flex flex-col justify-center text-center ">
                            @if (!empty($stations[$stationId]))
                                <p   class="text-xl font-bold text-red-500">Brak aktualnych danych z tego okresu dla stacji {{ $stationId.' - '.$stations[$stationId] }}</p>
                            @else
                                 <p   class="text-xl font-bold animate-pulse">Oczekiwanie na wybór stacji...</p>
                            @endif
                        </div>
                        <div wire:loading class="absolute top-0 left-0 w-full h-full z-20 animate-pulse bg-gray-200 ">
                        <div class="w-full h-full flex flex-col justify-center animate-pulse text-center">
                            <p class="text-xl font-bold animate-pulse">Ładowanie...</p>
                        </div>
                </div>
                </div>
            @else
            <div class="relative w-full h-[24rem] flex justify-center p-4 border-2 rounded border-gray-300 ">
                <div wire:loading class="absolute top-0 left-0 w-full h-full z-20 animate-pulse bg-gray-200 ">
                    <div class="w-full h-full flex flex-col justify-center animate-pulse text-center">
                        <p class="text-xl font-bold animate-pulse">Ładowanie...</p>
                    </div>
                </div>
                <canvas id="weatherChart" wire:loading.remove class="w-full h-full z-0 "></canvas>
            </div>

                    <b>Jeżeli id się różnią nie znaleziono stacji o podanym id a wyszukano stację o tej samej nazwie <br>(niektore stacje mogą posiadać nowe id nie będące na liście wyboru stacji nie wiadomo czemu)</b>
                    <br>  Odczytano dane dla: {{ $this->weatherData[0]['kod_stacji'].' '. $this->weatherData[0]['nazwa_stacji'] }}

                    @switch($this->aggregation)
                            @case('30min')

                                    <div class="overflow-hidden w-full overflow-x-auto overflow-y-auto rounded-xl border border-gray-300 max-h-screen">
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
                            @case('terminowe')
                             z okresu od {{ $this->terminoweStartDate .' do '.  $this->terminoweEndDate  }}
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
                                    z roku i miesiąca {{ $this->doboweDate }}
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
    document.addEventListener('livewire:init', () => {

         var chartInstance = null;
        if (chartInstance) {
                chartInstance.destroy(); // Clear existing chart
            }
        function renderChart(weatherData) {
            if (chartInstance) {
                chartInstance.destroy(); // Clear existing chart
            }
            const labels = weatherData.map(item => item.temperatura_gruntu_data ?? item.data);
            const temperatures = weatherData.map(item => parseFloat(item.temperatura_gruntu ?? item.mean_temp_gruntu_dobowa ?? item.mean_mean_temp_gruntu_mies) || null);

            const ctx = document.getElementById('weatherChart');
            if (!ctx) {
                    console.error('Brak canvasu!');
                    return;
            }


            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Temperatura gruntu (°C)',
                        data: temperatures,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.3,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { title: { display: true, text: 'Czas' }},
                        y: { title: { display: true, text: 'Temperatura (°C)' }}
                    }

                }
            });
            console.log('Odświeżono wykres');

        }




        Livewire.on('weatherDataUpdated', (newData) => {
            Alpine.nextTick(() => {
                if(Array.isArray(newData[0]) && newData[0].length > 0){
                    const data = newData[0] ?? @json($this->sortedWeatherData);

                    renderChart(data);
                }
                else{
                    console.log('Nie ładuję wykresu');
                }
            });



        });
    });
</script>

</div>
