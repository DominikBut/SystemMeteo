<div>

    <div class="p-4 space-y-4 w-full">
        <div class="flex flex-row align-content-center w-full text-xs sm:text-base">

            <div
            {{-- x-data="stationSelect({
                        selectedId: @entangle('stationId'),
                        stations: {{ Js::from($stations) }},

                    })" --}}
                    class="flex flex-col md:grid  md:grid-cols-2 w-full">
                    <h1 class="col-span-2 bg-white rounded-md shadow-sm py-4  px-2 mx-2 text-center  font-bold tracking-wider border"> <span class="text-red-500"> Pamiętaj o utworzeniu klucza API, aby móc przesyłać dane ze stacji na serwer!</span>
                        <br> Możesz używać jednego klucza dla wielu stacji! Zarządzanie kluczami api dostępne jest <a class="underline text-blue-500" href="{{ route('api-tokens.index') }}">tutaj</a> </h1>
                    <div class="flex flex-col justify-between p-2">
                         {{-- @if (!empty($stations) && count($stations)!=0)
                        <div>
                            <p class="p-1 font-bold text-gray-600">Wybierz jedną z {{ count($stations) }} oficjalnych stacji IMGW:</p>
                            <div class="relative w-full">
                                <input
                                    type="search"
                                    class="bg-white rounded-md shadow-sm border-2 border-gray-300 p-2 w-full"
                                    :value="stations[selectedId] ? `${stations[selectedId]}` : (selectedId ?? '')"
                                    @input="query = $event.target.value"
                                    {{-- to wyzej powoduje ze jak wpisuje to szuka a jak dostaje z url to nie szuka auto jak jest zle dodatkowy czek 2 kroki zamiast 1 --}}
                                    {{-- @focus="open = true"
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
                                        ❌ Nieprawidłowa stacja (ID: {{ $stationId }}) – Brak wśród oficjalnych stacji IMGW.
                                    </p>
                                    <p  x-show="!selectedId && !stations[selectedId]" class="font-bold text-lime-600 my-2 w-auto">
                                        Wybierz najpierw stację!.
                                    </p>
                                </div>
                            </div> --}}
                        {{-- </div>
                        @else
                            <h1 class="w-full bg-white rounded-md shadow-sm text-sm text-red-500 p-4 mb-2 text-center"><b>Błąd połączenia z API spróbuj ponownie za godzinę, aby móc przeglądać dane!</b></h1>
                        @endif --}}

                        <div class="w-full">

                        </div>
                    </div>
                    {{-- <div class="p-2 flex flex-col justify-end">
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
                                            {{ !empty($stationData['temperatura_powietrza_data']) ? Carbon::parse($stationData['temperatura_powietrza_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['temperatura_gruntu_data']) ? Carbon::parse($stationData['temperatura_gruntu_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['wilgotnosc_wzgledna_data']) ? Carbon::parse($stationData['wilgotnosc_wzgledna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['wiatr_srednia_predkosc_data']) ? Carbon::parse($stationData['wiatr_srednia_predkosc_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['wiatr_predkosc_maksymalna_data']) ? Carbon::parse($stationData['wiatr_predkosc_maksymalna_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['wiatr_kierunek_data']) ? Carbon::parse($stationData['wiatr_kierunek_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['wiatr_poryw_10min_data']) ? Carbon::parse($stationData['wiatr_poryw_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                                            {{ !empty($stationData['opad_10min_data']) ? Carbon::parse($stationData['opad_10min_data'], 'UTC')->setTimezone('Europe/Warsaw')->format('Y-m-d H:i') : 'Brak pomiaru' }}
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
                    </div> --}}
            </div>
        </div>

        <div class=" m-2  rounded-md shadow-sm border-none">

             {{ $this->table }}

            {{-- <div class=" bg-white rounded-md shadow-sm border border-gray-300 overflow-hidden w-full overflow-x-auto overflow-y-auto max-h-[80vh]">

            </div> --}}

        </div>
    </div>
</div>
