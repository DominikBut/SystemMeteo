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
            </style>
    @endPushOnce
    @pushOnce('scripts')
        <!-- Make sure you put this AFTER Leaflet's CSS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    @endPushOnce
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-lime-100 overflow-hidden shadow-xl sm:rounded-lg">
                {{ $status }}
                <div id="map" class=" min-h-[48rem]"></div>
                @if (!empty($data))
                        {{-- {{ dd($data) }} --}}
                        <div class="text-xs">
                            <table>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>lon</th>
                                    <th>lat</th>
                                    <th>data</th>
                                    <th>tmp</th>
                                    <th>wiatr</th>
                                    <th>wilg</th>
                                </tr>

                            @foreach ( $data as $stacja )
                                <tr>
                                <td> {{ $stacja->nazwa_stacji }} </td>
                                <td> {{ $stacja->lon }} </td>
                                <td> {{ $stacja->lat }} </td>
                                <td> {{ $stacja->temperatura_gruntu_data }} </td>
                                <td> {{ $stacja->temperatura_gruntu }} </td>
                                <td> {{ $stacja->wiatr_srednia_predkosc }} </td>
                                <td> {{ $stacja->wilgotnosc_wzgledna }} </td>
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
    </script>
    @pushOnce('scripts2')
        <script>
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
            var map = L.map('map').setView([52.25, 19.25], 7);
            //wymagany copyright
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
            // Loop through Laravel data passed to JS
            stacjeData.forEach(stacja => {
                if (stacja.lat && stacja.lon) {
                    const hasTemp = stacja.temperatura_gruntu !== null && stacja.temperatura_gruntu !== '' && stacja.temperatura_gruntu_data;
                    const tempDateStr = stacja.temperatura_gruntu_data;
                     // Skip if temp date is missing or invalid
                    if (!tempDateStr || isNaN(new Date(tempDateStr))) return;

                    if (!hasTemp) return; // Skip if no temperature

                    // Get only the date part (YYYY-MM-DD)
                    const tempDate = new Date(tempDateStr).toISOString().slice(0, 10);
                    const today = new Date().toISOString().slice(0, 10);

                    // Skip if the temp data is before today
                     if (tempDate < today) return;

                    const temp = parseFloat(stacja.temperatura_gruntu);
                    const tempText = `${temp.toFixed(1)}°C`;
                    const bgColor = getTemperatureColor(temp);

                    const tempIcon = L.divIcon({
                        className: 'custom-temp-icon',
                        html: `<div class="marker-label" style="background-color:${bgColor}">${tempText}</div>`,
                        iconSize: [50, 20],
                    });

                    const marker = L.marker(
                        [parseFloat(stacja.lat), parseFloat(stacja.lon)],
                        { icon: tempIcon }
                    ).addTo(map);

                    marker.bindPopup(`
                        <strong>${stacja.nazwa_stacji}</strong><br>
                        Data: ${stacja.temperatura_gruntu_data ?? 'brak'}<br>
                        Temp: ${tempText}<br>
                        Wiatr: ${stacja.wiatr_srednia_predkosc ?? 'brak'} m/s<br>
                        Wilgotność: ${stacja.wilgotnosc_wzgledna ?? 'brak'}%
                    `);
                }
            });
            // stacjeData.forEach(stacja => {
            //     if (stacja.lat && stacja.lon) {
            //         const hasTemp = stacja.temperatura_gruntu !== null && stacja.temperatura_gruntu !== '';

            //         if (!hasTemp) return; // ⛔ Skip stations with no temperature

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
            var marker_click = null; // initialize

            function onMapClick(e) {
                if (marker_click) {
                    map.removeLayer(marker_click); // remove previous marker
                }

                marker_click = L.marker(e.latlng).addTo(map);
                marker_click.bindPopup(
                    "<b>You clicked the map at</b> " +
                    e.latlng.lat.toFixed(5) + " " +
                    e.latlng.lng.toFixed(5)
                ).openPopup();
            }

            map.on('click', onMapClick);
        </script>
    @endPushOnce

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
