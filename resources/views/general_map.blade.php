<x-guest-layout>

    @pushOnce('css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""/>
            <style>
                #customAlert {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #db6435;
                color: #ffffff;
                padding: 12px 18px;
                border-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                font-family: sans-serif;
                font-size: 14px;
                display: none;
                z-index: 9999;
            }
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

            {{-- fullscreen --}}
            <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
            <link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />


    @endPushOnce

     <div class="py-2">
        <div class="mx-auto px-4 sm:px-2 lg:px-2 max-w-[120rem]">
            <div class="overflow-hidden">
                @livewire('map-recent')
            </div>
        </div>
    </div>
    <div id="customAlert"></div>

    <script>
        function showAlert(message, duration = 3000) {
            const alertBox = document.getElementById('customAlert');
            alertBox.textContent = message;
            alertBox.style.display = 'block';

            setTimeout(() => {
                alertBox.style.display = 'none';
            }, duration);
        }
                            var stacjeData = null;

                            let currentLayerType = 'temp'; // default visible

                            const allStationsLayerGroup = L.layerGroup();
                            const tempLayerGroup = L.layerGroup(); // create the group
                            const humidityLayerGroup = L.layerGroup();
                            const windLayerGroup = L.layerGroup();
                            const rainLayerGroup = L.layerGroup();
                            const tempgLayerGroup = L.layerGroup();
                            const clickedLayerGroup = L.layerGroup();

                            //setup
                            var map = L.map('map', {
                                renderer: L.canvas()
                            }).setView([52.25, 19.25], 6.5);
                            //credits
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                            }).addTo(map);
                            map.addControl(new L.Control.Fullscreen());
                             //console.log(Data);
                            map.addLayer(clickedLayerGroup);

            // function convertToLocalDate(dateStr) {
            //     if (!dateStr) return null;

            //     // Fix format: replace " " with "T" and mark as UTC with "Z"
            //     const isoStr = dateStr.replace(" ", "T") + "Z";

            //     const utcDate = new Date(isoStr);

            //     if (isNaN(utcDate.getTime())) return null;

            //     // Return local date and time (with seconds)
            //     return utcDate.toLocaleString('pl-PL', {
            //         hour: '2-digit',
            //         minute: '2-digit',
            //         second: '2-digit',
            //         year: 'numeric',
            //         month: '2-digit',
            //         day: '2-digit',
            //         hour12: false
            //     });
            // }

            // function isRecentEnough(localString, maxAgeMinutes = 120) {
            //     if (!localString) return false;

            //     // Create a Date from the given string without forcing UTC
            //     const dateObj = new Date(localString.replace(" ", "T"));

            //     if (isNaN(dateObj)) return false; // Invalid date safeguard

            //     const now = new Date();
            //     const diffMinutes = (now - dateObj) / (1000 * 60);

            //     return diffMinutes >= 0 && diffMinutes <= maxAgeMinutes;
            // }

            function focusStation(kod) {
                // const marker = stationMarkers[currentLayerType]?.[kod];

                var staccjacur = null;
                 stacjeData.forEach(stacja => {
                    if(stacja.kod_stacji===kod)
                    {
                        staccjacur=stacja;
                    }
                 });
                 if(staccjacur)
                 {
                    let marker = L.circleMarker([staccjacur['lat'], staccjacur['lon']], {
                                            radius: 0,
                                        });
                    clickedLayerGroup.addLayer(marker);
                    Livewire.first().call('getStationDataid', staccjacur['kod_stacji']);
                    if (marker) {
                        marker.bindPopup(`<div style="font-size: 14px; color: blue;" class="font-bold pb-2">${staccjacur['nazwa_stacji']} <span class="!font-normal text-gray-500">[${staccjacur['kod_stacji']}]</span></div>
                                                <div class="flex flex-row justify-between items-center"><b>Temp. powietrza: ${staccjacur['temperatura_powietrza'] ?? '- '} °C</b>&nbsp&nbsp<sub>${staccjacur['temperatura_powietrza_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Temp. gruntu: ${staccjacur['temperatura_gruntu'] ?? '- '} °C</b>&nbsp&nbsp<sub>${staccjacur['temperatura_gruntu_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Opad 10 min: ${staccjacur['opad_10min'] ?? '-'} mm</b>&nbsp&nbsp<sub>${staccjacur['opad_10min_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wilgotność: ${staccjacur['wilgotnosc_wzgledna'] ?? '- '} %</b>&nbsp&nbsp<sub>${staccjacur['wilgotnosc_wzgledna_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr śr.: ${staccjacur['wiatr_srednia_predkosc'] ?? '- '} m/s</b>&nbsp&nbsp<sub>${staccjacur['wiatr_srednia_predkosc_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr maks.: ${staccjacur['wiatr_predkosc_maksymalna'] ?? '- '} m/s</b>&nbsp&nbsp<sub>${staccjacur['wiatr_predkosc_maksymalna_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr poryw: ${staccjacur['wiatr_poryw_10min'] ?? '- '} m/s</b>&nbsp&nbsp<sub>${staccjacur['wiatr_poryw_10min_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr kieruenk: ${renderWindDirection(staccjacur['wiatr_kierunek']) ?? '-'} ${staccjacur['wiatr_kierunek'] ?? '-'} °</b>&nbsp&nbsp<sub>${staccjacur['wiatr_kierunek_data'] ?? 'Brak pomiaru'}</sub></div>
                                                <div class="pt-2 text-end flex flex-col justify-end ">
                                                    <div class="text-xs flex flex-row gap-2 justify-end">
                                                        <a class="hover:underline text-gray-500 text-nowrap"
                                                        href="{{ route('stacja_archive').'/?id='}}${staccjacur['kod_stacji']}">
                                                            Dane archiwalne
                                                        </a>
                                                        <a class="hover:underline text-blue-500 text-nowrap"
                                                        href="{{ route('stacja_recent').'/?id='}}${staccjacur['kod_stacji']}">
                                                            Dane bieżące
                                                        </a>
                                                    </div>
                                            </div>
                                                `);
                        marker.on('popupclose', function () {
                                                // Livewire.first().call('getStationDataid',null);
                                                clickedLayerGroup.removeLayer(marker);
                                            });
                        map.setView(marker.getLatLng(), 10);
                        marker.openPopup();
                    }
                }else{
                     showAlert('Przełącz wyświetlane dane na mapie lub wybierz istniejącą stację!');
                }
            }

            // function toggleLayer(typeName) {
            //     const layers = [tempLayerGroup, tempgLayerGroup, humidityLayerGroup, windLayerGroup, rainLayerGroup, allStationsLayerGroup,clickedLayerGroup];

            //     switch (typeName) {
            //         case 'tempg':
            //             activeLayer=tempgLayerGroup;
            //             break;
            //         case 'hum':
            //             activeLayer=humidityLayerGroup;
            //             break;
            //         case 'wind':
            //             activeLayer=windLayerGroup;
            //             break;
            //         case 'rain':
            //             activeLayer=rainLayerGroup;
            //             break;
            //         case 'all':
            //             activeLayer=allStationsLayerGroup;
            //             break;
            //         default:
            //             activeLayer=tempLayerGroup;
            //             break;
            //     }

            //     // Remove all layers
            //     layers.forEach(layer => map.removeLayer(layer));

            //     // Add chosen layer
            //     if (!map.hasLayer(activeLayer)) {
            //         map.addLayer(activeLayer);
            //          map.addLayer(clickedLayerGroup);
            //         currentLayerType = typeName;
            //         //updateStationListAvailability();
            //     }
            // }

            function getTemperatureColor(temp) {
                // // Normalize temperature between -20 and 40 (adjust to your real range)


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

            document.getElementById('stationSearch').addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const items = document.querySelectorAll('#stationList .station-list-item');
                let visibleCount = 0;

                items.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    const matches = text.includes(searchTerm);

                    item.style.display = matches ? 'block' : 'none';

                    if (matches) {
                        visibleCount++;
                    }
                });

                let noResultsEl = document.getElementById('noResultsMessage');
                if (!noResultsEl) {
                    noResultsEl = document.createElement('div');
                    noResultsEl.id = 'noResultsMessage';
                    noResultsEl.textContent = 'Brak wyników wyszukiwania';
                    noResultsEl.style.display = 'none';
                    noResultsEl.style.padding = '10px';
                    noResultsEl.style.color = 'gray';
                     noResultsEl.style.textAlign = 'center';
                    document.getElementById('stationList').appendChild(noResultsEl);
                }

                noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
            });


            let allStacjicon = {
                                        radius: 5,
                                        color: '#666',
                                        fillColor: 'lightblue',
                                        fillOpacity: 0.8,
                                        weight: 1
                                    };


        function renderWindDirection(wiatr_kierunek) {
            if (wiatr_kierunek === null || wiatr_kierunek === undefined) {
                return `<span>-</span>`;
            }

            const rotation = isNaN(Number(wiatr_kierunek)) ? 0 : Number(wiatr_kierunek);

            return `
                <span>
                    <div class="inline-block transform text-sm px-1" style="font-weight: 900; rotate: ${rotation+90}deg;">
                        ➤
                    </div>
                </span>
            `;
        }
         const layers = [tempLayerGroup, tempgLayerGroup, humidityLayerGroup, windLayerGroup, rainLayerGroup, allStationsLayerGroup];

         function updateMarkers(stacjeData,Option){
                // map.setView([52.25, 19.25], 6.5);

                                    // Loop through Laravel data passed to JS
                                stacjeData.forEach(stacja => {

                                    const lat = parseFloat(stacja.lat);
                                    const lon = parseFloat(stacja.lon);

                                    // // === Temperature gruntu ===
                                    if(Option==='all'){
                                        let allStationsmarkers = [];
                                        const markerc = L.circleMarker([lat, lon], allStacjicon);
                                        markerc.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                        markerc.on('mouseover', function () {
                                            this.setStyle({ radius: 8, color: '#333' });
                                        });
                                        markerc.on('mouseout', function () {
                                            this.setStyle({ radius: 6, color: '#666' });
                                        });
                                        allStationsmarkers.push(markerc);
                                         allStationsLayerGroup.addLayer(L.layerGroup(allStationsmarkers));
                                            allStationsmarkers =null;
                                            allStationsLayerGroup.addTo(map); // show by default

                                    }
                                    // // === Temperature gruntu ===
                                    if(Option==='tempg'){
                                        let tempgmarkers = [];

                                            let temp = parseFloat(stacja.temperatura_gruntu);
                                            let color = getTemperatureColor(temp);
                                            let icon = L.divIcon({
                                                className: 'custom-temp-icon',
                                                html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${temp.toFixed(1)} °C</div>`,
                                                iconSize: [55, 20],
                                            });
                                            let marker = L.marker([lat, lon], { icon });

                                            marker.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                            tempgmarkers.push(marker);

                                            tempgLayerGroup.addLayer(L.layerGroup(tempgmarkers));
                                            tempgmarkers =null;
                                            tempgLayerGroup.addTo(map); // show by default
                                    }


                                    if(Option==='temp'){
                                        let tempmarkers = [];
                                        // === Temperature powietrza ===

                                            let temp = parseFloat(stacja.temperatura_powietrza);
                                            let color = getTemperatureColor(temp);
                                            let icon = L.divIcon({
                                                className: 'custom-temp-icon',
                                                html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${temp.toFixed(1)} °C</div>`,
                                                iconSize: [55, 20],
                                            });

                                            let marker = L.marker([lat, lon], { icon });

                                            marker.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                            tempmarkers.push(marker);

                                            tempLayerGroup.addLayer(L.layerGroup(tempmarkers));
                                            tempmarkers =null;
                                            tempLayerGroup.addTo(map); // show by default
                                    }

                                    // // === Humidity ===
                                    if(Option==='hum'){
                                        let humiditymarkers =[];

                                            let hum = parseFloat(stacja.wilgotnosc_wzgledna);
                                            let color = getHumidityColor(hum);
                                            let icon = L.divIcon({
                                                className: 'custom-temp-icon',
                                                html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${hum.toFixed(0)} %</div>`,
                                                iconSize: [55, 20],
                                            });
                                            let marker = L.marker([lat, lon], { icon });

                                            marker.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                            humiditymarkers.push(marker);

                                            humidityLayerGroup.addLayer(L.layerGroup(humiditymarkers));
                                            humiditymarkers =null;
                                            humidityLayerGroup.addTo(map); // show by default
                                }

                                    // // === Wind ===
                                    if(Option==='wind'){
                                        let windmarkers =[];

                                            let marker;
                                            if(stacja.wiatr_srednia_predkosc != 0){
                                            let wind = parseFloat(stacja.wiatr_srednia_predkosc);
                                            let color = getWindColor(wind);
                                            let icon = L.divIcon({
                                                className: 'custom-temp-icon',
                                                html: `<div class="marker-label" style="opacity: 0.85; background-color:${color}">${renderWindDirection(stacja.wiatr_kierunek)} ${wind.toFixed(1)} m/s</div>`,
                                                iconSize: [80, 20],
                                            });
                                             marker = L.marker([lat, lon], { icon });
                                            }else{
                                                marker = L.circleMarker([lat, lon], allStacjicon);
                                            }
                                            marker.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                            windmarkers.push(marker);

                                            windLayerGroup.addLayer(L.layerGroup(windmarkers));
                                            windmarkers =null;
                                            windLayerGroup.addTo(map); // show by default
                                    }

                                    // // === Rainfall ===
                                    if(Option==='rain'){
                                        let rainmarkers =[];

                                            let marker;
                                            if(stacja.opad_10min != 0){
                                            let rain = parseFloat(stacja.opad_10min);
                                            let color = getRainColor(rain);
                                            let icon = L.divIcon({
                                                className: 'custom-temp-icon',
                                                html: `<div class="marker-label" style="border-color: lightblue; border-width: 1px; opacity: 0.8; color: blue; background-color:${color}">${rain.toFixed(1)} mm</div>`,
                                                iconSize: [55, 20],
                                            });

                                                marker = L.marker([lat, lon], { icon });
                                            }else{
                                                marker = L.circleMarker([lat, lon], allStacjicon);
                                            }

                                            marker.on('click', function () {
                                                focusStation(stacja.kod_stacji);
                                                Livewire.first().call('getStationDataid', stacja.kod_stacji);
                                            });
                                            rainmarkers.push(marker);

                                            rainLayerGroup.addLayer(L.layerGroup(rainmarkers));
                                            rainmarkers =null;
                                            rainLayerGroup.addTo(map); // show by default
                                }

                    });
            }
            function clearSearch() {
                const searchInput = document.getElementById('stationSearch');
                searchInput.value = ''; // Clear the input


            }
            document.addEventListener('livewire:init', () => {
                Livewire.on('open', data => {
                    setTimeout(() => {
                        Alpine.nextTick(() => {
                            console.log(`${data.id}`);
                            focusStation(`${data.id}`);
                        });
                    }, 1000); // delay in milliseconds (500ms = half a second)
                });
                Livewire.on('layer-updated', (newData) => {
                    Alpine.nextTick(() => {
                        clearSearch();
                        let Data = newData[0];
                        let Time = newData[1];
                        let Option = newData[2];
                        tempLayerGroup.clearLayers();
                                humidityLayerGroup.clearLayers();
                                windLayerGroup.clearLayers();
                                rainLayerGroup.clearLayers();
                                tempgLayerGroup.clearLayers();
                                allStationsLayerGroup.clearLayers();
                                clickedLayerGroup.clearLayers();
                                layers.forEach(layer => map.removeLayer(layer));
                        if (Array.isArray(Data) && Data.length > 0) {
                                stacjeData = Data;
                                updateMarkers(stacjeData, Option);

                        } else {
                            console.log('Nie ładuję  – brak danych');
                        }
                         Data=null;
            });
        });
    })
        </script>
   <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
</x-guest-layout>
