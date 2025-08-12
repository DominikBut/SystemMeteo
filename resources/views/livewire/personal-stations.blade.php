<div>

    <div class="p-4 space-y-4 w-full">
        <div class="flex flex-row align-content-center w-full text-xs sm:text-base">

            <div class="flex flex-col md:grid  md:grid-cols-2 w-full">
                    <h1 class="col-span-2 bg-white rounded-md shadow-sm py-4  px-2 mx-2 text-center  font-bold tracking-wider border"> <span class="text-red-500"> Pamiętaj o utworzeniu klucza API, aby móc przesyłać dane ze stacji na serwer!</span>
                        <br> Możesz używać jednego klucza dla wielu stacji! Zarządzanie tokenami API dostępne jest w zakładce <a class="underline text-blue-500" href="{{ route('api-tokens.index') }}">Moje tokeny API</a> </h1>
            </div>
        </div>

        <div class="m-2  rounded-md shadow-sm border-none">

             {{ $this->table }}

        </div>
    </div>
</div>
