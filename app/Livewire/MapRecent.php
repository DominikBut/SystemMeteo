<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MapRecent extends Component
{
    public $error = null;
    public $info;
    public $stationId;
    public $stationDataId = [];
    public $stationData = [];
    public string $askTime;
    public string $option = 'temp';

    #[Validate('string', message: 'Zły format zmiennej!')]
    public string $sortBy = '';

    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'

    public $sortedData;

    public $minMaxStats = [];


    public function mount()
    {
        $date = Carbon::today();
        $this->getStationData();
        $this->sortedData = $this->stationData;
        $this->calculateMinMaxStats();
    }
    public function getStationData()
    {
        try {
            if (!Cache::has('DaneStacji')) {
                $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/api/data/meteo/");
                if ($response->successful()) {
                    $this->stationData = (array) json_decode($response->body(), true);
                    Cache::put('DaneStacji', $this->stationData, now()->addMinutes(10));
                    $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
                    Cache::put('AskTime', $this->askTime);
                    $this->sortedData = $this->stationData;
                    $this->calculateMinMaxStats();
                } else {
                    $this->stationData = [];
                }
            } else {
                $this->stationData = Cache::get('DaneStacji');
                $this->askTime = Cache::get('AskTime');
            }
            $this->dispatch('station-data-loaded', $this->stationData, $this->askTime);
        } catch (\Throwable $th) {
            $this->stationData = [];
            $this->dispatch('station-data-loaded',  $this->stationData, Carbon::today());
            $this->error = 'Nie udało się pobrać danych z API. ';
            $this->sortedData = $this->stationData;
            $this->calculateMinMaxStats();
        }
    }
    public function setSort(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
        $this->sortWetherData();
    }

    public function calculateMinMaxStats()
    {
        $fields = [
            //terminowe
            'temperatura_gruntu',
            'wiatr_kierunek',
            'wiatr_srednia_predkosc',
            'wiatr_predkosc_maksymalna',
            'wiatr_poryw_10min',
            'wilgotnosc_wzgledna',
            'opad_10min',
            'temperatura_powietrza',

        ];

        foreach ($fields as $field) {
            $values = collect($this->stationData)->pluck($field)->filter(fn($val) => is_numeric($val));
            $this->minMaxStats[$field] = [
                'min' => $values->isEmpty() ? null : $values->min(),
                'max' => $values->isEmpty() ? null : $values->max(),
                'avg' => $values->isEmpty() ? null : round($values->avg(), 1),
            ];
        }
    }
    public function sortWetherData()
    {
        if ($this->sortBy === '') {
            $this->sortedData = $this->stationData;
            return;
        }

        $data = $this->stationData; // work on plain array for speed
        $sortBy = $this->sortBy;
        $direction = $this->sortDirection;

        usort($data, function ($a, $b) use ($sortBy, $direction) {
            $valA = $a[$sortBy] ?? null;
            $valB = $b[$sortBy] ?? null;

            // Always push nulls to the end
            $isNullA = is_null($valA);
            $isNullB = is_null($valB);

            if ($isNullA && !$isNullB) return 1;
            if (!$isNullA && $isNullB) return -1;
            if ($isNullA && $isNullB) return 0;

            // Both non-null: compare
            if ($valA == $valB) return 0;

            if ($direction === 'desc') {
                return ($valA < $valB) ? 1 : -1;
            }
            return ($valA > $valB) ? 1 : -1;
        });

        $this->sortedData = $data;
    }
    public function getStationDataid($kod)
    {
        $this->stationId = $kod;

        // // First, try to get from cache
        // if (Cache::has('DaneStacjiL' . $kod)) {
        //     $this->stationDataId = Cache::get('DaneStacjiL' . $kod);
        //     return;
        // }

        // Not in cache, search locally
        $record = collect($this->stationData)->firstWhere('kod_stacji', $kod);

        if ($record) {
            $this->stationDataId = (array) $record;
            // Cache the result for 5 minutes
            // Cache::put('DaneStacjiL' . $kod, $this->stationDataId, now()->addMinutes(5));
        } else {
            $this->stationDataId = [];
            $this->error = 'Nie znaleziono danych dla podanej stacji.';
        }
    }


    // public function getStationDataid($kod)
    // {
    //     $this->stationId = $kod;
    //     $this->stationData;
    //     try {
    //         if (!Cache::has('DaneStacji' . $this->stationId)) {
    //             $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/api/data/meteo/id/{$this->stationId}");
    //             if ($response->successful()) {
    //                 $this->stationDataId =  (array) json_decode($response->body(), true)[0];
    //                 Cache::put('DaneStacji' . $this->stationId, $this->stationDataId, now()->addMinutes(5));
    //                 $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
    //                 Cache::put('AskTime' . $this->stationId, $this->askTime);
    //             } else {
    //                 $this->stationDataId = [];
    //             }
    //         } else {
    //             $this->stationDataId = Cache::get('DaneStacji' . $this->stationId);
    //             $this->askTime = Cache::get('AskTime' . $this->stationId);
    //         }
    //     } catch (\Throwable $th) {
    //         $this->stationDataId = [];
    //         $this->error = 'Nie udało się pobrać danych z API. ';
    //     }
    // }


    public function render()
    {
        return view('livewire.map-recent');
    }
}
