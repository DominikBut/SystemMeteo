<?php

namespace App\Livewire;

use DateTime;
use DateTimeZone;
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
    public $stations = [];

    public function mount()
    {
        $date = Carbon::today();
        $this->getStationData();
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
                } else {
                    $this->stationData = [];
                }
            } else {
                $this->stationData = Cache::get('DaneStacji');
                $this->askTime = Cache::get('AskTime');
            }
            $this->dispatch('station-data-loaded', $this->stationData, $this->askTime);
            $this->sortedData = $this->stationData;
            $this->calculateMinMaxStats();
            $this->getStations();
        } catch (\Throwable $th) {
            $this->stationData = [];
            $this->dispatch('station-data-loaded',  $this->stationData, Carbon::today());
            $this->error = 'Nie udało się pobrać danych z API. ';
            $this->sortedData = $this->stationData;
            $this->calculateMinMaxStats();
            $this->getStations();
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

    protected function calculateMinMaxStats()
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
    public function updatedOption()
    {
        $this->getStations();
    }
    protected function getStations()
    {
        // Unique cache key for this option
        $cacheKey = "stations_list_{$this->option}";

        $this->stations = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $tmpStations = [];

            foreach ($this->stationData as $entry) {
                $id = trim($entry['kod_stacji'] ?? '');
                $name = trim($entry['nazwa_stacji'] ?? '');

                if (!$id || !$name) {
                    continue;
                }

                if ($this->option === 'hum') {
                    if (
                        $entry['wilgotnosc_wzgledna'] === null ||
                        !$this->isRecentEnough($entry['wilgotnosc_wzgledna_data'])
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'temp') {
                    if (
                        $entry['temperatura_powietrza_data'] === null ||
                        !$this->isRecentEnough($entry['temperatura_powietrza_data'])
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'rain') {
                    if (
                        $entry['opad_10min_data'] === null ||
                        !$this->isRecentEnough($entry['opad_10min_data'])
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'tempg') {
                    if (
                        $entry['temperatura_gruntu_data'] === null ||
                        !$this->isRecentEnough($entry['temperatura_gruntu_data'])
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'wind') {
                    if (
                        $entry['wiatr_srednia_predkosc_data'] === null ||
                        !$this->isRecentEnough($entry['wiatr_srednia_predkosc_data'])
                    ) {
                        continue;
                    }
                }

                // 'all' means no filtering

                $tmpStations[] = [
                    'kod_stacji' => $id,
                    'nazwa_stacji' => $name
                ];
            }

            return collect($tmpStations)->sortBy('nazwa_stacji')->values()->all();
        });
    }
    protected function sortWetherData()
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
    protected function isRecentEnough(?string $utcString, int $maxAgeMinutes = 120): bool
    {
        if (!$utcString) {
            return false;
        }
        try {
            $utcDate = new DateTime($utcString, new DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return false;
        }

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $diffMinutes = ($now->getTimestamp() - $utcDate->getTimestamp()) / 60;

        return $diffMinutes >= 0 && $diffMinutes <= $maxAgeMinutes;
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
