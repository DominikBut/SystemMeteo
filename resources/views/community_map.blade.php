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
                @livewire('map-community')
            </div>
        </div>
    </div>
    <div id="customAlert"></div>

    <script>

        // document.getElementById('ownedStationsOnly').addEventListener('change', function () {
        //     showOwnedOnly = this.checked;
        //     refreshMarkers();
        // });
        // let showOwnedOnly = false; // checkbox state

        let showOwnedOnly = false;

        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === 'ownedStationsOnly') {
                showOwnedOnly = e.target.checked;
                refreshMarkers();
            }
        });

        function refreshMarkers() {
            // 1) derive filtered data
            const data = Array.isArray(stacjeData) ? (showOwnedOnly ? stacjeData.filter(s => s.owned === true) : stacjeData) : [];

            // 2) wipe all known groups & remove from map
            [tempLayerGroup, humidityLayerGroup, windLayerGroup, rainLayerGroup, allStationsLayerGroup].forEach(g => {
                g.clearLayers();
                if (map.hasLayer(g)) map.removeLayer(g);
            });
            clickedLayerGroup.clearLayers();

            // 3) (re)draw current layer with filtered data
            if (data.length) updateMarkers(data, currentLayerType);
        }
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

        @if (Auth::id())
            // Custom control for checkbox
                L.Control.OwnedStations = L.Control.extend({
                    onAdd: function(map) {
                        const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                        div.style.backgroundColor = 'white';
                        div.style.padding = '6px';
                        div.style.fontSize = '13px';
                        div.style.cursor = 'default';

                        div.innerHTML = `
                            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;">
                                <input type="checkbox" id="ownedStationsOnly" />
                                <span class="ps-2">Pokaż tylko moje stacje</span>
                            </label>
                        `;

                        // Prevent map drag/zoom when clicking inside the control
                        L.DomEvent.disableClickPropagation(div);

                        return div;
                    },
                    onRemove: function(map) {
                        // nothing to clean up
                    }
                });

                // Add it to the map (top right, can be 'topleft', 'topright', 'bottomleft', 'bottomright')
                map.addControl(new L.Control.OwnedStations({ position: 'topright' }));

        @endif





            function focusStation(kod) {
                // const marker = stationMarkers[currentLayerType]?.[kod];

                var staccjacur = null;
                 stacjeData.forEach(stacja => {
                    if(stacja.station_id===kod)
                    {
                        staccjacur=stacja;
                    }
                 });

                 if(staccjacur)
                 {
                    //console.log(staccjacur);
                    let marker = L.circleMarker([staccjacur['lat'], staccjacur['lon']], {
                                            radius: 0,
                                        });
                    clickedLayerGroup.addLayer(marker);
                    Livewire.first().call('getStationDataid', staccjacur['station_id']);
                    if (marker) {
                        marker.bindPopup(`<div style="font-size: 14px; color: blue;" class="font-bold pb-1">${staccjacur['name']} <span class="!font-normal text-gray-500">[${staccjacur['station_id']}]</span></div>
                        <div class="flex flex-row justify-between items-center ${staccjacur['active'] ? 'text-lime-600': 'text-red-500'} pb-1"><b>Stacja ${staccjacur['owned'] ? '🚩prywatna': 'publiczna'} ${staccjacur['active'] ? 'aktywna': 'nieaktywna'}</b></div>
                        <div class="flex flex-row justify-between items-center"><b>Temp. powietrza: ${staccjacur['latest']['temp_air'] ?? '- '} °C</b></div>
                                                <div class="flex flex-row justify-between items-center"><b>Opad 10 min: ${staccjacur['latest']['rain_10min'] ?? '-'} mm</b></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wilgotność: ${staccjacur['latest']['humidity'] ?? '- '} %</b></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr śr.: ${staccjacur['latest']['wind_speed'] ?? '- '} m/s</b></div>
                                                <div class="flex flex-row justify-between items-center"><b>Wiatr kieruenk: ${renderWindDirection(staccjacur['latest']['wind_direction']) ?? '-'} ${staccjacur['latest']['wind_direction'] ?? '-'} °</b></div>
                                                <div class="flex flex-row justify-between items-center py-1">Pomiary wykonano: &nbsp${staccjacur['latest']['created_at'] ? convertToLocalDate(staccjacur['latest']['created_at']) : 'Brak pomiaru'}</div>
                                                    <div class=" flex flex-row justify-end">
                                                        <a class="hover:underline text-blue-500 text-nowrap"
                                                        href="{{ route('stacja_community').'?id='}}${staccjacur['station_id']}">
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

            function convertToLocalDate(dateStr) {
                if (!dateStr) return null;

                // Parse directly as ISO 8601 UTC string
                const utcDate = new Date(dateStr);

                if (isNaN(utcDate.getTime())) return null;

                // Return local date and time in Polish format
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

            function getTemperatureColor(temp) {

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
                                        fillColor: 'blue',
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
                    const layers = [tempLayerGroup, humidityLayerGroup, windLayerGroup, rainLayerGroup, allStationsLayerGroup];

                    function updateMarkers(data, Option) {
                if (Option === 'all') {
                    data.forEach(stacja => {
                        const lat = parseFloat(stacja.lat);
                        const lon = parseFloat(stacja.lon);

                        let color_lock = stacja.owned ? 'yellow' : '#666';
                        let weight_lock = stacja.owned ? 3 : 1;

                        const style = (stacja.active == false)
                        ? { radius: 5, color: color_lock, fillColor: 'red', fillOpacity: 0.8, weight: weight_lock }
                        : { radius: 5, color: '#666', fillColor: 'blue', fillOpacity: 0.8, weight: 1 };

                        const m = L.circleMarker([lat, lon], style)
                            .on('click', () => {
                                focusStation(stacja.station_id);
                                Livewire.first().call('getStationDataid', stacja.station_id);
                            })
                            .on('mouseover', function(){ this.setStyle({ radius: 8, color: '#333' }); })
                            .on('mouseout',  function(){ this.setStyle({ radius: 5, color: '#666' }); });

                        allStationsLayerGroup.addLayer(m);
                    });
                    allStationsLayerGroup.addTo(map);
                    return;
                }

                if (Option === 'temp') {
                    data.forEach(stacja => {
                        const lat = parseFloat(stacja.lat);
                        const lon = parseFloat(stacja.lon);
                        const temp = Number(stacja?.latest?.temp_air);
                        const lock = stacja.owned ? ' 🚩' : '';
                        const color = getTemperatureColor(temp);
                        const icon = L.divIcon({
                            className: 'custom-temp-icon',
                            html: `<div class="marker-label" style="opacity:0.85;background-color:${color}">${isFinite(temp)?temp.toFixed(1):'-'} °C${lock}</div>`,
                            iconSize: [80,20]
                        });
                        const marker = L.marker([lat, lon], { icon })
                            .on('click', () => {
                                focusStation(stacja.station_id);
                                Livewire.first().call('getStationDataid', stacja.station_id);
                            });
                        tempLayerGroup.addLayer(marker);
                    });
                    tempLayerGroup.addTo(map);
                    return;
                }

                if (Option === 'hum') {
                    data.forEach(stacja => {
                        const lat = parseFloat(stacja.lat);
                        const lon = parseFloat(stacja.lon);
                        const hum = Number(stacja?.latest?.humidity);
                        const lock = stacja.owned ? ' 🚩' : '';
                        const color = getHumidityColor(hum);
                        const icon = L.divIcon({
                            className: 'custom-temp-icon',
                            html: `<div class="marker-label" style="opacity:0.85;background-color:${color}">${isFinite(hum)?hum.toFixed(0):'-'} %${lock}</div>`,
                            iconSize: [65,20]
                        });
                        const marker = L.marker([lat, lon], { icon })
                            .on('click', () => {
                                focusStation(stacja.station_id);
                                Livewire.first().call('getStationDataid', stacja.station_id);
                            });
                        humidityLayerGroup.addLayer(marker);
                    });
                    humidityLayerGroup.addTo(map);
                    return;
                }

                if (Option === 'wind') {
                    data.forEach(stacja => {
                        const lat = parseFloat(stacja.lat);
                        const lon = parseFloat(stacja.lon);
                        const wind = Number(stacja?.latest?.wind_speed) || 0;
                        const dir  = stacja?.latest?.wind_direction;
                        const lock = stacja.owned ? ' 🚩' : '';

                        let marker;
                        if (wind !== 0) {
                            const color = getWindColor(wind);
                            const icon = L.divIcon({
                                className: 'custom-temp-icon',
                                html: `<div class="marker-label" style="opacity:0.85;background-color:${color}">${renderWindDirection(dir)} ${wind.toFixed(1)} m/s${lock}</div>`,
                                iconSize: [105,20]
                            });
                            marker = L.marker([lat, lon], { icon });
                        } else {
                            marker = L.circleMarker([lat, lon], allStacjicon);
                        }

                        marker.on('click', () => {
                            focusStation(stacja.station_id);
                            Livewire.first().call('getStationDataid', stacja.station_id);
                        });
                        windLayerGroup.addLayer(marker);
                    });
                    windLayerGroup.addTo(map);
                    return;
                }

                if (Option === 'rain') {
                    data.forEach(stacja => {
                        const lat = parseFloat(stacja.lat);
                        const lon = parseFloat(stacja.lon);
                        const r10 = Number(stacja?.latest?.rain_10min) || 0;
                        const lock = stacja.owned ? ' 🚩' : '';

                        let marker;
                        if (r10 !== 0) {
                            const color = getRainColor(r10);
                            const icon = L.divIcon({
                                className: 'custom-temp-icon',
                                html: `<div class="marker-label" style="border-color:lightblue;border-width:1px;opacity:.8;color:blue;background-color:${color}">${r10.toFixed(1)} mm${lock}</div>`,
                                iconSize: [80,20]
                            });
                            marker = L.marker([lat, lon], { icon });
                        } else {
                            marker = L.circleMarker([lat, lon], allStacjicon);
                        }

                        marker.on('click', () => {
                            focusStation(stacja.station_id);
                            Livewire.first().call('getStationDataid', stacja.station_id);
                        });
                        rainLayerGroup.addLayer(marker);
                    });
                    rainLayerGroup.addTo(map);
                    return;
                }
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
                 Livewire.on('clear-url-id', () => {
                    // Remove ?id=... from URL without reloading
                    const url = new URL(window.location.href);
                    url.searchParams.delete('id');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                });
                Livewire.on('layer-updated', (newData) => {
                    Alpine.nextTick(() => {
                        clearSearch();
                        let Data = newData[0];
                        let Time = newData[1];
                        let Option = newData[2];
                         //console.log(Data);
                        tempLayerGroup.clearLayers();
                                humidityLayerGroup.clearLayers();
                                windLayerGroup.clearLayers();
                                rainLayerGroup.clearLayers();
                                tempgLayerGroup.clearLayers();
                                allStationsLayerGroup.clearLayers();
                                clickedLayerGroup.clearLayers();
                                layers.forEach(layer => map.removeLayer(layer));
                        stacjeData = Array.isArray(Data) ? Data : [];
                        currentLayerType = Option;         // <-- keep in sync!

                        refreshMarkers();                  // <-- single source of truth
                         Data=null;
            });
        });
    })
        </script>
   <!-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead -->
</x-guest-layout>
