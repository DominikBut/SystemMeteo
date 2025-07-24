<div>
    <div class="p-4 space-y-4">
        <div class="grid grid-cols-4 gap-2">
            <input type="text" wire:model="stationId" placeholder="Kod stacji" value="249190560" class="border p-2 rounded" />
            <input type="number" wire:model="year" placeholder="Rok" value="2025" class="border p-2 rounded" />
            <input type="number" wire:model="month" placeholder="Miesiąc" value="4" class="border p-2 rounded" />
            {{-- <input type="number" wire:model="day" placeholder="Dzień" class="border p-2 rounded" /> --}}
        </div>

        <button wire:click="loadData" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Pokaż dane
        </button>
        // TODO odczyt z listy stacji z serwera pliku wybor z searchem z listy -> wybor -> zapytanie do api na podstawie id + odczyt z csv <br>
        terminowe-> 3 na dzien rozne godziny 6 12 18 bez min max tylko srednie <br>
        dobowe -> 1 na dzien plus min max temp bez wiatru <br>
        miesieczne -> tylko stare lata od 2024 ->reszta samemu wyliczyc <br>
        hydro -> useless <br>
        synop -> strasznie duzo o chmurasz lodzie itp <br>
        <br>jak nie znajdzie data w plikach w katalogu archive i w serverze to sprawdza czy jest w api-data przerobiona wersja ale za to jest dopisek ze nie oficjalne -> moze dac checka w w komendzie co robi download zeby jak sie miesiac zmienia czy rok to robilo tez zestawienie miesieczne i roczne z pozostalych
        <br>last 7 days full time
        <br>reszta do terminowych co 6 h temp 6 12 18 i z tego dobowez dobowych miesieczne??
        <br>mojew dane:
        <br>dla last 7 days mam full 24h co 30min (api-data)
        <br>miesiac caly -> terminowe 3 wartosci na dzien 6,12,18 (collected/terminowe)
        <br>jezeli chodzi o wystwielanie to zrobic warunek ze najpierw szuka w archive czy jest plik potem dowload czy jest  jezeli nie to wyswietl z collected i zrobic 2 zmienne zeby latwiej to zrozoznic przy wyswietlaniu)
        <br>fo wyswietlania last 24h z api-data i last 7 days
        <br>last 7 24h tez na biezoco min amx
        <br>jak wyswietlam terminowe to max min na dzien liczone na biezaca (wybor dnia z miesiaca lub caly miesiac z 3 kreskamik z godzinami)
        <br>
        @if ($info)
            <div class="text-red-600">{{ $info }}</div>
        @endif
        @if ($error)
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
        @endif
    </div>

</div>
