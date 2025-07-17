<x-guest-layout>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-lime-100 overflow-hidden shadow-xl sm:rounded-lg">
                {{ $status }}

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
</div>
</x-guest-layout>
