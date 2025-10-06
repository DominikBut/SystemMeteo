<x-guest-layout>
    <div class="py-8 h-full  min-h-9/10 flex flex-col items-center">
        <div class="relative max-w-full m-auto px-2 xl:px-0">
            <div class="bg-white overflow-hidden shadow-md rounded-lg border-1 border-blue-300">
                <div class="flex flex-col items-center justify-center ">
                    <div class="w-full  p-6 bg-white border-b-4 border-blue-200 ">
                                                <div class="w-full  text-start sm:px-2 py-3 sm:py-4 flex flex-row items-center gap-2">
                                                    <a href="{{ route('welcome') }}">
                                                        <x-application-mark class="size-10"/>
                                                    </a>
                                                    <a href="{{ route('welcome') }}" class="w-fit text-2xl sm:text-4xl font-bold text-black tracking-widest">
                                                        <span class="sr-only">homepage</span>
                                                        System Meteo
                                                    </a>
                                                </div>

                        <p class="text-gray-500 ps-2 sm:ps-4 sm:leading-relaxed sm:max-w-4xl  sm:text-balance text-xs sm:text-sm lg:text-base font-bold">
                        System służący do archiwizacji i wizualizacji danych meteorologicznych oparty o technologię Laravel 12</p>
                    </div>

                    <div class="max-w-full my-auto px-2 xl:px-0 h-full !text-wrap truncate">
                        <div class="overflow-hidden rounded-lg !text-wrap truncate">
                            <div class="p-6 sm:px-10 sm:pt-4 bg-white !text-wrap truncate" >
                                <h1 class="text-sm lg:text-2xl font-bold text-left text-sky-950 place-content-center tracking-wider">
                                    Informacje o systemie
                                </h1>
                                <p class="mt-3  text-gray-500 leading-relaxed sm:max-w-4xl text-wrap text-xs sm:text-sm ">

                                    <b class="text-wrap">System Meteo to kompleksowe narzędzie pozwalające na odczyt wybranych przez użytkownika oficjalnych
                                        danych meteorologicznych pozyskiwanych od Instytutu Meteorologii i Gospodarki Wodnej (IMGW) – Państwowego Instytutu Badawczego przez API oraz archiwum plików, a także danych społecznościowych i niezweryfikowanych bieżących danych klimatycznych
                                        archiwizowanych bezpośrednio na serwerze. Dane m.in. temperatury powietrza i gruntu, wilgotności względnej, prędkości i kierunku wiatru, a także opadu z ostatnich 10 min są prezentowane
                                        w postaci tabelarycznej oraz graficznej zarówno na mapie, jak i na wykresach.
                                </b><br><br>
                                    <b>Funkcjonalności systemu:</b><br>
                                    - Odczyt najnowszych danych meteorologicznych wybranej stacji dostępnej przez API IMGW wraz z prezentacją,<br>
                                    - Odczyt najnowszych danych meteorologicznych wszystkich stacji dostępnych przez API IMGW wraz z prezentacją na interkatywnej mapie,<br>
                                    - Codzienny odczyt i archiwizacja 30-minutowa bieżących danych meteorologicznych wszystkich stacji dostępnych przez API IMGW w postaci dziennych plików w formacie JSON oraz przygotowywanie terminowych, dobowych, miesięcznych plików sumarycznych zawierających odpowiednie średnie sumy, maksyma, minima parametrów wszystkich stacji, również w formatach JSON,<br>
                                    - Odczyt oficjalnych zweryfikowanych danych meteorologicznych-klimatycznych dla wybranej stacji z archiwum plików IMGW wraz z prezentacją,<br>
                                    - Interfejs POST API odbioru, autoryzacji i archiwizacji bieżących danych stacji pogodowych użytkowników społeczności,<br>
                                    - Odczyt danych meteorologicznych dla wybranej stacji społeczności z bazy danych systemu wraz z prezentacją,<br>
                                    <br>
                                    <b>Źródła danych wykorzystywanych przez system:</b><br>
                                    - <b>Oficjalne dane publiczne IMGW:</b> <a class="underline truncate" href="https://danepubliczne.imgw.pl/pl/introduction">https://danepubliczne.imgw.pl/pl/introduction</a>,<br>
                                    - <b>IMGW API: </b><a class="underline truncate" href="https://danepubliczne.imgw.pl/api/data/meteo/">https://danepubliczne.imgw.pl/api/data/meteo/</a>,<br>
                                    - <b>Oficjalne dane archiwalne IMGW plikowe (CSV) pomiarowo-obserwacyjne - dane meteorologiczne: </b><a class="underline truncate" href="https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/">https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/</a><br>
                                    (system wykorzystuje tylko dane z katalogów:</b> "terminowe/klimat/", "dobowe/klimat/", "miesieczne/klimat/"),<br>
                                    - <b>Dla wyboru danych bieżących lista stacji pozyskiwana jest okresowo z API, natomiast dla danych archiwalnych jest połączona ze stacjami z pliku CSV dostępnego w archiwum IMGW:</b> <a class="underline truncate" href="https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv">wykaz_stacji.csv</a>,<br>
                                    <br>
                                    <b>Technologie wykorzystane przy tworzeniu systemu:</b><br>
                                    - <a class="underline" href="https://laravel.com/">Laravel v12</a>,<br>
                                    - <a class="underline" href="https://jetstream.laravel.com/introduction.html">Laravel Jetstream v5</a>,<br>
                                    - <a class="underline" href="https://livewire.laravel.com/">Livewire v3</a>,<br>
                                    - <a class="underline" href="https://alpinejs.dev/">Alpine.js v3</a>,<br>
                                    - <a class="underline" href="https://www.chartjs.org/docs/latest/">Chart.js v4.5.0</a>.<br>
                                    - <a class="underline" href="https://filamentphp.com/">Filament v3</a>,<br>
                                    - <a class="underline" href="https://tailwindcss.com/">TailwindCSS v4.1</a>,<br>
                                    - <a class="underline" href="https://leafletjs.com/">Leaflet v1.9.4</a>,<br>
                                    - <a class="underline" href="https://github.com/Leaflet/Leaflet.fullscreen">Plugin Leaflet.fullscreen</a>,<br>
                                    - <a class="underline" href="https://filamentphp.com/plugins/dotswan-map-picker">Plugin Filament v3 Map Picker v1.8.8</a>.<br>
                                    <br>
                                    <b>Co oznaczają rodzaje agregacji:</b><br>
                                    - <b>30-minutowa</b> - dane surowe pozyskiwane bezpośrednio z API co 30 minut dla całego dnia, dostępne tylko przez 8 dni (z dniem aktualnie zapisywanym), <br>
                                    - <b>terminowa</b> - dane dobowe surowe lub zweryfikowane zawierające po 4 pomiary agregujące lub 3 pomiary dla jednego dnia w przypadku zweryfikowanych, do godz. 6, do godz. 12, do godz. 18, plus do godz. 24 w przypadku danych surowych, <br>
                                    - <b>dobowa</b> - dane miesięczne surowe lub zweryfikowane zawierające pomiary agregujące, sumaryczne, minimalne, maksymalne, średnie dla każdego dnia wybranego miesiąca, <br>
                                    - <b>miesięczna</b> - dane roczne surowe lub zweryfikowane zawierające pomiary agregujące, sumaryczne, minimalne, maksymalne, średnie dla każdego miesiąca wybranego roku. <br>
                                <br>
                                    <b>Zasada archiwizacji danych z IMGW API:</b><br>
                                    Dane archiwizowane są dla wszystkich dostępnych stacji pogodowych przez 24 h/7 dni w tygodniu co 30 minut, tzn. CronJob serwera systemu uruchamia polecenie Artisan Laravel wysyłające zapytanie do API IMGW. Dane następnie są dopisywane do pliku JSON z datą bieżącego dnia.<br>
                                    (nowy plik tworzony jest z wykryciem nowego dnia w chociaż jednej z dat pomiarów uzyskiwanych z API, było to koniecznością, gdyż API IMGW udostępnia dane z opóźnieniem 20 minutowym np. o 11:30 dostajemy dane z 11:10 - pomiary IMGW wykonywane są co 10 minut) <br>
                                    Następnie, jeżeli faktycznie wykryto nowy dzień, dane z pliku z dnia poprzedniego są konwertowane do 4 agregujących pomiarów na dzień tzn. od 00:00 do 5:59, 6:00 - 11:59, 12:00 - 17:59, 18:00 - 23:59 i zapisywane do pliku JSON z datą dnia poprzedniego w katalogu "terminowe". <br>
                                    Po zapisaniu pliku z danymi terminowymi wykonywane jest wyliczanie parametrów średnich, maksymalnych, minimalnych, sumarycznych itp. na podstawie pliku terminowego dla również całej doby poprzedniej i dopisanie ich do pliku JSON miesięcznego odpowiadającego miesiącowi dnia poprzedniego znajdującego się w katalogu "dobowe", jeżeli nie istnieje taki plik to jest on tworzony.<br>
                                    Po wyliczeniu wartości dobowych aktualizowane są w pliku JSON rocznym w katalogu "miesieczne", odpowiadającym roku dnia poprzedniego, parametry miesiąca dnia poprzedniego, jeżeli nie istnieje taki plik jest tworzony.<br>
                                    Dopiero na końcu całego procesu usuwane są z bieżącego katalogu pliki JSON starsze od 7 dni z danymi pozyskiwanymi co 30 minut z API IMGW.<br>
                                    <br>
                                    <b>UWAGA! Dane są poprawnie archiwizowane od początku września 2025 roku, wcześniej zarchiwizowane dane mogą zawierać błędy lub luki.</b>
                                    <br>
                                    <br>
                                    <b>Autor aplikacji internetowej systemu meteo:</b><br>
                                    But Dominik,<br>
                                    Uniwersytet Przyrodniczy w Poznaniu,<br>
                                    <a href="https://wisim.up.poznan.pl/" class="underline">WYDZIAŁ INŻYNIERII ŚRODOWISKA I INŻYNIERII MECHANICZNEJ,</a><br>
                                    Informatyka i Inżyniera Danych, 2 stopień.<br>
                                    Kontakt: <a href="mailto:butdominik.biz@gmail.com" class="text-blue-500 hover:underline">butdominik.biz@gmail.com</a></p>
                                    <br>
                                    <b>System wykonany na potrzeby pracy dyplomowej magisterskiej pod kierunkiem dr inż. Sebastiana Kujawy.</b><br>
                                    Data wydania: wrzesień 2025.                            </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

