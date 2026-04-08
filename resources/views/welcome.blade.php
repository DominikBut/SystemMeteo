<x-guest-layout>
<div class="py-8 h-full m-auto min-h-9/10 flex flex-col items-center">
    <div class="relative  max-w-7xl m-auto px-4">
        <div class="bg-white  overflow-hidden shadow-md rounded-lg border-1 border-blue-300 ">


            <div class=" w-full flex flex-col items-center justify-center ">
                <div class="w-full p-6 lg:p-8 bg-white border-b-4 border-blue-200 ">
                                <div class="w-full text-start px-2 py-3 sm:py-4 flex flex-row items-center gap-2">
                                                <a href="{{ route('welcome') }}">
                                                    <x-application-mark class="size-10"/>
                                                </a>
                                                <a href="{{ route('welcome') }}" class="w-fit text-2xl sm:text-4xl font-bold text-black tracking-widest">
                                                    <span class="sr-only">homepage</span>
                                                    System Meteo
                                                </a>
                                            </div>

                    <p class="text-gray-500 sm:ps-6 sm:leading-relaxed sm:max-w-4xl  sm:text-balance text-xs sm:text-sm lg:text-base font-bold">
                    System służący do archiwizacji i wizualizacji danych meteorologicznych oparty o technologię Laravel 12</p>
                </div>

                <div class="bg-blue-100 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-16 p-6 lg:p-12">
                    <div>
                        <div class="flex items-center">

                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                                                </svg>
                            <h2 class="ms-3 lg:text-xl font-bold text-gray-900">
                                <a href="{{ route('map') }}" class="tracking-wide flex flex-col border-blue-600
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                        <div class="flex flex-row place-content-center justify-center place-items-center text-wrap">
                                            Zobacz mapę aktualnych pomiarów stacji IMGW

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                            </svg>

                                        </div>

                                        <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                </a>
                            </h2>
                        </div>
                        <p class="mt-2 sm:mt-4 ps-6 text-gray-500 text-xs sm:text-sm sm:sm:leading-relaxed text-pretty">
                            Zobacz na mapie Polski najnowsze, aktualnie odebrane od stacji pogodowych IMGW, pomiary temperatur powietrza i gruntu, prędkości i kierunku wiatru, poziomu wilgotności i opadu.
                        </p>

                    </div>

                    <div>
                        <div class="flex items-center">

                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>

                            <h2 class="ms-3 lg:text-xl font-bold text-gray-900">
                                <a href="{{ route('stacja_archive') }}" class="tracking-wide flex flex-col border-blue-600
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                        <div class="flex flex-row place-content-center justify-center place-items-center text-wrap">
                                            Sprawdź archiwum oficjalnych danych IMGW
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                            </svg>

                                        </div>
                                        <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                </a>
                            </h2>
                        </div>

                        <p class="mt-2 sm:mt-4 ps-6 text-gray-500 text-xs sm:text-sm sm:leading-relaxed">
                            Wybierz stację pogodową IMGW i sprawdź zweryfikowane archiwalne dane meteorologiczne IMGW, gromadzone przez lata, rozmieszone na wykresach i w tabelach statystycznych.
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center">

                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>


                            <h2 class="ms-3 lg:text-xl font-bold text-gray-900">
                                <a href="{{ route('stacja_recent') }}" class="tracking-wide flex flex-col border-blue-600
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                        <div class="flex flex-row place-content-center justify-center place-items-center text-wrap">
                                            Sprawdź lokalne archiwum danych IMGW
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                            </svg>

                                        </div>

                                        <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                </a>
                            </h2>
                        </div>

                        <p class="mt-2 sm:mt-4 ps-6 text-gray-500 text-xs sm:text-sm sm:leading-relaxed">
                            Wybierz stację pogodową IMGW i sprawdź archiwizowane na bieżąco przez system dane meteorologiczne, rozmieszone na wykresach i w tabelach statystycznych.
                        </p>

                    </div>

                    <div>
                        <div class="flex items-center">

                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>


                            <h2 class="ms-3 lg:text-xl font-bold text-gray-900">
                                <a href="{{ route('stacja_community') }}" class="tracking-wide flex flex-col border-blue-600
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                        <div class="flex flex-row place-content-center justify-center place-items-center text-wrap">
                                            Sprawdź pomiary stacji społeczności
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                            </svg>

                                        </div>

                                        <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                </a>
                            </h2>
                        </div>

                        <p class="mt-2 sm:mt-4 ps-6 text-gray-500 text-xs sm:text-sm sm:leading-relaxed">
                            Wybierz stację pogodową użytkownika społeczności i sprawdź archiwizowane na bieżąco przez system dostępne dane meteorologiczne, rozmieszone na wykresach i w tabelach statystycznych.
                        </p>

                    </div>
                </div>
                <div class="p-4 lg:p-12 items-center bg-white border-t-4 border-blue-200 w-full grid grid-cols-1 sm:grid-cols-2">
                            @if (Route::has('login'))

                                @auth
                                <a href="{{ url('/dashboard') }}" class="cols-span-1 group rounded-lg max-w-md bg-slate-50 p-4 shadow-md ring-1 ring-blue-300 transition ease-in-out duration-300  hover:ring-blue-400  ">
                                    <div class="flex items-center">
                                             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                        <h2 class="ms-3 lg:text-lg font-bold text-gray-900">
                                            <div  class="tracking-wide flex flex-col border-blue-300
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                                    <div class="flex flex-row place-content-center justify-center place-items-center">
                                                        Przejdź do listy stacji pogodowych
                                                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                            </svg>

                                                    </div>

                                                    <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                                </div>
                                        </h2>
                                    </div>

                                    <p class="mt-2  ps-6 text-gray-500 text-xs  sm:leading-relaxed">
                                       Przejdź do listy stacji pogodowych należących do twojego konta.
                                    </p>

                                </a>
                            @else
                            <a href="{{ route('login') }}" class="cols-span-1 group rounded-lg max-w-md bg-slate-50 p-4 shadow-md ring-1 ring-blue-300 transition ease-in-out duration-300  hover:ring-blue-400  ">
                                <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-blue-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                                    </svg>
                                    <h2 class="ms-3 lg:text-lg font-bold text-gray-900">
                                        <div  class="tracking-wide flex flex-col border-blue-300
                                        hover:text-sky-700 group transition-all duration-300 truncate text-sky-950">
                                                <div class="flex flex-row place-content-center justify-center place-items-center text-wrap">
                                                    Zaloguj się i zyskaj nowe funkcje
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-8 text-blue-600">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                                                      </svg>

                                                </div>
                                                <div class=" bg-sky-700 h-[0.10rem] w-0 group-hover:w-full transition-all duration-300 "></div>
                                            </div>
                                    </h2>
                                </div>

                                <p class="mt-2  ps-6 text-gray-500 text-xs sm:leading-relaxed">
                                    Założenie konta gwarantuje autoryzowany dostęp do API  Systemu oraz możliwość dodawania prywatnych stacji pogodowych.
                                </p>

                            </a>
                            @endauth
                            @endif

                    <div class="flex flex-col justify-center text-center text-sm text-gray-500 font-semibold pt-6 sm:pt-0">
                        &copy; 2025-2026 | Dominik But
                        <p class="text-xs text-gray-500 font-normal pt-2 text-balance">Źródłem pochodzenia danych jest Instytut Meteorologii i Gospodarki Wodnej – Państwowy Instytut Badawczy. <br>Dane IMGW-PIB zostały przetworzone na potrzeby niektórych funkcjonalności systemu.</p>
                    </div>
                </div>

            </div>

    </div>

</x-guest-layout>
