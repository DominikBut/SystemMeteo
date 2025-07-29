<div>
    <div class="p-4 space-y-4 w-auto">
        {{-- <label for="station-select" class="block font-medium">Wybierz stację:</label>
        <select id="station-select" wire:model.live="stationId" class="border p-2 mt-1">
            <option value="">-- wybierz --</option>
            @foreach ($this->stations as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select> --}}
        {{-- <div>
                            30-minutowa agregacja danych
                            <select wire:model="dateOption"
                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''">
                                <option value="today">Dzisiaj</option>
                                <option value="yesterday">Wczoraj</option>
                                <option value="7days">7 dni</option>
                            </select>
                        </div> --}}
        <div class="flex flex-row space-x-4 align-content-center">

            <div x-data="stationSelect({
                        initialId: @entangle('stationId'),
                        stations: {{ Js::from($this->stations) }},
                    })"
                    class="grid grid-cols-2">
                    <div>
                        <p class="p-1 font-bold">Wyszukaj stację z {{ count($stations) }} oficjalnych stacji IMGW (refresh 7 dni):</p>
                        <input
                            type="text"

                            class="border p-2 w-full"
                            :value="stations[selectedId] ? `${stations[selectedId]}` : (selectedId ?? '')"
                            @input="query = $event.target.value"
                            {{-- to wyzej powoduje ze jak wpisuje to szuka a jak dostaje z url to nie szuka auto jak jest zle dodatkowy czek 2 kroki zamiast 1 --}}
                            @focus="open = true"
                            @blur="setTimeout(() => open = false, 200)"
                            placeholder="Wpisz ID lub nazwę stacji..."
                        >

                        <ul x-cloak x-show="open" class="border mt-1 bg-white max-h-60 overflow-y-auto shadow z-10 absolute w-[32rem]">
                            <template x-for="[id, name] in filtered()" :key="id">
                                <li
                                    class="px-4 py-2 hover:bg-gray-200 cursor-pointer"
                                    @click="select(id)"
                                    x-text="`${id} – ${name}`"
                                ></li>

                            </template>
                            <li x-cloak class="px-4 py-2 text-sm text-gray-500 border-t bg-gray-50 sticky bottom-0 z-10">
                                Dostępnych opcji: <span x-text="filtered().length"></span>
                            </li>
                        </ul>

                       <p x-cloak class="my-2 text-sm" x-show="selectedId && stations[selectedId]">
                            Wybrana stacja ID: <span x-text="`${selectedId} – ${stations[selectedId]}`"></span>
                        </p>
                        <p x-cloak x-show="selectedId && !stations[selectedId]" class="text-sm text-red-500 my-2">
                            ❌ Nieprawidłowa stacja (ID: {{ $stationId }}) – brak wśród oficjalnych stacji IMGW.
                        </p>
                    </div>
                    <div class="p-4">
                        <p x-cloak wire:loading class="w-1/3 flex content-center">Oficjalne stacje IMGW: </p>
                    </div>

                        <div class="col-span-2">
                            <div x-data="{ selectedTab: '30min' }" class="w-full">
                                <div  class="flex gap-2 overflow-x-auto border-b border-slate-30 bg-stone-50" role="tablist" >
                                    <button x-on:click="selectedTab = '30min'; $wire.set('aggregation', '30min'); $wire.loadData()"
                                    x-bind:tabindex="selectedTab === '30min' ? '0' : '-1'"
                                    x-bind:class="selectedTab === '30min' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                    class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                    30min</button>
                                    <button x-on:click="selectedTab = 'terminowe'; $wire.set('aggregation', 'terminowe'); $wire.loadData()"
                                    x-bind:tabindex="selectedTab === 'terminowe' ? '0' : '-1'"
                                    x-bind:class="selectedTab === 'terminowe' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                    class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                    terminowe</button>
                                    <button x-on:click="selectedTab = 'dobowe'; $wire.set('aggregation', 'dobowe'); $wire.loadData()"
                                    x-bind:class="selectedTab === 'dobowe' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                    class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                    dobowe</button>
                                    <button x-on:click="selectedTab = 'miesieczne'; $wire.set('aggregation', 'miesieczne'); $wire.loadData()"
                                    x-bind:tabindex="selectedTab === 'miesieczne' ? '0' : '-1'"
                                    x-bind:class="selectedTab === 'miesieczne' ? 'font-bold text-blue-700 border-b-2 border-blue-700 ' : 'text-slate-700 font-medium  hover:border-b-2 hover:border-b-slate-800 hover:text-black'"
                                    class="h-min px-4 py-2 text-sm" type="button" role="tab"  >
                                    miesieczne</button>
                                </div>
                                <div class="px-2 py-4 bg-gray-50">
                                    <div x-cloak x-show="selectedTab === '30min'"  role="tabpanel" aria-label="30min">
                                        <b><a href="#" class="underline">30min</a></b> tab is selected
                                        <div class="flex gap-2">
                                            <button wire:click="$set('dateOption', 'today'); $wire.loadData()"
                                                class="btn {{ $dateOption === 'today' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                Dzisiaj
                                            </button>

                                            <button wire:click="$set('dateOption', 'yesterday'); $wire.loadData()"
                                                class="btn {{ $dateOption === 'yesterday' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                Wczoraj
                                            </button>

                                            <button wire:click="$set('dateOption', '7days'); $wire.loadData()"
                                                class="btn {{ $dateOption === '7days' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                                7 dni
                                            </button>
                                        </div>
                                    </div>
                                    <div x-cloak x-show="selectedTab === 'terminowe'"  role="tabpanel" aria-label="terminowe">
                                        <b>Dane można załadować tylko dla dni z tego samego miesiąca!</b>

                                                <div class="flex gap-2 mt-4">

                                                     {{-- minDate below set to earliest data that i have --}}
                                                     <div x-data="{
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
                                                                <label for="start" class="text-sm font-medium text-gray-700">Start date:</label>
                                                                <input type="date" id="start"
                                                                    x-model="start" x-on:change="validateRange()"
                                                                    :min="minDate"
                                                                    :max="maxDate"
                                                                    class="border px-2 py-1 rounded" />
                                                            </div>

                                                            <div class="flex flex-col">
                                                                <label for="end" class="text-sm font-medium text-gray-700">End date:</label>
                                                                <input type="date" id="end"
                                                                    x-model="end" x-on:change="validateRange()"
                                                                    :min="endMin"
                                                                    :max="endMax"
                                                                    class="border px-2 py-1 rounded" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <button
                                                    wire:click="loadData"
                                                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                >
                                                    Załaduj dane
                                                </button>


                                    </div>


                                    <div x-cloak x-show="selectedTab === 'dobowe'"  role="tabpanel" aria-label="dobowe">
                                        <b><a href="#" class="underline">dobowe</a></b> tab is selected
                                                    <div class="flex gap-2 mt-4">
                                                        {{-- minDate below set to earliest data that i have --}}
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
                                                                    <label for="start" class="text-sm font-medium text-gray-700">Date for data:</label>
                                                                    <input type="month" id="start"
                                                                        x-model="start" x-on:change="validateRange()"
                                                                        min="2025-07"
                                                                        :max="maxDate"
                                                                        class="border px-2 py-1 rounded" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <button
                                                        wire:click="loadData"
                                                        class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                                        :disabled="!selectedId || !stations[selectedId] || !query"
                                                        :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                                                        :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                                                    >
                                                        Załaduj dane
                                                    </button>


                                        </div>

                                    </div>


                                    <div x-cloak x-show="selectedTab === 'miesieczne'" role="tabpanel" aria-label="miesieczne">
                                        <b><a href="#" class="underline">miesieczne</a></b> tab is selected
                                    </div>
                                </div>
                            </div>
                        </div>

            </div>


        </div>
        {{-- {{ dd($stations) }} --}}
        {{-- @if ($stationId)
            <p class="mt-2 text-sm">Wybrana stacja ID: {{ $stationId }}</p>
        @endif --}}

        @if ($error)
            <p class="text-red-500">{{ $error }}</p>
        @endif
        @if ($info)
            <p class="text-red-500">{{ $info }}</p>
        @endif

        <div>

            <h3>Dane pogodowe dla stacji: {{ $stationId }}
                </h3>      {{-- "kod_stacji": "249190190",
        "nazwa_stacji": "MAKÓW PODHALAŃSKI",
        "lon": "19.688056",
        "lat": "49.725833", --}}

        </div>
        <div class="w-full bg-slate-50">

            @if(empty($this->weatherData))
                        <p>Brak danych do odczytu</p>
                    @else
                    <b>Jeżeli id się różnią nie znaleziono stacji o podanym id a wyszukano stację o tej samej nazwie <br>(niektore stacje mogą posiadać nowe id nie będące na liście wyboru stacji nie wiadomo czemu)</b>
                    <br>  Odczytano dane dla: {{ $this->weatherData[0]['kod_stacji'].' '. $this->weatherData[0]['nazwa_stacji'] }} {{ $this->aggregation==='terminowe' ? ' z okresu od ' .$this->terminoweStartDate .' do '.  $this->terminoweEndDate : '' }}
                        @switch($this->aggregation)
                            @case('30min' or 'terminowe')
                                    <div class="overflow-hidden w-full overflow-x-auto overflow-y-auto rounded-xl border border-slate-300 max-h-screen">
                                        <table class="w-full text-left text-sm ">
                                            <thead class="border-b border-slate-300 bg-slate-100 text-sm text-black ">
                                                <tr class="even:bg-blue-700/5 ">
                                                    <th class="p-4">temperatura_gruntu</th>
                                                    <th class="p-4 cursor-pointer" wire:click="setSort('temperatura_gruntu_data')">
                                                        temperatura_gruntu_data
                                                        @if($sortBy === 'temperatura_gruntu_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                    </th>
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

                                                        <td class="p-4">{{ $data['temperatura_gruntu'] ?? '-' }}</td>
                                                        <td class="p-4">{{ $data['temperatura_gruntu_data'] ?? '-' }}</td>
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
                                                    <th class="p-4">temperatura_gruntu</th>
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
                            @case('miesieczne')
                                    z roku {{ $this->miesieczneDate }}
                                @break
                            @default

                        @endswitch

                    @endif

        </div>




        {{-- @if ($error)
            <div class="text-red-600">{{ $error }}</div>
        @elseif(!empty($stationData))
        super
        <div class="bg-gray-100 p-4 rounded"><strong>Stacja:</strong> {{ $stationData[0][1] }}</div>
            @foreach ( $stationData as $Data)
                <div class="bg-gray-100 p-4 rounded">

                    <div><strong>Data:</strong> {{ $Data[2] }}-{{ $Data[3] }}-{{ $Data[4] }}</div>
                    <div><strong>Tmax:</strong> {{ $Data[5] ?? 'brak' }}</div>
                    <div><strong>Tmin:</strong> {{ $Data[7] ?? 'brak' }}</div>
                    <div><strong>Śr. temperatura:</strong> {{ $Data[9] ?? 'brak' }}</div>
                    <div><strong>Opad:</strong> {{ !empty($Data[13]) ? $Data[13].' [mm] '.$Data[15].' '.$Data[16].' [cm]' : 'brak' }}</div>
                </div>
            @endforeach
        @endif --}}
    </div>
    @pushOnce('scripts2')
                <script>
                function stationSelect({ initialId, stations }) {
                    return {
                        open: false,
                        query: '',
                        selectedId: initialId,
                        stations,
                        filtered() {
                            const q = this.query.toLowerCase();
                            return Object.entries(this.stations).filter(([id, name]) =>
                                id.includes(q) || name.toLowerCase().includes(q)
                            );
                        },
                        select(id) {
                            this.selectedId = id;
                            this.query = this.stations[id] ? `${this.stations[id]}` : id;
                            this.open = false;
                             this.$wire.set('stationId', id).then(() => {
                                if (this.stations[id]) {
                                    this.$wire.call('loadData');
                                    this.$wire.set('dateOption', 'today');
                                } else {
                                    this.$wire.set('weatherData', []); // Clear data if ID is invalid
                                    this.$wire.set('dateOption', 'today');
                                }
                            });
                            this.$wire.set('stop', false);

                            console.log(id);
                        },
                        init() {
                            // Populate input with name if stationId is passed in URL
                            if (this.selectedId && this.stations[this.selectedId]) {
                                this.query = this.stations[this.selectedId];
                            }else{
                                this.$wire.set('stop', true);
                            }

                        }
                    };
                }
                </script>
    @endpushOnce
</div>
