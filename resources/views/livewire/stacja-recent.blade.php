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
                 <button
                    wire:click="getData"
                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    :disabled="!selectedId || !stations[selectedId]"
                    :class="(!selectedId || !stations[selectedId]) ? 'opacity-60' : ''"
                    :title="!selectedId ? 'Wybierz stację' : (!stations[selectedId] ? 'Nieprawidłowa stacja' : '')"
                >
                    Pokaż dane
                </button>
            </div>
            {{-- <p class="w-1/3 flex content-center">Oficjalne stacje IMGW: </p> --}}

        </div>
        {{-- {{ dd($stations) }} --}}
        {{-- @if ($stationId)
            <p class="mt-2 text-sm">Wybrana stacja ID: {{ $stationId }}</p>
        @endif --}}

        @if ($error)
            <p class="text-red-500">{{ $error }}</p>
        @endif
        <div class="grid grid-cols-4 gap-2">
            {{-- <input type="text" wire:model="stationId" placeholder="Kod stacji" class="border p-2 rounded" /> --}}
            <input type="number" wire:model="year" placeholder="Rok"  class="border p-2 rounded" />
            <input type="number" wire:model="month" placeholder="Miesiąc"  class="border p-2 rounded" />
            {{-- <input type="number" wire:model="day" placeholder="Dzień" class="border p-2 rounded" /> --}}
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
                        },
                        init() {
                            // Populate input with name if stationId is passed in URL
                            if (this.selectedId && this.stations[this.selectedId]) {
                                this.query = this.stations[this.selectedId];
                            }

                        }
                    };
                }
                </script>
    @endpushOnce
</div>
