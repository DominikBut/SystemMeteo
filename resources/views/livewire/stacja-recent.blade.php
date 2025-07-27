<div>
    <div class="p-4 space-y-4 w-auto">
        {{-- <label for="station-select" class="block font-medium">Wybierz stację:</label>
        <select id="station-select" wire:model.live="stationId" class="border p-2 mt-1">
            <option value="">-- wybierz --</option>
            @foreach ($this->stations as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select> --}}
        <div class="flex flex-row space-x-4 align-content-center">

            <div x-data="stationSelect({
                        initialId: @entangle('stationId'),
                        stations: {{ Js::from($this->stations) }},
                    })"
                    class="w-[32rem]">
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
                        <div>
                            <select wire:model="dateOption"
                                    :disabled="!selectedId || !stations[selectedId] || !query"
                                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''">
                                <option value="today">Dzisiaj</option>
                                <option value="yesterday">Wczoraj</option>
                                <option value="7days">7 dni</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                wire:click="$set('dateOption', 'today')"
                                class="btn {{ $dateOption === 'today' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                Dzisiaj
                            </button>

                            <button
                                wire:click="$set('dateOption', 'yesterday')"
                                class="btn {{ $dateOption === 'yesterday' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                Wczoraj
                            </button>

                            <button
                                wire:click="$set('dateOption', '7days')"
                                class="btn {{ $dateOption === '7days' ? '' : 'opacity-50' }} mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                7 dni
                            </button>
                        </div>
                 {{-- <button
                    wire:click="loadData"
                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    :disabled="!selectedId || !stations[selectedId] || !query"
                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                >
                    Pokaż dane
                </button> --}}

            </div>
            <p wire:loading class="w-1/3 flex content-center">Oficjalne stacje IMGW: </p>

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
        {{-- <div class="grid grid-cols-4 gap-2">
            <input type="text" wire:model="stationId" placeholder="Kod stacji" class="border p-2 rounded" />
            <input type="number"  placeholder="Rok"  class="border p-2 rounded" />
            <input type="number"  placeholder="Miesiąc"  class="border p-2 rounded" />
             <input type="number" wire:model="day" placeholder="Dzień" class="border p-2 rounded" />
        </div>
         --}}
        <div>

            <h3>Dane pogodowe dla stacji: {{ $stationId }}
                </h3>      {{-- "kod_stacji": "249190190",
        "nazwa_stacji": "MAKÓW PODHALAŃSKI",
        "lon": "19.688056",
        "lat": "49.725833", --}}

        </div>
        <div class="w-full bg-slate-50">
            {{-- <div class="my-2 flex gap-2 items-center">
            <label for="sortBy">Sortuj wg:</label>
            <select wire:model="sortBy" class="border rounded px-2 py-1">
                <option value="">Brak</option>
                <option value="temperatura_gruntu_data">Data temperatury</option>
                <option value="opad_10min_data">Data opadu</option>

            </select>

            <select wire:model="sortDirection" class="border rounded px-2 py-1">
                <option value="asc">Rosnąco</option>
                <option value="desc">Malejąco</option>
            </select>
        </div> --}}
            @if(empty($this->weatherData))
                        <p>Brak danych do odczytu</p>
                    @else
                    <b>Jeżeli nie znaleziono stacji o podanym id wyszukano stację o tej samej nazwiwie <br>(niektore stacje mogą posiadać nowe id nie będące na liście wyboru stacji nie wiadomo czemu)</b>
                    <br>  Odczytano dane dla: {{ $this->weatherData[1]['kod_stacji'].' '. $this->weatherData[1]['nazwa_stacji'] }}
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
                                    <th class="p-4">opad_10min_data</th>
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
