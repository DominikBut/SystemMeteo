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

            if ($numericRecords->isEmpty()) {
                $this->minMaxStats[$field] = [
                    'min'            => null,
                    'min_station'    => null,
                    'min_station_id' => null,
                    'max'            => null,
                    'max_station'    => null,
                    'max_station_id' => null,
                    'avg'            => null,
                    'median'         => null,
                    'std'            => null,
                    'variance'       => null,
                    'sum'            => null,
                    'count'          => 0,
                ];
                continue;
            }

            $values = $numericRecords->pluck($field)->map(fn($v) => (float) $v);

            $minValue = $values->min();
            $maxValue = $values->max();
            $avgValue = round($values->avg(), 1);
            $sumValue = round($values->sum(), 1);
            $countValue = $values->count();

            // Median
            $sorted = $values->sort()->values();
            $mid = (int) floor(($countValue - 1) / 2);
            $median = $countValue % 2
                ? $sorted[$mid]
                : round(($sorted[$mid] + $sorted[$mid + 1]) / 2, 1);

            // Variance & Std
            $variance = $values->map(fn($x) => pow($x - $avgValue, 2))->avg();
            $std = round(sqrt($variance), 2);

            // Find stations for min/max
            $minEntry = $numericRecords->firstWhere($field, $minValue);
            $maxEntry = $numericRecords->firstWhere($field, $maxValue);

            $this->minMaxStats[$field] = [
                'min'            => $minValue,
                'min_station'    => $minEntry['nazwa_stacji'] ?? null,
                'min_station_id' => $minEntry['kod_stacji'] ?? null,
                'max'            => $maxValue,
                'max_station'    => $maxEntry['nazwa_stacji'] ?? null,
                'max_station_id' => $maxEntry['kod_stacji'] ?? null,
                'avg'            => $avgValue,
                'median'         => $median,
                'std'            => $std,
                'variance'       => round($variance, 2),
                'sum'            => $sumValue,
                'count'          => $countValue,
            ];
        }
    }


    public function updatedOption()
    {
        $this->getStations();
        $this->error = null;
        $this->stationId = null;
        $this->getStationDataid(null);
        $this->dispatch('clear-url-id');
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

        $sortBy = $this->sortBy;
        $direction = $this->sortDirection;

        // Split into nulls and non-nulls
        $nonNulls = [];
        $nulls = [];

        foreach ($this->stationData as $row) {
            if (empty($row[$sortBy]) && $row[$sortBy] !== 0) {
                $nulls[] = $row;
            } else {
                $nonNulls[] = $row;
            }
        }

        // Sort only non-nulls
        usort($nonNulls, function ($a, $b) use ($sortBy, $direction) {
            $valA = $a[$sortBy];
            $valB = $b[$sortBy];

            if ($valA == $valB) return 0;

            if ($direction === 'desc') {
                return ($valA < $valB) ? 1 : -1;
            }
            return ($valA > $valB) ? 1 : -1;
        });

        // Merge: non-nulls first, then nulls
        $this->sortedData = array_merge($nonNulls, $nulls);
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
                    $this->error = 'W oficjalnym API IMGW nie znaleziono danych dla stacji o wybranym ID.';
                }
            }
        } catch (\Throwable $th) {
            $this->stationDataId = [];
            $this->stationId = null;
            if ($kod !== null) {
                $this->error = 'W oficjalnym API IMGW nie znaleziono danych dla stacji o wybranym ID.';
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
