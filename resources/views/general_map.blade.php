<x-guest-layout>

    @pushOnce('css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""/>
            <style>
            .custom-temp-icon .marker-label {
                background-color: green;
                color: white;
                padding: 3px 5px;
                border-radius: 5px;
                border-color: white;
                border-width: 2px;
                font-size: 12px;
                font-weight: bold;
                text-align: center;
            }
            .station-disabled {
                opacity: 0.4;
                pointer-events: none;
            }
            </style>
    @endPushOnce
    @pushOnce('scripts')
        <!-- Make sure you put this AFTER Leaflet's CSS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    @endPushOnce
    <div class="p-6">{{--  py-12 max-w-7xl mx-auto sm:px-6 lg:px-8--}}
        <div class=" ">
            <div class="bg-lime-100 overflow-hidden shadow-xl sm:rounded-lg">
                <div id="layerToggles" class="p-4">
                    <button class="p-2 text-center mx-2 outline-4 rounded-lg outline-cyan-500 bg-cyan-300 hover:bg-slate-300" onclick="toggleLayer(tempLayerGroup, this, 'temp')">Temperatura</button>
                    <button style="opacity: 0.5;" class="p-2 text-center mx-2 outline-4 rounded-lg outline-cyan-500 bg-cyan-300 hover:bg-slate-300" onclick="toggleLayer(humidityLayerGroup, this, 'humidity')">Wilgotność</button>
                    <button style="opacity: 0.5;" class="p-2 text-center mx-2 outline-4 rounded-lg outline-cyan-500 bg-cyan-300 hover:bg-slate-300" onclick="toggleLayer(windLayerGroup, this,'wind')">Wiatr</button>
                    <button style="opacity: 0.5;" class="p-2 text-center mx-2 outline-4 rounded-lg outline-cyan-500 bg-cyan-300 hover:bg-slate-300" onclick="toggleLayer(rainLayerGroup, this, 'rain')">Opady</button>
                    <button style="opacity: 0.5;" class="p-2 text-center mx-2 outline-4 rounded-lg outline-cyan-500 bg-gray-300 hover:bg-slate-300"
                        onclick="toggleLayer(allStationsLayerGroup, this, 'all')">
                        Pokaż wszystkie stacje
                    </button>
                </div>
                {{ $status .': '. $AskedAt . ' (nowe dane co 10 min (najswiezsze z 20min przed))'}}
                <div class="flex flex-row p-2 bg-white">

                    <div id="stationListWrapper" class="w-1/6  border-r border-gray-300 p-2 bg-white text-sm">
                        <div class="flex items-center gap-2 mb-2">

                            <div  class="relative my-4 flex w-full max-w-xs flex-col gap-1 text-slate-700 ">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="absolute left-2 top-7 size-5 -translate-y-1/2 text-slate-700/50 " aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                </svg>
                                <input id="stationSearch"  type="search" placeholder="Szukaj stacji..." class="w-full border border-slate-300 rounded-xl bg-white px-2 py-1.5 pl-9 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 disabled:cursor-not-allowed disabled:opacity-75 " name="search" aria-label="Search" placeholder="Search"/>
                            </div>
                            {{-- <button
                                id="clearStationSearch"
                                class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-sm rounded"
                            >
                                X
                            </button> --}}
                        </div>
                            <h4>to laguje ładowanie</h4>
                            <div id="stationList" class="h-[40rem] overflow-y-auto">
                                @foreach (collect($data)->sortBy('nazwa_stacji') as $stacja)
                                        <div
                                            class="station-list-item cursor-pointer hover:bg-gray-200 px-2 py-1 border-b"
                                            data-kod="{{ $stacja->kod_stacji }}" onclick="focusStation('{{ $stacja->kod_stacji }}')"
                                        >
                                            {{ $stacja->nazwa_stacji }}
                                        </div>
                                @endforeach
                            </div>
                    </div>
                    <div class="relative w-5/6 min-h-[48rem]">
                        <!-- Placeholder (skeleton loader) -->
                        <div class="absolute top-0 left-0 w-full h-full z-10 animate-pulse bg-gray-300"></div>

                        <!-- Map (initially hidden or placed underneath) -->
                        <div id="map" class="absolute top-0 left-0 w-full h-full z-20"></div>
                    </div>
                </div>
                @if (!empty($data))
                        {{-- {{ dd($data) }} --}}
                        <div class="text-xs text-center">
                            <table class="border-separate border-spacing-2 border border-black">
                                <tr >
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">Nazwa</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">kod</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">data</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">tmp</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">wilg</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">wiatr srednia</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">wiatr max</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">wiatr poryw 10min</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">opad 10min</th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-2">opad data</th>
                                </tr>

                            @foreach ( $data as $stacja )
                                <tr >
                                <td> {{ $stacja->nazwa_stacji }} </td>
                                <td> {{ $stacja->kod_stacji }} </td>
                                <td> {{ $stacja->temperatura_gruntu_data }} </td>
                                <td> {{ $stacja->temperatura_gruntu }} </td>
                                <td> {{ $stacja->wilgotnosc_wzgledna }} </td>
                                <td> {{ $stacja->wiatr_srednia_predkosc }} </td>
                                <td> {{ $stacja->wiatr_predkosc_maksymalna }} </td>
                                <td> {{ $stacja->wiatr_poryw_10min }} </td>
                                <td> {{ $stacja->opad_10min }} </td>
                                <td> {{ $stacja->opad_10min_data }} </td>
                                </tr>
                            @endforeach

                            </table>
                        </div>
                @endif
            </div>
        </div>
    </div>

        <script>
            const stacjeData = @json($data);
            const stationMarkers = {
                            temp: {},
                            humidity: {},
                            wind: {},
                            rain: {},
                            all: {}
                        };
            let currentLayerType = 'temp'; // default visible

            const allStationsLayerGroup = L.layerGroup();
            const tempLayerGroup = L.layerGroup(); // create the group
            const humidityLayerGroup = L.layerGroup();
            const windLayerGroup = L.layerGroup();
            const rainLayerGroup = L.layerGroup();


            function focusStation(kod) {
                const marker = stationMarkers[currentLayerType]?.[kod];
                if (marker) {
                    map.setView(marker.getLatLng(), 12);
                    marker.openPopup();
                }
            }

           function toggleLayer(activeLayer, activeBtn, typeName) {
                // Define all layers and buttons
                const layers = [tempLayerGroup, humidityLayerGroup, windLayerGroup, rainLayerGroup, allStationsLayerGroup];
                const buttons = document.querySelectorAll('#layerToggles button');

                // Remove all layers from the map
                layers.forEach(layer => map.removeLayer(layer));

                // Reset button styles
                buttons.forEach(btn => btn.style.opacity = 0.5);

                // If the clicked layer is not already on the map, add it
                if (!map.hasLayer(activeLayer)) {
                    map.addLayer(activeLayer);
                    activeBtn.style.opacity = 1;
                    currentLayerType = typeName; //  update here
                    updateStationListAvailability(); // refresh list based on current layer
                }
            }
            // // Define custom icons
            // const redIcon = L.icon({
            //     iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
            //     iconSize: [25, 41],
            //     iconAnchor: [12, 41],
            //     popupAnchor: [1, -34],
            //     shadowSize: [41, 41]
            // });

            // const grayIcon = L.icon({
            //     iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-grey.png',
            //     iconSize: [25, 41],
            //     iconAnchor: [12, 41],
            //     popupAnchor: [1, -34],
            //     shadowSize: [41, 41]
            // });

            //setup
            var map = L.map('map', {
                renderer: L.canvas()
            }).setView([52.25, 19.25], 7);
            //credits
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);


            function getTemperatureColor(temp) {
                // // Normalize temperature between -20 and 40 (adjust to your real range)
                // const minTemp = -20;
                // const maxTemp = 40;
                // const clamped = Math.max(minTemp, Math.min(maxTemp, temp));
                // const percent = (clamped - minTemp) / (maxTemp - minTemp);

                // // Convert percent to a hue value (240 = blue, 0 = red)
                //  // Hue: 240 (cold/blue) to 0 (hot/red)
                // const hue = (1 - percent) * 240;
                // Define key temp points
                const cold = 0;     // blue
                const warm = 16;    // green
                const hot = 28;     // red

                let hue;

                if (temp <= cold) {
                    hue = 240; // blue
                } else if (temp >= hot) {
                    hue = 0; // red
                } else if (temp <= warm) {
                    // Blue to green
                    const ratio = (temp - cold) / (warm - cold); // 0 to 1
                    hue = 240 - (ratio * (240 - 120)); // from 240 (blue) to 120 (green)
                } else {
                    // Green to red
                    const ratio = (temp - warm) / (hot - warm); // 0 to 1
                    hue = 120 - (ratio * 120); // from 120 (green) to 0 (red)
                }

                // Adjusted to look more saturated, less neon
                const saturation = 80;  // Lower than 100%
                const lightness = 40;   // Darker, richer tones

                return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
            }

            function getHumidityColor(humidity) {
                const dry = 0;      // 0% humidity
                const optimalLow = 40;
                const optimalHigh = 60;
                const wet = 100;

                let hue;

                if (humidity <= dry) {
                    hue = 0; // Red
                } else if (humidity >= wet) {
                    hue = 240; // Blue
                } else if (humidity <= optimalLow) {
                    // Red → Green
                    const ratio = (humidity - dry) / (optimalLow - dry); // 0 to 1
                    hue = 0 + ratio * (120 - 0); // From red (0) to green (120)
                } else if (humidity <= optimalHigh) {
                    hue = 120; // Keep green in optimal zone
                } else {
                    // Green → Blue
                    const ratio = (humidity - optimalHigh) / (wet - optimalHigh); // 0 to 1
                    hue = 120 + ratio * (240 - 120); // From green (120) to blue (240)
                }

                const saturation = 80;
                const lightness = 45;

                return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
            }

            function getWindColor(speed) {
                const maxSpeed = 15;
                const clamped = Math.min(speed, maxSpeed);
                const percent = clamped / maxSpeed;

                // light gray start at rgb(200,200,200) to black (0,0,0)
                const grayValue = Math.round(170 - (170 * percent)); // 200 → 0

                return `rgb(${grayValue},${grayValue},${grayValue})`;
            }

           function getRainColor(rain) {
                const maxRain = 5; // adjust max rain amount as needed
                const clamped = Math.min(rain, maxRain);
                const percent = clamped / maxRain;

                // Light start: almost white/light gray
                // We'll interpolate lightness from ~95% (very light) down to ~40% (rich blue)
                // Hue for blue is ~210

                const hue = 210;
                const saturation = 90; // rich blue
                const lightness = 100 - (55 * percent); // from 95% to 40%

                return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
            }
            function convertToLocalDate(dateStr) {
                if (!dateStr) return null;

                // Fix format: replace " " with "T" and mark as UTC with "Z"
                const isoStr = dateStr.replace(" ", "T") + "Z";

                const utcDate = new Date(isoStr);

                if (isNaN(utcDate.getTime())) return null;

                // Return local date and time (with seconds)
                return utcDate.toLocaleString('pl-PL', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour12: false
                });
            }

            function isRecentEnough(utcString, maxAgeMinutes = 120) {
                if (!utcString) return false;
                const utcDate = new Date(utcString.replace(" ", "T") + "Z"); // zapewnia UTC
                const now = new Date(); // lokalny czas
                const diffMinutes = (now - utcDate) / (1000 * 60);
                return diffMinutes >= 0 && diffMinutes <= maxAgeMinutes;
            }


            // Loop through Laravel data passed to JS
            stacjeData.forEach(stacja => {

                const lat = parseFloat(stacja.lat);
                const lon = parseFloat(stacja.lon);

                const markerc = L.circleMarker([lat, lon], {
                    radius: 6,
                    color: '#666',
                    fillColor: 'cyan',
                    fillOpacity: 0.8,
                    weight: 1
                }).bindPopup(`<strong>${stacja.nazwa_stacji}</strong><br>Kod: ${stacja.kod_stacji}`);

                allStationsLayerGroup.addLayer(markerc);
                stationMarkers.all[stacja.kod_stacji] = markerc;

                markerc.on('mouseover', function () {
                    this.setStyle({ radius: 8, color: '#333' });
                });
                markerc.on('mouseout', function () {
                    this.setStyle({ radius: 6, color: '#666' });
                });


                // === Temperature ===
                if (stacja.temperatura_gruntu !== null && isRecentEnough(stacja.temperatura_gruntu_data)) {
                    const temp = parseFloat(stacja.temperatura_gruntu);
                    const color = getTemperatureColor(temp);
                    const icon = L.divIcon({
                        className: 'custom-temp-icon',
                        html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${temp.toFixed(1)} °C</div>`,
                        iconSize: [55, 20],
                    });
                    let tempDate = convertToLocalDate(stacja.temperatura_gruntu_data);
                    const marker = L.marker([lat, lon], { icon }).bindPopup(`
                        <strong>${stacja.nazwa_stacji}</strong><br>
                        Temperatura: ${temp.toFixed(1)}°C <br>
                        Data zapisu: ${tempDate ?? 'brak'}<br>
                    `);
                    tempLayerGroup.addLayer(marker);
                    stationMarkers.temp[stacja.kod_stacji] = marker; // save reference
                }
                // === Humidity ===
                if (stacja.wilgotnosc_wzgledna !== null && isRecentEnough(stacja.wilgotnosc_wzgledna_data)) {
                    const hum = parseFloat(stacja.wilgotnosc_wzgledna);
                    const color = getHumidityColor(hum);
                    const icon = L.divIcon({
                        className: 'custom-temp-icon',
                        html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${hum.toFixed(0)} %</div>`,
                        iconSize: [55, 20],
                    });
                    let humDate = convertToLocalDate(stacja.wilgotnosc_wzgledna_data);
                    const marker = L.marker([lat, lon], { icon }).bindPopup(`
                        <strong>${stacja.nazwa_stacji}</strong><br>
                        Wilgotność: ${hum.toFixed(0)}% <br>
                        Data zapisu: ${humDate ?? 'brak'}<br>
                    `);
                    humidityLayerGroup.addLayer(marker);
                    stationMarkers.humidity[stacja.kod_stacji] = marker; // save reference
                }

                // === Wind ===
                if (stacja.wiatr_srednia_predkosc !== null && isRecentEnough(stacja.wiatr_srednia_predkosc_data) && stacja.wiatr_srednia_predkosc != 0) {
                    const wind = parseFloat(stacja.wiatr_srednia_predkosc);
                    const color = getWindColor(wind);
                    const icon = L.divIcon({
                        className: 'custom-temp-icon',
                        html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${wind.toFixed(1)} km/h</div>`,
                        iconSize: [70, 20],
                    });
                    let windDate = convertToLocalDate(stacja.wiatr_srednia_predkosc_data);
                    const marker = L.marker([lat, lon], { icon }).bindPopup(`
                        <strong>${stacja.nazwa_stacji}</strong><br>
                        Wiatr: ${wind.toFixed(1)} m/s <br>
                        Data zapisu: ${windDate ?? 'brak'}<br>
                    `);
                    windLayerGroup.addLayer(marker);
                    stationMarkers.wind[stacja.kod_stacji] = marker; // save reference
                }

                // === Rainfall ===
                if (stacja.opad_10min !== null && isRecentEnough(stacja.wiatr_srednia_predkosc_data) && stacja.opad_10min != 0) {
                    const rain = parseFloat(stacja.opad_10min);
                    const color = getRainColor(rain);
                    const icon = L.divIcon({
                        className: 'custom-temp-icon',
                        html: `<div class="marker-label" style="border-color: lightblue; border-width: 1px; opacity: 0.8; color: blue; background-color:${color}">${rain.toFixed(1)} mm</div>`,
                        iconSize: [55, 20],
                    });
                    let rainDate = convertToLocalDate(stacja.opad_10min_data);
                    const marker = L.marker([lat, lon], { icon }).bindPopup(`
                        <strong>${stacja.nazwa_stacji}</strong><br>
                        Opad: ${rain.toFixed(1)} mm / 10min <br>
                        Data zapisu: ${rainDate ?? 'brak'}<br>
                    `);
                    rainLayerGroup.addLayer(marker);
                    stationMarkers.rain[stacja.kod_stacji] = marker;
                }


                tempLayerGroup.addTo(map); // show by default


                // if (stacja.lat && stacja.lon) {
                //     const hasTemp = stacja.temperatura_gruntu !== null && stacja.temperatura_gruntu && stacja.temperatura_gruntu_data;
                //     const tempDateStr = stacja.temperatura_gruntu_data;
                //      // Skip if temp date is missing or invalid
                //     if (!tempDateStr || isNaN(new Date(tempDateStr))) return;

                //     if (!hasTemp) return; // Skip if no temperature

                //     // Get only the date part (YYYY-MM-DD)
                //     const tempDate = new Date(tempDateStr).toISOString().slice(0, 10);
                //     const today = new Date().toISOString().slice(0, 10);

                //     // Skip if the temp data is before today
                //      if (tempDate < today) return;

                //     const temp = parseFloat(stacja.temperatura_gruntu);
                //     const tempText = `${temp.toFixed(1)}°C`;
                //     const bgColor = getTemperatureColor(temp);

                //     const tempIcon = L.divIcon({
                //         className: 'custom-temp-icon',
                //         html: `<div class="marker-label" style="background-color:${bgColor}">${tempText}</div>`,
                //         iconSize: [50, 20],
                //     });

                //     const marker = L.marker(
                //         [parseFloat(stacja.lat), parseFloat(stacja.lon)],
                //         { icon: tempIcon }
                //     );

                //     marker.bindPopup(`
                //         <strong>${stacja.nazwa_stacji}</strong><br>
                //         Data: ${stacja.temperatura_gruntu_data ?? 'brak'}<br>
                //         Temp: ${tempText}<br>
                //         Wiatr: ${stacja.wiatr_srednia_predkosc ?? 'brak'} m/s<br>
                //         Wilgotność: ${stacja.wilgotnosc_wzgledna ?? 'brak'}%
                //     `);
                //     tempLayerGroup.addLayer(marker); // add to group
                // }
            });

            // tempLayerGroup.addTo(map);
            // humidityLayerGroup.addTo(map);
            // windLayerGroup.addTo(map);
            // rainLayerGroup.addTo(map);
            // stacjeData.forEach(stacja => {
            //     if (stacja.lat && stacja.lon) {
            //         const hasTemp = stacja.temperatura_gruntu !== null && stacja.temperatura_gruntu !== '';

            //         if (!hasTemp) return; //  Skip stations with no temperature

            //         const marker = L.marker(
            //             [parseFloat(stacja.lat), parseFloat(stacja.lon)],
            //             { icon: redIcon } // Only red icon, since gray is skipped { icon: hasTemp ? redIcon : grayIcon }
            //         ).addTo(map);

            //         marker.bindPopup(`
            //             <strong>${stacja.nazwa_stacji}</strong><br>
            //             Temp: ${stacja.temperatura_gruntu}°C<br>
            //             Wiatr: ${stacja.wiatr_srednia_predkosc ?? 'brak'} km/h<br>
            //             Wilgotność: ${stacja.wilgotnosc_wzgledna ?? 'brak'}%
            //         `);
            //     }
            // });

            // //klikalny marker
            // var marker_click = null; // initialize

            // function onMapClick(e) {
            //     if (marker_click) {
            //         map.removeLayer(marker_click); // remove previous marker
            //     }
            //     var data = new Date().toISOString().slice(0, 10);
            //     marker_click = L.marker(e.latlng).addTo(map);
            //     marker_click.bindPopup(
            //         `<b>You clicked the map at</b><br>
            //             ${e.latlng.lat.toFixed(5)} ${e.latlng.lat.toFixed(5)}<br>
            //             Data: ${data ?? 'brak'}<br>
            //         `).openPopup();
            // }
            // map.on('click', onMapClick);
            function updateStationListAvailability() {
                const items = document.querySelectorAll('.station-list-item');

                items.forEach(item => {
                    const kod = item.getAttribute('data-kod');
                    const markerExists = stationMarkers[currentLayerType]?.[kod];

                    if (!markerExists) {
                        item.classList.add('station-disabled');
                    } else {
                        item.classList.remove('station-disabled');
                    }
                });
            }

            document.getElementById('stationSearch').addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const items = document.querySelectorAll('#stationList .station-list-item');

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const matches = text.includes(searchTerm);

                    item.style.display = matches ? 'block' : 'none';
                });
            });
            updateStationListAvailability();
            // document.getElementById('clearStationSearch').addEventListener('click', function () {
            //     const searchInput = document.getElementById('stationSearch');
            //     const items = document.querySelectorAll('#stationList .station-list-item');

            //     searchInput.value = '';
            //     searchInput.dispatchEvent(new Event('input'));
            // });

        </script>


     {{-- +"kod_stacji": "352220385"
    +"nazwa_stacji": "SIEDLCE"
    +"lon": "22.244722"
    +"lat": "52.181111"
    +"temperatura_gruntu": "8.7"
    +"temperatura_gruntu_data": "2025-07-17 00:10:00"
    +"wiatr_kierunek": "248"
    +"wiatr_kierunek_data": "2025-07-17 00:10:00"
    +"wiatr_srednia_predkosc": "0.2"
    +"wiatr_srednia_predkosc_data": "2025-07-17 00:10:00"
    +"wiatr_predkosc_maksymalna": "0.5"
    +"wiatr_predkosc_maksymalna_data": "2025-07-17 00:10:00"
    +"wilgotnosc_wzgledna": "94"
    +"wilgotnosc_wzgledna_data": "2025-07-17 00:10:00"
    +"wiatr_poryw_10min": "10"
    +"wiatr_poryw_10min_data": "2025-07-16 18:00:00"
    +"opad_10min": "0"
    +"opad_10min_data": "2025-07-17 00:00:00" --}}
   <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
</x-guest-layout>
