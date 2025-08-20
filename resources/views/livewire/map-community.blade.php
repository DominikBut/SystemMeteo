<div>

    @php
         use Carbon\Carbon;
    @endphp
    <div class="p-4 space-y-4 w-full">
        <div class="bg-white rounded-md shadow-sm border">
                    <x-slot name="header">
                            {{ __('Przeglądasz najnowsze dane meteo stacji pogodowych społeczności') }}
                    </x-slot>
            <div class="flex flex-col justify-between w-full">
                                <div    x-data="{
                                        selectedTab: 'temp',
                                        selectTab(tab, $el) {
                                            this.selectedTab = tab;
                                            $wire.set('option', tab);

                                        }

                                    }"
                                    class="w-full p-1 pb-2 flex flex-col justify-between border-b">

                                        {{-- <p class="ms-2 text-sm text-gray-500 font-medium">Wybierz rodzaj danych wyświetlanych na mapie:</p> --}}
                                        <div
                                            class="sm:text-sm overflow-x-auto flex flex-row mx-3 items-center w-auto min-h-10 md:min-h-14 2xl:min-h-10 text-sm font-medium px-1  text-gray-500 bg-gray-200 rounded-md "
                                            role="tablist">

                                            <!-- Buttons -->
                                            <button data-tab="temp" x-on:click="selectTab('temp');" wire:loading.attr="disabled" wire:target="getStationData, getStations, getStationDataid"
                                            :class="selectedTab === 'temp' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Temp. powietrza [°C]</button>


                                            <button data-tab="hum" x-on:click="selectTab('hum');" wire:loading.attr="disabled" wire:target="getStationData, getStations, getStationDataid"
                                            :class="selectedTab === 'hum' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Wilg. względna [%]</button>

                                            <button data-tab="wind" x-on:click="selectTab('wind'); " wire:loading.attr="disabled" wire:target="getStationData, getStations, getStationDataid"
                                            :class="selectedTab === 'wind' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Wiatr śr. prędkość [m/s]</button>

                                            <button data-tab="rain" x-on:click="selectTab('rain'); " wire:loading.attr="disabled" wire:target="getStationData, getStations, getStationDataid"
                                            :class="selectedTab === 'rain' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3 transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Opad deszczu (10 min) [mm]</button>

                                            <button data-tab="all" x-on:click="selectTab('all');" wire:loading.attr="disabled" wire:target="getStationData, getStations, getStationDataid"
                                            :class="selectedTab === 'all' ? ' bg-white rounded-md shadow-sm text-blue-700' : 'hover:text-black'"
                                            class=" delay-100 duration-300 ease-out inline-flex items-center justify-center w-full h-8 px-3  transition-all rounded-md cursor-pointer whitespace-nowrap"
                                            type="button" role="tab">Lokalizacje stacji (24h)</button>
                                        </div>
                                </div>
                    </div>
            <div class="grid sm:grid-cols-4">
                <div class="sm:col-span-3 ">

                    <div wire:ignore class="relative w-auto h-[48rem]  flex justify-center p-4   shadow-sm border">
                        <!-- Map (initially hidden or placed underneath) -->
                        <div class="absolute top-0 left-0 w-full h-full z-10 animate-pulse bg-gray-300  shadow-sm  "></div>
                        <div wire:ignore id="map" class="absolute top-0 left-0 w-full h-full z-10  shadow-sm "></div>
                        <div wire:loading wire:target.except="setSort, getStationDataid" class="absolute top-0 left-0 w-full h-full z-30 flex flex-col justify-center">
                            <div class="w-full h-full flex flex-col justify-center text-center items-center text-gray-500">
                                <p class="w-min text-sm sm:text-xl font-bold  p-3 bg-white rounded-md ">Ładowanie...</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="sm:col-span-1 border-t max-h-[20rem] sm:max-h-[48rem] flex flex-col bg-blue-50 border-gray-300">
                    <div  class="bg-blue-100 relative pt-2 px-2 flex w-full max-w-full flex-col gap-1 text-slate-700 ">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="absolute left-4 top-9 size-5 -translate-y-1/2 text-slate-700/50 " aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                </svg>
                                <input id="stationSearch"  type="search" placeholder="Szukaj stacji..." class="w-full border border-slate-300 rounded-xl bg-white px-2 py-1.5 pl-9 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 disabled:cursor-not-allowed disabled:opacity-75 " name="search" aria-label="Search" placeholder="Search"/>
                    </div>
                    @if (Auth::id())
                    <div class="text-xs ps-4 text-gray-500 py-1 font-medium bg-blue-100 border-b border-gray-300">Dostępne moje 🚩 stacje pomiarowe  [{{ collect($stationData)->where('owned',true)->count(0)  }}]</div>
                    {{-- <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" id="ownedStationsOnly" />
                        <span>Pokaż tylko moje stacje</span>
                    </label> --}}
                    @endif
                    <div class="text-xs ps-4 text-gray-500 py-1 font-medium bg-blue-100 border-b border-gray-300">Dostępne stacje pomiarowe  [{{ collect($stationData)->count(0)  }}]:</div>

                    <div id="stationList"
                        x-data="{ stationId: $wire.entangle('stationId'),
                        scrollToStation(id) {
                                this.$nextTick(() => {
                                    const el = this.$el.querySelector(`[data-kod='${id}']`);
                                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                });
                                }
                            }"
                            x-init="
                            // Watch for changes on stationId and scroll
                            $watch('stationId', value => {
                                if (value) {
                                scrollToStation(value);
                                }
                            });
                            "
                        class=" text-xs overflow-y-auto  w-full min-h-[18rem] max-h-full h-full bg-slate-50 relative">

                        @if (!empty($this->stationData))
                            @foreach (collect($this->stationData)->sortBy('name') as $stacja)
                                <div
                                    class="station-list-item cursor-pointer hover:bg-gray-200 px-2 py-1 border z-20"
                                    :class="{ 'bg-slate-100 font-bold border-2': stationId === '{{ $stacja['station_id'] }}' }"
                                    data-kod="{{ $stacja['station_id'] }}"
                                    x-on:click="stationId = '{{ $stacja['station_id'] }}'; focusStation('{{ $stacja['station_id'] }}')"
                                >
                                    <p class=" truncate  hover:underline ps-2" :class="{ 'py-1 text-sm text-blue-500': stationId === '{{ $stacja['station_id'] }}' }">{{ ($stacja['owned']) ? '🚩 ' : '' }}{{ $stacja['name'].' ['. $stacja['station_id'] .']' }}</p>
                                    <!-- Show links if this is the selected station -->
                                    <template x-if="stationId === '{{ $stacja['station_id'] }}'">
                                        <div class="ps-2 text-end flex flex-row justify-end gap-2">
                                            <div class="pb-1 w-full text-end"> Zobacz dane:</div>
                                                <a class="underline text-blue-500 text-nowrap"
                                                href="{{ route('stacja_community').'?id='.$stacja['station_id'] }}">
                                                    Dane bieżące
                                                </a>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        @else
                                <div class="text-wrap text-center text-base bg-white pt-12">Brak stacji wykonujących pomiary.</div>
                        @endif
                    </div>

                    <div  class="text-sm ps-2 min-h-2  py-1 font-medium bg-blue-100 border-y border-gray-300"></div>
                </div>

            </div>
             <div class="flex flex-col sm:flex-row justify-center sm:justify-between">
            @if (!empty($stationData) )
                <p wire:loading.remove wire:target="getStationData" class="text-xs ms-2 py-2  text-gray-500">Dane meteorologiczne wszystkich dostępnych stacji pogodowych pobrano z bazy systemu: <span class="text-lime-600">{{ Carbon::parse($askTime)->format('H:i:s d.m.Y') ?? '–' }}</span></p>
            @else
                <p wire:loading.remove wire:target="getStationData" class="text-xs ms-2 py-2  text-red-500">Brak danych do pobrania z bazy systemu... </p>

            @endif
                <p wire:loading wire:target="getStationData" class="text-xs ms-2 py-2  animate-pulse">Ładowanie...</span></p>
                <p wire:loading wire:target.except="setSort, getStationDataid" class="hidden md:block text-sm ps-2 px-4 py-1 font-medium  animate-pulse">Aktualizowanie...</p>
            </div>
        </div>
        <div class="p-4 bg-white rounded-md shadow-sm text-xs sm:text-sm min-h-32 h-auto w-full">
                        @if (!empty($stationId) && !is_null($stationDataId['latest']))
                            <div wire:loading.remove wire:target="getStationDataid">
                                <div class="flex flex-col sm:flex-row  justify-between w-full mb-4">
                                    <p class="truncate font-bold  text-sm sm:text-base text-gray-500 w-auto">Najnowsze dane meteo dla stacji <span class="text-nowrap text-lime-600">{{ $stationDataId['name'] ?? '-' }} [{{  $stationDataId['station_id'] }}] </span></p>

                                        <p class="text-xs text-gray-500  w-auto">
                                        Dane pobrano: {{ $askTime ?? '–' }}
                                        </p>

                                </div>

                                <ul class="flex flex-row gap-4 sm:text-sm min-h-6 overflow-x-auto text-nowrap">
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v2.25M12 18.75V21M4.22 4.22l1.59 1.59M17.19 17.19l1.59 1.59M3 12h2.25M18.75 12H21M4.22 19.78l1.59-1.59M17.19 6.81l1.59-1.59" stroke-linecap="round"
                                                    stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Temp. powietrza:</strong> {{ $stationDataId['latest']['temp_air'] ?? '-' }} °C
                                        </p>
                                    </li>

                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M12 3v18m9-9H3" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Wilg. względna:</strong> {{ $statistationDataId['latest']['humidity'] ?? '-' }} %
                                        </p>

                                    </li>
                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 12h16M4 6h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong>Wiatr śr.:</strong> {{ $stationDataId['latest']['wind_speed'] ?? '-' }} m/s
                                        </p>

                                    </li>

                                    <li>
                                        @php
                                            $rotation = (!is_null($stationDataId['latest']['wind_direction']) && is_numeric($stationDataId['latest']['wind_direction']) ) ? $stationDataId['latest']['wind_direction'] : 0;

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
                                                {{ $stationDataId['latest']['wind_direction'] ?? '-' }} °
                                            </div>
                                        </div>

                                    </li>

                                    <li>
                                        <p class="flex items-start sm:items-center gap-1">
                                            <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5"
                                                viewBox="0 0 24 24">
                                                <path d="M4 4v16h16V4H4zM9 9h6v6H9z" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <strong class=" w-min sm:w-auto">Opad <span class="text-nowrap">(10 min):</span></strong> {{ $stationDataId['latest']['rain_10min'] ?? '-' }} mm
                                        </p>

                                    </li>
                                </ul>
                                <div class="flex flex-row items-center {{$stationDataId['active'] ? 'text-lime-600': 'text-red-500'}} ps-2 pt-2">
                                    <b>Stacja {{$stationDataId['owned'] ? '🚩 prywatna': 'publiczna'}} {{$stationDataId['active'] ? 'aktywna': 'nieaktywna'}}</b>
                                    <p class="ps-2 text-xs text-gray-500  w-auto  font-medium">
                                        Pomiary wykonano: <span class="text-nowrap">{{ !empty($stationDataId['latest']['created_at']) ? Carbon::parse($stationDataId['latest']['created_at'])->format('Y-m-d H:i') : 'Brak pomiaru' }}</span>
                                        </p>
                                    </div>
                            </div>
                        @elseif(!empty($error))
                            <div wire:loading.remove wire:target="getStationDataid"  class="min-h-20 items-center w-full flex flex-col justify-center text-center ">
                                <div class=" font-bold  w-full flex flex-col justify-center">
                                    {{-- <div class="text-sm font-bold text-red-500">W bazie systemu nie znaleziono danych dla stacji o wybranym ID.</div> --}}

                                    @if ($error)
                                        <p class="font-bold text-red-500">{{ $error }}</p>
                                    @endif
                                    <br>
                                     <div class="font-bold text-gray-600 text-sm mt-2">
                                        Wybierz inną stację, przełącz warstwę wyświetlania lub spróbuj innej z tego samego regionu.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div wire:loading.remove  wire:target="getStationDataid"  class="min-h-20 items-center  w-full flex flex-col justify-center text-center ">
                                <div class="font-bold w-full flex flex-col items-center justify-center">
                                    <p class="w-full  m-auto animate-pulse">Oczekiwanie na wybór stacji...</p>
                                </div>
                            </div>
                        @endif
                        <div wire:loading  wire:target="getStationDataid"  class="pt-2 min-h-20 items-center w-full flex flex-col justify-center text-center ">
                                <div class="font-bold items-center w-full flex flex-col justify-center">
                                    <p class="w-full  m-auto animate-pulse">Pobieranie danych...</p>
                                </div>
                        </div>
        </div>
        <div class="p-4 bg-white rounded-md shadow-sm">

            @php
                $allNull = collect($stationData)->every(fn($item) =>
                    collect($item)->except(['station_id', 'nazwa_stacji', 'lon', 'lat', 'data'])
                                ->filter(fn($val) => !is_null($val))
                                ->isEmpty()
                );
            @endphp
            @if(empty($stationData) || $allNull)

            <div>
                <div  class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold  flex flex-row justify-between">
                Brak danych...
                </div>
                <div class="h-44 text-xs bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto ">

                </div>
            </div>
            <div class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold  flex flex-row justify-between">
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
                Statystyki pobranych najnowszych danych (do -2 godz.):
                </div>
                <div class="h-64 text-xs bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto ">
                    <table wire:loading.remove wire:target="getStationData"  class="w-full text-left h-full">

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
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['temp_air']['max'] ?? '-' }}
                                                @if ($minMaxStats['temp_air']['max']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['temp_air']['max_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['temp_air']['max_station'] ?? '-' }} {{ $minMaxStats['temp_air']['max_station_id'] ?? '-' }}</a>
                                                 @endif
                                            </div>
                                        </td>

                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['humidity']['max'] ?? '-' }}
                                                @if ($minMaxStats['humidity']['max']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['humidity']['max_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['humidity']['max_station'] ?? '-' }} {{ $minMaxStats['humidity']['max_station_id'] ?? '-' }}</a>
                                                 @endif
                                            </div>
                                        </td>
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['rain_10min']['max'] ?? '-' }}
                                                @if ($minMaxStats['rain_10min']['max']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['rain_10min']['max_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                    <span class="text-nowrap"> {{ $minMaxStats['rain_10min']['max_station'] ?? '-' }} {{ $minMaxStats['rain_10min']['max_station_id'] ?? '-' }}</span> </a>
                                                 @endif
                                            </div>
                                        </td>
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['wind_speed']['max'] ?? '-' }}
                                                @if ($minMaxStats['wind_speed']['max']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['wind_speed']['max_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['wind_speed']['max_station'] ?? '-' }} {{ $minMaxStats['wind_speed']['max_station_id'] ?? '-' }}</a>
                                                 @endif
                                            </div>
                                        </td>


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
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['temp_air']['min'] ?? '-' }}
                                                @if ($minMaxStats['temp_air']['min']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['temp_air']['min_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['temp_air']['min_station'] ?? '-' }} {{ $minMaxStats['temp_air']['min_station_id'] ?? '-' }}</a>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['humidity']['min'] ?? '-' }}
                                                @if ($minMaxStats['humidity']['min']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['humidity']['min_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['humidity']['min_station'] ?? '-' }} {{ $minMaxStats['humidity']['min_station_id'] ?? '-' }}</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['rain_10min']['min'] ?? '-' }}
                                                @if ($minMaxStats['rain_10min']['min']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['rain_10min']['min_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['rain_10min']['min_station'] ?? '-' }} {{ $minMaxStats['rain_10min']['min_station_id'] ?? '-' }}</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-1 sm:px-2">
                                            <div class="flex flex-col mt-2">
                                                {{ $minMaxStats['wind_speed']['min'] ?? '-' }}
                                                @if ($minMaxStats['wind_speed']['min']!=null)
                                                <a style="font-size: 0.6rem;" x-on:click="focusStation('{{ $minMaxStats['wind_speed']['min_station_id'] ?? null }}')"
                                                class=" cursor-pointer text-xs text-gray-500 truncate underline text-nowrap">
                                                {{ $minMaxStats['wind_speed']['min_station'] ?? '-' }} {{ $minMaxStats['wind_speed']['min_station_id'] ?? '-' }}</a>
                                                @endif
                                            </div>
                                        </td>

                                    </tr>
                                </tbody>

                    </table>
                </div>
            </div>
            <div wire:loading.remove wire:target="getStationData, setSort"  class="ms-2 mt-4 text-xs sm:text-sm py-4 font-semibold text-gray-500 flex flex-row justify-between">

                Zestawienie tabelaryczne pobranych danych meteorologicznych wszystkich stacji aktywnych przez ostatnie 24 godz.:

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
                                                    <th class=" p-2 text-gray-600">
                                                       Lp.
                                                    </th>
                                                    <th class=" p-2 text-gray-600"
                                                        >
                                                        Zapisane dane <span class="text-nowrap">stacji
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'station_id' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('station_id')">
                                                        Numer ID <span class="text-nowrap">stacji
                                                        @if($sortBy === 'station_id')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'name' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('name')">
                                                         <span class="text-nowrap">Nazwa stacji
                                                        @if($sortBy === 'name')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>
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
                                                        Temp. <span class="text-nowrap">powietrza [°C]
                                                        @if($sortBy === 'temp_air')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>


                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'humidity' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('humidity')">
                                                        Wilg. <span class="text-nowrap">względna [%]
                                                        @if($sortBy === 'humidity')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>

                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'rain_10min' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('rain_10min')">
                                                        Opad <span class="text-nowrap">10 min [mm]
                                                        @if($sortBy === 'rain_10min')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer   transition hover:opacity-75 {{$sortBy === 'wind_speed' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wind_speed')">
                                                        Wiatr śr. <span class="text-nowrap">prędkość [m/s]
                                                        @if($sortBy === 'wind_speed')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                    <th title="Sortuj" class="hover:underline p-2 cursor-pointer  transition hover:opacity-75 {{$sortBy === 'wind_direction' ? 'text-blue-600' : 'text-gray-600'  }}"
                                                        wire:click="setSort('wind_direction')">
                                                        Wiatr <span class="text-nowrap">kierunek [°]
                                                        @if($sortBy === 'wind_direction')
                                                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                                        @endif
                                                        </span>
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 ">
                                                @foreach($sortedData as $data)
                                                    <tr class="hover:!bg-blue-100 even:bg-gray-700/5 even: text-center transition  text-xs">
                                                        <td class="p-2 ">
                                                                 {{ $loop->iteration }}
                                                        </td>
                                                        <td class="p-2 font-medium">
                                                             @if (Auth::id())
                                                            <span title="Moja stacja" class="cursor-pointer">{{ ($data['owned']) ? '🚩 ' : '' }}</span>
                                                            @endif
                                                            <a class="underline text-blue-500 text-nowrap" href="{{ route('stacja_community').'?id='.$data['station_id'] ?? ''  }}">Bieżące </a>

                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'station_id' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['station_id'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'name' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['name'] ?? '-' }}
                                                        </td>
                                                        <td class="p-2 text-nowrap {{$sortBy === 'created_at' ? 'text-blue-400 font-semibold' : 'text-gray-500'  }}">
                                                            {{ !empty($data['latest']['created_at']) ? Carbon::parse($data['latest']['created_at'])->format('Y-m-d H:i') : 'Brak' }}
                                                        </td>
                                                        <td class="p-2  {{$sortBy === 'temp_air' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['latest']['temp_air'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'humidity' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['latest']['humidity'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'rain_10min' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['latest']['rain_10min'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'wind_speed' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            {{ $data['latest']['wind_speed'] ?? '-' }}
                                                        </td>

                                                        <td class="p-2  {{$sortBy === 'wind_direction' ? 'text-blue-500 font-semibold' : ''  }}">
                                                            @if(!is_null($data['latest']['wind_direction']))
                                                                @php
                                                                    $rotation = is_numeric($data['latest']['wind_direction']) ? $data['latest']['wind_direction'] : 0;
                                                                @endphp
                                                                <span class="w-10">
                                                                    <div class="inline-block transform font-extrabold text-lg px-1" style="rotate: {{ $rotation+90 }}deg;">
                                                                        ➤
                                                                    </div>
                                                                </span>
                                                                {{ $data['latest']['wind_direction'] ?? '-' }}
                                                            @else
                                                                <span>{{ $data['latest']['wind_direction'] ?? '-' }}</span>
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
