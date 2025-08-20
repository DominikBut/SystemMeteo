<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @stack('css')
         @filamentStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <!-- Styles -->

        @stack('scripts')
        @livewireStyles
    </head>
    <body class=" font-monospace antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100">

            <!-- Page Content -->
            <main>
                <div x-data="{ showSidebar: false }" class="relative flex w-full flex-col md:flex-row">

                    <!-- dark overlay for when the sidebar is open on smaller screens  -->
                    <div x-show="showSidebar" class="fixed inset-0 z-10 bg-slate-900/10 backdrop-blur-xs md:hidden" aria-hidden="true" x-on:click="showSidebar = false" x-transition.opacity></div>

                   <nav
                    class="fixed left-0 z-50 flex justify-between h-svh w-60 shrink-0 flex-col border-r-2 border-gray-200 bg-white p-3 transition-transform duration-300
                        -translate-x-60 md:translate-x-0 md:relative md:w-64"
                    x-bind:class="showSidebar ? 'translate-x-0' : ''"
                    aria-label="sidebar navigation">
                        <div>
                        <!-- logo  -->
                            <div class="w-full text-start px-2 pt-1 pb-4 flex flex-row items-center gap-2">
                                <a href="{{ route('welcome') }}">
                                    <x-application-mark class="size-10"/>
                                </a>
                                <a href="http://127.0.0.1:8000" class="w-fit text-xl sm:text-2xl font-bold text-black ">
                                    <span class="sr-only">homepage</span>
                                    System Meteo
                                </a>
                            </div>
                            <!-- sidebar links  -->
                            <div class="flex flex-col gap-2 overflow-y-auto py-4 border-t border-gray-200 ">
                                <div class=" gap-2 flex flex-col  uppercase font-semibold text-xs ">
                                        <a href="{{ route('map')}}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2 font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('map') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                                                </svg>
                                                <span>[Mapa meteo] <br>IMGW</span>
                                            </a>
                                            <a href="{{ route('stacja_recent') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_recent') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>
                                                <span>[Archiwum Lokalne]<br> Dane stacji</span>
                                            </a>

                                            <a href="{{ route('stacja_archive') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_archive') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>
                                                <span>[Archiwum IMGW]<br> Dane stacji</span>
                                            </a>
                                            <a href="{{ route('map_community')}}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2 font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('map_community') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                                                </svg>
                                                <span>[Mapa meteo] <br>  społeczności</span>
                                            </a>
                                            <a href="{{ route('stacja_community') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2 font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_community') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>
                                                <span>[Archiwum społeczności]<br> Dane stacji </span>
                                            </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 overflow-y-auto  text-xs">
                            @if (Route::has('login'))
                                @auth
                                 <div>
                                <div  x-data="{ menuIsOpen: false }" class="mt-auto " x-on:keydown.esc.window="menuIsOpen = false">
                                    <div class=" gap-2 flex flex-col py-4  uppercase font-bold ">
                                        <a href="{{ route('profile.show')}}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('profile.show') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>

                                                <span>Moje konto</span>
                                            </a>
                                            <a href="{{ url('/dashboard') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('dashboard') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">

                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>

                                                <span>Moje stacje pogodowe</span>
                                            </a>
                                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                            <a href="{{ route('api-tokens.index') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('api-tokens.index') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                                </svg>
                                                <span>Moje tokeny API</span>
                                            </a>
                                            @endif

                                        </div>
                                        <button type="button" class="flex w-full items-center rounded-xl gap-2 p-2 text-left border-2 border-blue-300 border-md text-slate-700 hover:bg-blue-700/5 hover:text-black   " x-bind:class="menuIsOpen ? 'bg-blue-700/10 ' : ''" aria-haspopup="true" x-on:click="menuIsOpen = ! menuIsOpen" x-bind:aria-expanded="menuIsOpen">
                                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                                <div class="flex text-sm border-2 border-blue-300 rounded-full focus:outline-none transition">
                                                    <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                                </div>
                                            @else
                                                <span class="inline-flex rounded-md">
                                                    <button type="button" class="truncate inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                        {{ Auth::user()->name }}

                                                        <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            @endif
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-black "> {{ Auth::user()->name }}</span>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2" class="ml-auto size-4 shrink-0 -rotate-90 md:rotate-0" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                            </svg>
                                        </button>

                                        <!-- menu -->
                                        <div x-cloak x-show="menuIsOpen" class=" absolute bottom-50 right-6 md:-right-[11.5rem] md:bottom-[4.5rem] z-20 -mr-1 w-48 border divide-y divide-slate-300 border-gray-200 bg-white   rounded-xl " role="menu" x-on:click.outside="menuIsOpen = false" x-on:keydown.down.prevent="$focus.wrap().next()" x-on:keydown.up.prevent="$focus.wrap().previous()" x-transition="" x-trap="menuIsOpen">

                                                <form method="POST" action="{{ route('logout') }}" x-data>
                                                    @csrf
                                                <!-- Authentication -->
                                                <div class="flex flex-col py-1.5">
                                                    <a href="{{ route('logout') }}" @click.prevent="$root.submit();" class="flex items-center gap-2 px-2 py-1.5 text-sm font-medium text-gray-600 underline-offset-2 hover:bg-blue-200 hover:text-black focus-visible:underline focus:outline-hidden " role="menuitem">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/>
                                                            <path fill-rule="evenodd" d="M6 10a.75.75 0 0 1 .75-.75h9.546l-1.048-.943a.75.75 0 1 1 1.004-1.114l2.5 2.25a.75.75 0 0 1 0 1.114l-2.5 2.25a.75.75 0 1 1-1.004-1.114l1.048-.943H6.75A.75.75 0 0 1 6 10Z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span> {{ __('Wyloguj') }}</span>
                                                    </a>
                                                </div>
                                            </form>

                                        </div>
                                    </div>


                                </div>

                                    @else

                                            <a  href="{{ route('login') }}" class=" uppercase font-bold flex w-full items-center rounded-xl gap-2 p-2 text-left border-2 border-gray-300 border-md text-gray-600 hover:bg-blue-100 hover:text-black transition duration-100
                                            {{ request()->routeIs('login') ? 'bg-blue-500 text-white' : 'bg-blue-200 hover:bg-blue-100 hover:text-black text-sky-950'}}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                                    </svg>

                                                <span>Zaloguj się</span>
                                            </a>

                                                @if (Route::has('register'))

                                                    <a  href="{{ route('register') }}" class=" uppercase font-bold flex w-full items-center rounded-xl gap-2 p-2 text-left border-2 border-gray-300 border-md text-gray-600 hover:bg-blue-100 hover:text-black transition duration-100
                                                    {{ request()->routeIs('register') ? 'bg-blue-500 text-white' : 'bg-blue-200 hover:bg-blue-100 hover:text-black text-sky-950'}}">

                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                                                    </svg>

                                                <span>Zarejestruj się</span>
                                            </a>

                                         @endif
                                    @endauth
                                @endif
                            <div>
                                <a href="{{ route('about') }}" class="text-xs underline uppercase font-semibold flex flex-row justify-center text-center  text-blue-400 border-t-2 border-gray-200 rounded-md py-1 sm:py-2">
                                Informacje o systemie
                                </a>
                                <div class="text-xs flex flex-row justify-center text-center  text-gray-500 ">
                                &copy; 2025-2026 | Dominik But
                                </div>
                            </div>
                        </div>

                    </nav>

                    <!-- main content  -->
                    <div  class="h-svh w-full overflow-y-auto ">
                                <!-- Page Heading -->
                        @if (isset($header))
                            <header class="bg-white shadow-sm">
                                <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                                    <h1 class=" px-2 mx-2 sm:text-start text-xl sm:text-2xl font-bold tracking-wider">
                                    {{ $header }}
                                    </h1>
                                </div>
                            </header>
                        @endif

                        <!-- Add main content here  -->
                        {{ $slot }}
                    </div>

                <!-- toggle button for sidebar on mobile  -->
                    <button x-cloak class="fixed right-4 top-4 z-20 rounded-full bg-blue-500 p-4 md:hidden text-slate-100 " x-on:click="showSidebar = ! showSidebar">
                        <svg x-show="showSidebar" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5" aria-hidden="true">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                        </svg>
                        <svg x-show="! showSidebar" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5" aria-hidden="true">
                            <path d="M0 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5-1v12h9a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zM4 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2z"/>
                        </svg>
                        <span class="sr-only">Sidebar toggle</span>
                    </button>
                </div>

            </main>
        </div>

        @stack('modals')
        @stack('scripts2')

        @livewireScripts

    </body>
</html>
