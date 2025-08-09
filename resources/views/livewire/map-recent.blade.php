<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    {{-- @section('list')
        <div>
            @if (!empty($stationData))

                @foreach (collect($stationData)->sortBy('nazwa_stacji') as $stacja)

                                                <div
                                                    class="station-list-item cursor-pointer hover:bg-gray-200 px-2 py-1 border-b"
                                                    data-kod="{{ $stacja->kod_stacji }}" onclick="focusStation('{{ $stacja->kod_stacji }}')"
                                                >
                                                    {{ $stacja->nazwa_stacji }}
                                                </div>
                @endforeach
            @endif
        </div>
    @endsection --}}

    @php
         use Carbon\Carbon;
    @endphp
    <div class="p-2 space-y-4 w-full">


        <div class="bg-white rounded-md shadow-sm border">

            <h1 class=" pt-3 px-2 sm:px-3 pb-2 text-center text-sm sm:text-xl font-bold tracking-wider">Przeglądasz najnowsze dane meteorologiczne stacji pogodowych dostępne przez API IMGW</h1>
            <div class="flex flex-col justify-between w-full">
                                <div    x-data="{
                                        selectedTab: 'temp',
                                        selectTab(tab, $el) {
                                            this.selectedTab = tab;
                                            $wire.set('option', tab);

                                        }

                                    }"
                                    x-init="$watch('$wire.option', value => {
                                        toggleLayer(value);
                                    })"

                                    class="w-full p-1 pb-2 flex flex-col justify-between border-b">

                                        {{-- <p class="ms-2 text-sm text-gray-500 font-medium">Wybierz rodzaj danych wyświetlanych na mapie:</p> --}}
                                        <div
                                            class="sm:text-sm overflow-x-auto flex flex-row mx-3 items-center w-auto min-h-10 md:min-h-14 2xl:min-h-10 text-sm font-medium px-1  text-gray-500 bg-gray-200 rounded-md "
                                            role="tablist">

                                            <!-- Buttons -->
                                            <button data-tab="temp" x-on:click="selectTab('temp');" wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'temp' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Temp. powietrza [°C]</button>

                                            <button data-tab="tempg" x-on:click="selectTab('tempg');" wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'tempg' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-7 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Temp. gruntu [°C]</button>

                                            <button data-tab="hum" x-on:click="selectTab('hum');" wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'hum' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Wilg. względna [%]</button>

                                            <button data-tab="wind" x-on:click="selectTab('wind'); " wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'wind' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Wiatr śr. prędkość [m/s]</button>

                                            <button data-tab="rain" x-on:click="selectTab('rain'); " wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'rain' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Opad deszczu (10 min) [mm]</button>

                                            <button data-tab="all" x-on:click="selectTab('all');" wire:loading.attr="disabled" wire:target="getStationData"
                                            :class="selectedTab === 'all' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Lokalizacje stacji</button>
                                        </div>
                                </div>
                    </div>
            <div class="grid sm:grid-cols-4">
                <div class="sm:col-span-3 ">

                    <div wire:ignore class="relative w-auto h-[48rem]  flex justify-center p-4   shadow-sm border">
                        <!-- Map (initially hidden or placed underneath) -->
                        <div class="absolute top-0 left-0 w-full h-full z-10 animate-pulse bg-gray-300  shadow-sm  "></div>
                        <div wire:ignore id="map" class="absolute top-0 left-0 w-full h-full z-20  shadow-sm "></div>
                    </div>

                </div>
                <div class="sm:col-span-1 border-b max-h-[20rem] sm:max-h-[48rem] flex flex-col ">
                    <div class="text-sm ps-2  py-1 font-medium bg-blue-50">Dostępne stacje:</div>
                    <hr>
                    <div
                        x-data="{ stationId: $wire.entangle('stationId') }"
                        class=" text-xs overflow-y-auto  w-full max-h-full bg-slate-50">

                        @if (!empty($stations))
                            @foreach (collect($stations)->sortBy('nazwa_stacji') as $stacja)
                                <div
                                    class="station-list-item cursor-pointer hover:bg-gray-200 px-2 py-1 border"
                                    :class="{ 'bg-slate-100 font-bold border-2': stationId === '{{ $stacja['kod_stacji'] }}' }"
                                    data-kod="{{ $stacja['kod_stacji'] }}"
                                    x-on:click="stationId = '{{ $stacja['kod_stacji'] }}'; focusStation('{{ $stacja['kod_stacji'] }}')"
                                >
                                    <p class=" truncate  hover:underline" :class="{ 'py-1 text-sm text-blue-500': stationId === '{{ $stacja['kod_stacji'] }}' }">{{ $stacja['nazwa_stacji'].' ['. $stacja['kod_stacji'] .']' }}</p>
                                    <!-- Show links if this is the selected station -->
                                    <template x-if="stationId === '{{ $stacja['kod_stacji'] }}'">
                                        <div class="ps-2 text-end flex flex-col justify-end ">
                                            <div class="pb-1 w-full text-end"> Zobacz dane:</div>

                                            <div class="text-xs flex flex-row gap-2 justify-end">
                                                <a class="hover:underline text-gray-500 text-nowrap"
                                                href="{{ route('stacja_archive').'/?id='.$stacja['kod_stacji'] }}">
                                                    Dane archiwalne
                                                </a>
                                                <a class="hover:underline text-blue-500 text-nowrap"
                                                href="{{ route('stacja_recent').'/?id='.$stacja['kod_stacji'] }}">
                                                    Dane bieżące
                                                </a>

                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <hr>
                    <div  class="text-sm ps-2   py-1 font-medium bg-blue-50">Stacje z odczytami: {{ collect($stations)->count(0)  }}</div>
                </div>

            </div>
             <div class="flex flex-col sm:flex-row justify-center sm:justify-between">
            @if (!empty($stationData) )
                <p wire:loading.remove wire:target="getStationData" class="text-xs ms-2 py-2  text-gray-500">Dane meteorologiczne wszystkich dostępnych stacji pogodowych pobrano z API IMGW: <span class="text-lime-600">{{ Carbon::parse($askTime)->format('H:i:s d.m.Y') ?? '–' }}</span></p>
            @else
                <p wire:loading.remove wire:target="getStationData" class="text-xs ms-2 py-2  text-red-500">Błąd pobierania danych z API IMGW... </p>

            @endif
                <p wire:loading wire:target="getStationData" class="text-xs ms-2 py-2  animate-pulse">Ładowanie...</span></p>
                <p wire:loading wire:target.except="setSort, getStationDataid" class="hidden md:block text-sm ps-2 px-4 py-1 font-medium  animate-pulse">Aktualizowanie...</p>
            </div>
        </div>
        <div class="p-4 bg-white rounded-md shadow-sm text-xs sm:text-sm min-h-72 w-full">
                        @if (!empty($stationId))
                            <div wire:loading.remove wire:target="getStationData">
                                <p class="font-bold  text-sm sm:text-base text-lime-600">
                                    Najnowsze dane meteo dla stacji ID: <span class="text-nowrap">{{ $stationDataId['kod_stacji'] }} ({{ $stationDataId['nazwa_stacji'] ?? '-' }})</span>
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
                                            <strong class=" w-min sm:w-auto">Temp. powietrza:</strong> {{ $stationDataId['temperatura_powietrza'] ?? '-' }} °C
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['temperatura_powietrza_data']) ? Carbon::parse($stationDataId['temperatura_powietrza_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Temp. gruntu:</strong> {{ $stationDataId['temperatura_gruntu'] ?? '-' }} °C
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['temperatura_gruntu_data']) ? Carbon::parse($stationDataId['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wilg. względna:</strong> {{ $statistationDataIdonData['wilgotnosc_wzgledna'] ?? '-' }} %
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['wilgotnosc_wzgledna_data']) ? Carbon::parse($stationDataId['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 12h16M4 6h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr śr.:</strong> {{ $stationDataId['wiatr_srednia_predkosc'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationData['wiatr_srednia_predkosc_data']) ? Carbon::parse($stationDataId['wiatr_srednia_predkosc_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wiatr maks.:</strong> {{ $stationDataId['wiatr_predkosc_maksymalna'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($stationDataId['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        @php
                                            $rotation = is_numeric($stationDataId['wiatr_kierunek']) ? $stationDataId['wiatr_kierunek'] : 0;
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
                                                {{ $stationDataId['wiatr_kierunek'] ?? '-' }} °
                                                [
                                                <div class="inline-block transform font-extrabold px-1" style="rotate: {{ $rotation }}deg;">
                                                    ↓
                                                </div>]
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['wiatr_kierunek_data']) ? Carbon::parse($stationDataId['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4l16 16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wiatr poryw <span class="text-nowrap">(10 min):</span></strong> {{ $stationDataId['wiatr_poryw_10min'] ?? '-' }} m/s
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['wiatr_poryw_10min_data']) ? Carbon::parse($stationDataId['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4v16h16V4H4zM9 9h6v6H9z" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Opad <span class="text-nowrap">(10 min):</span></strong> {{ $stationDataId['opad_10min'] ?? '-' }} mm
                                        </p>

                                        <div class="text-xs text-gray-500">
                                            {{ !empty($stationDataId['opad_10min_data']) ? Carbon::parse($stationDataId['opad_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        @elseif(!empty($error))
                            <div wire:loading.remove wire:target="getStationDataid"  class="relative h-full w-full flex flex-col justify-center text-center ">
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
                            <div wire:loading.remove wire:target="getStationDataid"  class="relative h-full w-full flex flex-col justify-center text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center">
                                    <p class="w-full h-full m-auto animate-pulse">Oczekiwanie na wybór stacji...</p>
                                </div>
                            </div>
                        @endif
                        <div wire:loading wire:target="getStationDataid"  class="relative h-full text-center ">
                                <div class="absolute top-0 left-0 font-bold h-full w-full flex flex-col justify-center">
                                    <p class="w-full h-full m-auto animate-pulse">Pobieranie danych...</p>
                                </div>
                        </div>
        </div>
        <div class="p-4 bg-white rounded-md shadow-sm">

            @php
                $allNull = collect($stationData)->every(fn($item) =>
                    collect($item)->except(['kod_stacji', 'nazwa_stacji', 'lon', 'lat', 'data'])
                                ->filter(fn($val) => !is_null($val))
                                ->isEmpty()
                );
            @endphp
            @if(empty($stationData) || $allNull)
            <div class="relative w-full h-[32rem] flex justify-center p-4 bg-white rounded-md shadow-sm border">
                        <div wire:loading.remove wire:target.except="setSort" class="absolute left-0 top-0 w-full h-full flex flex-col justify-center text-center ">

                                 <p class="text-sm sm:text-xl font-bold animate-pulse">Oczekiwanie na wybór stacji...</p>

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

            <div>
                <div wire:loading wire:target="getStationData"  class="ms-2 text-xs sm:text-sm pb-4 font-semibold text-gray-500 flex flex-row justify-between">
                Ładowanie...
                </div>
                <div wire:loading.remove wire:target="getStationData"  class="ms-2 text-xs sm:text-sm pb-4 font-semibold text-gray-500 flex flex-row justify-between">
                Statystyki odczytanych danych:
                </div>
                <div class="h-44 text-xs bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto ">
                    <table wire:loading.remove wire:target="getStationData"  class="w-full text-left h-full">

                                <thead class="border-b-2 border-gray-300  text-black text-center h-auto">
                                    <th class="p-2 text-gray-600">
                                        <span class="text-nowrap">Typ wartości</span>
                                    </th>
                                    <th class="p-2  text-gray-600">
                                        Temp. <span class="text-nowrap">gruntu [°C]</span>
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
                                        <td>{{ $minMaxStats['temperatura_powietrza']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['max'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['max'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-green-50 text-center h-auto">
                                        <td class="p-2 text-gray-700">ŚR.</td>
                                        <td>{{ $minMaxStats['temperatura_gruntu']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['temperatura_powietrza']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['avg'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['avg'] ?? '-' }}</td>
                                    </tr>
                                    <tr class="bg-blue-50 text-center font-semibold h-auto">
                                        <td class="p-2 text-gray-700">MIN.</td>
                                        <td>{{ $minMaxStats['temperatura_gruntu']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['temperatura_powietrza']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wilgotnosc_wzgledna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['opad_10min']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_srednia_predkosc']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_predkosc_maksymalna']['min'] ?? '-' }}</td>
                                        <td>{{ $minMaxStats['wiatr_poryw_10min']['min'] ?? '-' }}</td>
                                    </tr>
                                </tbody>

                    </table>
                </div>
            </div>
            <div wire:loading.remove wire:target="getStationData, setSort"  class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">

                Zestawienie tabelaryczne pobranych danych meteorologicznych wszystkich stacji:

            </div>
            <div wire:loading wire:target="setSort, setSort" class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">
                Sortowanie...
            </div>
            <div wire:loading wire:target="getStationData"  class="bg-white rounded-md shadow-sm border border-gray-300 min-h-32 w-full">

            </div>
            <div wire:loading.remove wire:target="getStationData"  class="min-h-32">

                                    <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">
                                        <table class="w-full text-left ">
                                            <thead class="h-16 border-b-2 border-gray-300 bg-slate-100  text-black ">
                                                <tr class="even:bg-blue-600/5 text-wrap text-center text-xs ">
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 text-gray-600"
                                                        wire:click="setSort('kod_stacji')">
                                                        Zapisane dane <span class="text-nowrap">stacji
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'kod_stacji' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('kod_stacji')">
                                                        Numer ID <span class="text-nowrap">stacji
                                                        @if($sortBy === 'kod_stacji')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'nazwa_stacji' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('nazwa_stacji')">
                                                         <span class="text-nowrap">Nazwa stacji
                                                        @if($sortBy === 'nazwa_stacji')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'temperatura_powietrza_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('temperatura_powietrza_data')">
                                                        Pomiar temp.<span class="text-nowrap"> powietrza
                                                        @if($sortBy === 'temperatura_powietrza_data')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'temperatura_powietrza' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('temperatura_powietrza')">
                                                        Temp. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temperatura_powietrza')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'temperatura_gruntu_data' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                    wire:click="setSort('temperatura_gruntu_data')">
                                                        Pomiar temp.<span class="text-nowrap"> gruntu
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
                                                        <td class="p-2 font-medium">
                                                            <a class="underline text-blue-500 text-nowrap" href="{{ route('stacja_recent').'/?id='.$data['kod_stacji'] ?? ''  }}">Bieżace </a>
                                                            <hr class="my-2">
                                                            <a class="underline text-gray-500 text-nowrap" href="{{ route('stacja_archive').'/?id='.$data['kod_stacji'] ?? ''  }}">Archiwalne </a>

                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'kod_stacji' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['kod_stacji'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'nazwa_stacji' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['nazwa_stacji'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'temperatura_powietrza_data' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['temperatura_powietrza_data']) ? Carbon::parse($data['temperatura_powietrza_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temperatura_powietrza' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['temperatura_powietrza'] ?? '-' }}
                                                        </td>
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
                                                                        ↓
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

            </div>

        @endif
        </div>
    </div>

</div>
