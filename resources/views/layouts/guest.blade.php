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
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Bieżąca mapa meteo</span>
                                            </a>
                                            <a href="{{ route('stacja_recent') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_recent') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Bieżące dane stacji</span>
                                            </a>

                                            <a href="{{ route('stacja_archive') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_archive') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Oficjalne archiwum danych IMGW stacji</span>
                                            </a>
                                            <a href="{{ route('stacja_community') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2 font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('stacja_community') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Bieżące dane stacji społeczności</span>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Moje konto</span>
                                            </a>
                                            <a href="{{ url('/dashboard') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('dashboard') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Moje stacje pogodowe</span>
                                            </a>
                                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                            <a href="{{ route('api-tokens.index') }}" class="shadow-sm flex items-center rounded-md gap-2 px-2 py-2  font-medium underline-offset-2  focus-visible:underline focus:outline-hidden
                                            {{ request()->routeIs('api-tokens.index') ? 'bg-blue-200 text-sky-950' : 'hover:bg-blue-100 hover:text-black text-gray-600' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                                    </svg>
                                                <span>Zaloguj się</span>
                                            </a>

                                                @if (Route::has('register'))

                                                    <a  href="{{ route('register') }}" class=" uppercase font-bold flex w-full items-center rounded-xl gap-2 p-2 text-left border-2 border-gray-300 border-md text-gray-600 hover:bg-blue-100 hover:text-black transition duration-100
                                                    {{ request()->routeIs('register') ? 'bg-blue-500 text-white' : 'bg-blue-200 hover:bg-blue-100 hover:text-black text-sky-950'}}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M7.84 1.804A1 1 0 0 1 8.82 1h2.36a1 1 0 0 1 .98.804l.331 1.652a6.993 6.993 0 0 1 1.929 1.115l1.598-.54a1 1 0 0 1 1.186.447l1.18 2.044a1 1 0 0 1-.205 1.251l-1.267 1.113a7.047 7.047 0 0 1 0 2.228l1.267 1.113a1 1 0 0 1 .206 1.25l-1.18 2.045a1 1 0 0 1-1.187.447l-1.598-.54a6.993 6.993 0 0 1-1.929 1.115l-.33 1.652a1 1 0 0 1-.98.804H8.82a1 1 0 0 1-.98-.804l-.331-1.652a6.993 6.993 0 0 1-1.929-1.115l-1.598.54a1 1 0 0 1-1.186-.447l-1.18-2.044a1 1 0 0 1 .205-1.251l1.267-1.114a7.05 7.05 0 0 1 0-2.227L1.821 7.773a1 1 0 0 1-.206-1.25l1.18-2.045a1 1 0 0 1 1.187-.447l1.598.54A6.992 6.992 0 0 1 7.51 3.456l.33-1.652ZM10 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
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
