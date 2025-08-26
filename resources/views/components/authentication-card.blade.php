<div class=" bg-white rounded-md shadow-sm min-h-80 sm:max-w-md w-full h-auto overflow-hidden sm:rounded-lg flex flex-col sm:justify-center items-center">

    <div class="flex flex-col items-start min-w-full   p-6 justify-center w-full text-start">
         <div class="w-full text-start mb-4 flex flex-row items-center gap-4">
        {{ $logo }} <a href="{{ route('welcome') }}" class="ml-2 mb-2 w-fit text-2xl sm:text-3xl font-bold text-black ">
                                <span class="sr-only">homepage</span>
                                System Meteo
                            </a>
        </div>
        <div class="w-full">
        {{ $slot }}
        </div>
    </div>
</div>
