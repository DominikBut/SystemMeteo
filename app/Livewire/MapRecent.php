<?php

namespace App\Livewire;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MapRecent extends Component
{
    public $error = null;
    public $info;
    #[Url(except: null, as: 'id', history: true)]
    #[Validate('numeric', message: 'Zły format id!')]
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
    // public $matchingRecords = [];
    public function mount()
    {
        $date = Carbon::today();
        $this->getStationData();
        if ($this->stationId != null) {
            $this->getStationDataid($this->stationId);
            $this->dispatch('open', id: $this->stationId);
        }
    }
    public function getStationData()
    {
        try {
            if (!Cache::has('DaneStacji')) {
                $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/api/data/meteo/");
                if ($response->successful()) {
                    $this->stationData = (array) json_decode($response->body(), true);
                    // Example: adjust all *_data fields to current timezone
                    $this->stationData = collect($this->stationData)->map(function ($record) {
                        foreach ($record as $key => $value) {
                            if (str_ends_with($key, '_data') && !empty($value)) {
                                // Convert from UTC to your app's timezone
                                $record[$key] = Carbon::parse($value, 'UTC')
                                    ->setTimezone(config('app.timezone'))
                                    ->toDateTimeString();
                            }
                        }
                        return $record;
                    })->toArray();
                    Cache::put('DaneStacji', $this->stationData, now()->addMinutes(5));
                    $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
                    Cache::put('AskTime', $this->askTime);
                } else {
                    $this->stationData = [];
                }
            } else {
                $this->stationData = Cache::get('DaneStacji');
                $this->askTime = Cache::get('AskTime');
            }

            $this->sortedData = $this->stationData;
            $this->calculateMinMaxStats();
            $this->getStations();
        } catch (\Throwable $th) {
            $this->stationData = [];
            $this->askTime = Carbon::today();
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
            $numericRecords = collect($this->stationData)
                ->filter(function ($entry) use ($field) {
                    return is_numeric($entry[$field] ?? null)
                        && $this->isRecentEnough($entry[$field . '_data'] ?? null);
                });

            $minValue = $numericRecords->min($field);
            $maxValue = $numericRecords->max($field);

            $minStation    = $numericRecords->firstWhere($field, $minValue)['nazwa_stacji'] ?? null;
            $minidStation  = $numericRecords->firstWhere($field, $minValue)['kod_stacji'] ?? null;
            $maxStation    = $numericRecords->firstWhere($field, $maxValue)['nazwa_stacji'] ?? null;
            $maxidStation  = $numericRecords->firstWhere($field, $maxValue)['kod_stacji'] ?? null;

            $this->minMaxStats[$field] = [
                'min'             => $numericRecords->isEmpty() ? null : $minValue,
                'min_station'     => $numericRecords->isEmpty() ? null : $minStation,
                'min_station_id'  => $numericRecords->isEmpty() ? null : $minidStation,
                'max'             => $numericRecords->isEmpty() ? null : $maxValue,
                'max_station'     => $numericRecords->isEmpty() ? null : $maxStation,
                'max_station_id'  => $numericRecords->isEmpty() ? null : $maxidStation,
                'avg'             => $numericRecords->isEmpty() ? null : round($numericRecords->avg($field), 1),
            ];
        }
    }


    public function updatedOption()
    {
        $this->getStations();
        $this->getStationDataid(null); /// hm?
    }
    protected function getStations()
    {
        // Unique cache key for this option
        $cacheKey = "stations_list_{$this->option}";
        $tmpmatchingRecords = [];

        [$this->stations, $tmpmatchingRecords] = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $tmpStations = [];
            $matchingRecords = [];

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

                // Passed filter → add to both arrays
                $tmpStations[] = [
                    'kod_stacji' => $id,
                    'nazwa_stacji' => $name
                ];
                $matchingRecords[] = $entry;
            }

            return [
                collect($tmpStations)->sortBy('nazwa_stacji')->values()->all(),
                $matchingRecords
            ];
        });
        $this->dispatch('layer-updated', $tmpmatchingRecords, $this->askTime, $this->option);
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
        try {
            $record = collect($this->stationData)->firstWhere('kod_stacji', $kod);

            if ($record) {
                $this->stationDataId = (array) $record;
                // Cache the result for 5 minutes
                // Cache::put('DaneStacjiL' . $kod, $this->stationDataId, now()->addMinutes(5));
            } else {
                $this->stationDataId = [];
                $this->stationId = null;
                if ($kod !== null) {
                    $this->error = 'Nie znaleziono danych dla podanej stacji.';
                }
            }
        } catch (\Throwable $th) {
            $this->stationDataId = [];
            $this->stationId = null;
            if ($kod !== null) {
                $this->error = 'Nie znaleziono danych dla podanej stacji.';
            }
        }
    }
    protected function isRecentEnough(?string $utcString, int $maxAgeMinutes = 120): bool
    {
        if (!$utcString) {
            return false;
        }
        try {
            $utcDate = new DateTime($utcString);
        } catch (\Exception $e) {
            return false;
        }

        $now = new DateTime('now');
        $diffMinutes = ($now->getTimestamp() - $utcDate->getTimestamp()) / 60;

        return $diffMinutes >= 0 && $diffMinutes <= $maxAgeMinutes;
    }

    public function render()
    {
        return view('livewire.map-recent');
    }
}
