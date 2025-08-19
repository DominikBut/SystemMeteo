<?php

namespace App\Livewire;

use DateTime;
use Carbon\Carbon;
use App\Models\Data;
use Livewire\Component;
use App\Models\Stations;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MapCommunity extends Component
{
    public $error = null;
    public $info;
    #[Url(except: null, as: 'id', history: true)]
    #[Validate('numeric', message: 'Zły format id!')]
    public $stationId;
    public $stationDataId = [];
    public $stationData = [];
    public  $askTime = null;
    public string $option = 'temp';

    #[Validate('string', message: 'Zły format zmiennej!')]
    public string $sortBy = '';

    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'

    public $sortedData;
    public $sortedDataAll = [];
    public $stationInfo;
    public $station_name;
    public $minMaxStats = [];
    public $stations = [];
    public $allstations = [];
    // public $matchingRecords = [];
    public function mount()
    {
        $date = Carbon::today();
        $this->getStations();
        $this->getStationData();

        if ($this->stationId != null) {
            $this->getStationDataid($this->stationId);
            $this->dispatch('open', id: $this->stationId);
        }
    }
    public function getStationData()
    {
        $this->error = null;
        try {
            $userId = Auth::id();
            $results = [];
            $hours = 23; // or make it a property/parameter
            foreach ($this->allstations as $station) {

                $latestDataall = Data::where('station_id', $station['id'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                $resultsall[] = [
                    'station_id'   => $station['id'],
                    'name'         => $station['name'],
                    'lat'          => $station['lat'],
                    'lon'          => $station['lon'],
                    'voivodeship'  => $station['voivodeship'],
                    'district'     => $station['district'],
                    'active'     => $station['active'],
                    'latest'       => $latestDataall,
                    'owned'        => $station['user_id'] === $userId,
                ];

                if ($this->option != 'all') {
                    $latestData = Data::where('station_id', $station['id'])
                        ->where('created_at', '>=', Carbon::now()->subHours($hours))
                        ->orderBy('created_at', 'desc')
                        ->first();
                } else {
                    $latestData = Data::where('station_id', $station['id'])
                        ->orderBy('created_at', 'desc')
                        ->first();
                }

                if (!$latestData) {
                    continue; // skip station with no data today
                }

                // Apply option-specific filters
                if ($this->option === 'hum') {
                    if (
                        $latestData->humidity === null ||
                        !$this->isRecentEnough($latestData->created_at)
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'temp') {
                    if (
                        $latestData->temp_air === null ||
                        !$this->isRecentEnough($latestData->created_at)
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'rain') {
                    if (
                        $latestData->rain_10min === null ||
                        !$this->isRecentEnough($latestData->created_at)
                    ) {
                        continue;
                    }
                }

                if ($this->option === 'wind') {
                    if (
                        $latestData->wind_speed === null ||
                        !$this->isRecentEnough($latestData->created_at)
                    ) {
                        continue;
                    }
                }

                // Passed all checks → keep station
                $results[] = [
                    'station_id'   => $station['id'],
                    'name'         => $station['name'],
                    'lat'          => $station['lat'],
                    'lon'          => $station['lon'],
                    'voivodeship'  => $station['voivodeship'],
                    'district'     => $station['district'],
                    'active'     => $station['active'],
                    'latest'       => $latestData,
                    'owned'        => $station['user_id'] === $userId,
                ];
            }

            $this->stationData = $results;
            $this->sortedDataAll = $resultsall;
            $this->sortedData = $this->sortedDataAll;
            $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
            $this->calculateMinMaxStats();
            // // build stations list like old getStations
            // $this->stations = collect($this->stationData)
            //     ->map(fn($s) => [
            //         'id'   => $s['station_id'],
            //         'name' => $s['name'],
            //         'lat'  => $s['lat'],
            //         'lon' =>   $s['lon'],
            //         'district' =>   $s['district'],
            //         'voivodeship' => $s['voivodeship'],
            //         'user_id' => $s['user_id'],
            //     ])
            //     ->sortBy('name')
            //     ->values()
            //     ->all();
            // dd($this->stationData);
            // Dispatch layer update
            $this->dispatch('layer-updated', $this->stationData, $this->askTime, $this->option);
        } catch (\Throwable $th) {
            $this->stationData = [];
            $this->stations = [];
            $this->sortedDataAll = [];
            $this->sortedData = $this->sortedDataAll;
            $this->askTime = Carbon::today();
            $this->calculateMinMaxStats();
            $this->error = 'Nie udało się pobrać danych dla stacji.';
            $this->dispatch('layer-updated', $this->stationData, $this->askTime, $this->option);
        }
        // try {
        //     $userId = Auth::id();
        //     $results = [];

        //     foreach ($this->allstations as $station) {

        //         $latestData = Data::where('station_id', $station['id'])
        //             ->whereDate('created_at', Carbon::today())
        //             ->orderBy('created_at', 'desc')
        //             ->first();

        //         $results[] = [
        //             'station_id'   => $station['id'],
        //             'name' => $station['name'],
        //             'lat' => $station['lat'],
        //             'lon' => $station['lon'],
        //             'voivodeship' => $station['voivodeship'],
        //             'district' => $station['district'],
        //             'latest'       => $latestData,
        //             'owned'        => $station['user_id'] === $userId, // like before
        //         ];
        //     }

        //     $this->stationData = $results;
        //     $this->askTime     = Carbon::now()->format('Y-m-d H:i:s');
        //     // Dispatch the event like in old code
        //     $this->dispatch('layer-updated', $this->stationData, $this->askTime, $this->option);
        // } catch (\Throwable $th) {
        //     $this->stationData = [];
        //     $this->askTime     = Carbon::today();
        //     $this->error       = 'Nie udało się pobrać danych dla stacji.';
        //     // Dispatch the event like in old code
        //     $this->dispatch('layer-updated', $this->stationData, $this->askTime, $this->option);
        // }
    }


    // public function getStationData()
    // {
    //     try {
    //         if (!Cache::has('DaneStacji')) {
    //             $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/api/data/meteo/");
    //             if ($response->successful()) {
    //                 $this->stationData = (array) json_decode($response->body(), true);
    //                 // Example: adjust all *_data fields to current timezone
    //                 $this->stationData = collect($this->stationData)->map(function ($record) {
    //                     foreach ($record as $key => $value) {
    //                         if (str_ends_with($key, '_data') && !empty($value)) {
    //                             // Convert from UTC to your app's timezone
    //                             $record[$key] = Carbon::parse($value, 'UTC')
    //                                 ->setTimezone(config('app.timezone'))
    //                                 ->toDateTimeString();
    //                         }
    //                     }
    //                     return $record;
    //                 })->toArray();
    //                 Cache::put('DaneStacji', $this->stationData, now()->addMinutes(5));
    //                 $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
    //                 Cache::put('AskTime', $this->askTime);
    //             } else {
    //                 $this->stationData = [];
    //             }
    //         } else {
    //             $this->stationData = Cache::get('DaneStacji');
    //             $this->askTime = Cache::get('AskTime');
    //         }

    //         $this->sortedData = $this->stationData;
    //         $this->calculateMinMaxStats();
    //         $this->getStations();
    //     } catch (\Throwable $th) {
    //         $this->stationData = [];
    //         $this->askTime = Carbon::today();
    //         $this->error = 'Nie udało się pobrać danych z API. ';
    //         $this->sortedData = $this->stationData;
    //         $this->calculateMinMaxStats();
    //         $this->getStations();
    //     }
    // }
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
            'wind_direction',
            'wind_speed',
            'humidity',
            'rain_10min',
            'temp_air',
        ];

        foreach ($fields as $field) {
            // Filter only numeric and recent values
            $numericRecords = collect($this->stationData)
                ->filter(function ($entry) use ($field) {
                    return isset($entry['latest'][$field])
                        && is_numeric($entry['latest'][$field])
                        && $this->isRecentEnough($entry['latest']['created_at'] ?? null);
                });

            if ($numericRecords->isEmpty()) {
                $this->minMaxStats[$field] = [
                    'min'             => null,
                    'min_station'     => null,
                    'min_station_id'  => null,
                    'max'             => null,
                    'max_station'     => null,
                    'max_station_id'  => null,
                    'avg'             => null,
                ];
                continue;
            }

            $minValue = $numericRecords->min(fn($e) => $e['latest'][$field]);
            $maxValue = $numericRecords->max(fn($e) => $e['latest'][$field]);

            $minEntry = $numericRecords->first(fn($e) => $e['latest'][$field] == $minValue);
            $maxEntry = $numericRecords->first(fn($e) => $e['latest'][$field] == $maxValue);

            $this->minMaxStats[$field] = [
                'min'             => $minValue,
                'min_station'     => $minEntry['name'] ?? null,
                'min_station_id'  => $minEntry['station_id'] ?? null,
                'max'             => $maxValue,
                'max_station'     => $maxEntry['name'] ?? null,
                'max_station_id'  => $maxEntry['station_id'] ?? null,
                'avg'             => round($numericRecords->avg(fn($e) => $e['latest'][$field]), 1),
            ];
        }
    }


    public function updatedOption()
    {
        $this->error = null;
        $this->getStations();
        $this->getStationData();
        $this->stationId = null;
        $this->getStationDataid(null);
        $this->dispatch('clear-url-id');
        // if ($this->stationId != null) {
        //     $this->getStationDataid($this->stationId);
        //     $this->dispatch('open', id: $this->stationId);
        // }
    }
    protected function getStations()
    {
        $this->error = null;
        $user = Auth::id();

        // Query DB depending on auth
        if ($user) {
            $stations = Stations::where(function ($query) use ($user) {
                $query->where('public', true)
                    ->orWhere('user_id', $user);
            })
                ->orderBy('name', 'asc')
                ->select('id', 'name', 'user_id', 'lat', 'lon', 'voivodeship', 'district', 'active')
                ->get();
        } else {
            $stations = Stations::where('public', true)
                ->orderBy('name', 'asc')
                ->select('id', 'name', 'user_id', 'lat', 'lon', 'voivodeship', 'district', 'active')
                ->get();
        }

        $tmpStations = [];
        $matchingRecords = [];

        foreach ($stations as $entry) {
            $id = trim($entry->id ?? '');
            $name = trim($entry->name ?? '');

            if (!$id || !$name) {
                continue;
            }

            $tmpStations[] = [
                'id'          => $id,
                'name'        => $name,
                'lat'         => $entry->lat,
                'lon'         => $entry->lon,
                'district'    => $entry->district,
                'voivodeship' => $entry->voivodeship,
                'active'      => $entry->active,
                'user_id'     => $entry->user_id,
            ];

            $matchingRecords[] = $entry;
        }

        // Assign stations list to your component property
        $this->allstations = collect($tmpStations)->sortBy('name')->values()->all();
    }

    // protected function getStations()
    // {

    //     ///here old code
    //     // Unique cache key for this option
    //     $cacheKey = "stations_list_base_{$this->option}";
    //     $tmpmatchingRecords = [];

    //     [$this->stations, $tmpmatchingRecords] = Cache::remember($cacheKey, now()->addMinutes(5), function () {
    //         $tmpStations = [];
    //         $matchingRecords = [];

    //         foreach ($this->stationData as $entry) {
    //             $id = trim($entry['id'] ?? '');
    //             $name = trim($entry['name'] ?? '');

    //             if (!$id || !$name) {
    //                 continue;
    //             }
    //             if (
    //                 $this->isRecentEnough($entry['created_at'])
    //             ) {
    //                 continue;
    //             }


    //             // Passed filter → add to both arrays
    //             $tmpStations[] = [
    //                 'id' => $id,
    //                 'name' => $name
    //             ];
    //             $matchingRecords[] = $entry;
    //         }

    //         return [
    //             collect($tmpStations)->sortBy('name')->values()->all(),
    //             $matchingRecords
    //         ];
    //     });
    //     $this->dispatch('layer-updated', $tmpmatchingRecords, $this->askTime, $this->option);
    // }

    protected function sortWetherData()
    {
        if ($this->sortBy === '') {
            $this->sortedData = $this->sortedDataAll;
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
        $this->error = null;
        $this->stationId = $kod;

        // // First, try to get from cache
        // if (Cache::has('DaneStacjiL' . $kod)) {
        //     $this->stationDataId = Cache::get('DaneStacjiL' . $kod);
        //     return;
        // }

        // Not in cache, search locally
        try {
            $record = collect($this->stationData)->firstWhere('station_id', $kod);

            if ($record) {
                $this->stationDataId = (array) $record;
                // Cache the result for 5 minutes
                // Cache::put('DaneStacjiL' . $kod, $this->stationDataId, now()->addMinutes(5));
            } else {
                $this->stationDataId = [];
                $this->stationId = null;
                if ($kod !== null) {
                    $this->error = 'Nie znaleziono danych dla podanej stacji w bazie lub w tej warstwie.';
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

    // public function getStationDataid($kod)
    // {
    //     $this->stationId = $kod;

    //     // // First, try to get from cache
    //     // if (Cache::has('DaneStacjiL' . $kod)) {
    //     //     $this->stationDataId = Cache::get('DaneStacjiL' . $kod);
    //     //     return;
    //     // }
    //     $station_tmp = Stations::where('id', $this->stationId)->first();

    //     if ($station_tmp) {
    //         if ($station_tmp->public == true || $station_tmp->user_id === Auth::id()) {
    //             $this->station_name = $station_tmp->name;
    //             $response = Data::where('station_id', $this->stationId)
    //                 ->whereDate('created_at', Carbon::today())
    //                 ->orderBy('created_at', 'desc')
    //                 ->first();
    //             $this->stationInfo = $station_tmp;
    //             $this->stationData = $response;
    //             $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
    //         } else {
    //             $this->stationInfo = null;
    //             $this->station_name = null;
    //             $this->stationData = null;
    //             $this->error = 'Stacja nie jest publiczna. ';
    //         }
    //     } else {
    //         $this->stationInfo = null;
    //         $this->station_name = null;
    //         $this->stationData = null;
    //         $this->error = 'Nie udało się pobrać danych dla tej stacji. ';
    //     }
    //     // Not in cache, search locally
    //     try {
    //         $record = collect($this->stationData)->firstWhere('id', $kod);

    //         if ($record) {
    //             $this->stationDataId = (array) $record;
    //             // Cache the result for 5 minutes
    //             // Cache::put('DaneStacjiL' . $kod, $this->stationDataId, now()->addMinutes(5));
    //         } else {
    //             $this->stationDataId = [];
    //             $this->stationId = null;
    //             if ($kod !== null) {
    //                 $this->error = 'Nie znaleziono danych dla podanej stacji.';
    //             }
    //         }
    //     } catch (\Throwable $th) {
    //         $this->stationDataId = [];
    //         $this->stationId = null;
    //         if ($kod !== null) {
    //             $this->error = 'Nie znaleziono danych dla podanej stacji.';
    //         }
    //     }
    // }
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
        return view('livewire.map-community');
    }
}
