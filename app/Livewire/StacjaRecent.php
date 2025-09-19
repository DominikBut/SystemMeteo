<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StacjaRecent extends Component
{
    #[Url(except: null, as: 'id', history: true)]
    #[Validate('numeric', message: 'Zły format id!')]
    public $stationId;
    public $stations = [];
    public $stationData = [];
    public string $askTime;

    // protected $stationListPath = 'imgw/wykaz_stacji.csv';
    // protected $stationListUrl = 'https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv';

    public $weatherData = [];
    public  $minMaxStats = [];

    #[Validate('string', message: 'Zły format zmiennej!')]
    public string $sortBy = 'kod_stacji';

    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'

    public $sortedData;

    public bool $stop = false;

    public $error = null;
    public $info;

    #[Validate('string|in:today,yesterday,7days', message: 'Zły format wyboru!')]
    public string $dateOption = 'today'; // 'dzis', 'wczoraj', '7 dni'

    #[Validate('string|in:30min,terminowe,dobowe,miesieczne', message: 'Zły format wyboru!')]
    public string $aggregation = '30min'; // Default mode

    public $terminoweStartDate;
    public $terminoweEndDate;
    public $doboweDate;
    public $miesieczneDate;

    public function mount()
    {
        $date = Carbon::yesterday();
        $this->terminoweEndDate = $date->format('Y-m-d');
        $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
        $this->doboweDate = $date->format('Y-m');
        $this->miesieczneDate = $date->format('Y');
        $this->stations = $this->getStationsProperty();
        $this->validate();
        $this->getStationData();
        $this->loadData();
        $this->calculateMinMaxStats();
    }

    public function setSort(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->sortWetherData();
    }

    // public function calculateMinMaxStats()
    // {
    //     $fields = [
    //         //terminowe
    //         'temperatura_gruntu',
    //         'wiatr_kierunek',
    //         'wiatr_srednia_predkosc',
    //         'wiatr_predkosc_maksymalna',
    //         'wiatr_poryw_10min',
    //         'wilgotnosc_wzgledna',
    //         'opad_10min',
    //         'mean_temp_gruntu_dobowa',
    //         'min_temp_gruntu_dobowa',
    //         'max_temp_gruntu_dobowa',
    //         'mean_wilgotnosc_wzgledna',
    //         'min_wilgotnosc_wzgledna',
    //         'max_wilgotnosc_wzgledna',
    //         'sum_opad_10min',
    //         'mean_wiatr_srednia_predkosc',
    //         'max_wiatr_predkosc_maksymalna',
    //         'max_wiatr_poryw_10min',
    //         'mean_wiatr_kierunek',
    //         'max_max_temp_gruntu_mies',
    //         'mean_max_temp_gruntu_mies',
    //         'min_min_temp_gruntu_mies',
    //         'mean_min_temp_gruntu_mies',
    //         'mean_mean_temp_gruntu_mies',
    //         'mean_mean_wiatr_kierunek',
    //         'mean_mean_wiatr_srednia_predkosc',
    //         'max_max_wiatr_predkosc_maksymalna',
    //         'max_max_wiatr_poryw_10min',
    //         'min_min_wilgotnosc_wzgledna',
    //         'mean_min_wilgotnosc_wzgledna',
    //         'max_max_wilgotnosc_wzgledna',
    //         'mean_max_wilgotnosc_wzgledna',
    //         'mean_mean_wilgotnosc_wzgledna',
    //         'max_sum_opad_10min',
    //         'sum_sum_opad_10min',
    //         'max_max_temp_powietrza_mies',
    //         'mean_max_temp_powietrza_mies',
    //         'min_min_temp_powietrza_mies',
    //         'mean_min_temp_powietrza_mies',
    //         'mean_mean_temp_powietrza_mies',
    //         'max_temp_powietrza_dobowa',
    //         'min_temp_powietrza_dobowa',
    //         'mean_temp_powietrza_dobowa',
    //         'temperatura_powietrza',
    //         'temperatura_powietrza_data',
    //     ];

    //     foreach ($fields as $field) {
    //         $values = collect($this->weatherData)->pluck($field)->filter(fn($val) => is_numeric($val));
    //         $this->minMaxStats[$field] = [
    //             'min' => $values->isEmpty() ? null : $values->min(),
    //             'max' => $values->isEmpty() ? null : $values->max(),
    //             'avg' => $values->isEmpty() ? null : round($values->avg(), 1),
    //         ];
    //     }
    // }
    public function calculateMinMaxStats()
    {
        $fields = [
            // terminowe + dobowe + miesieczne
            'temperatura_gruntu',
            'wiatr_kierunek',
            'wiatr_srednia_predkosc',
            'wiatr_predkosc_maksymalna',
            'wiatr_poryw_10min',
            'wilgotnosc_wzgledna',
            'opad_10min',
            'mean_temp_gruntu_dobowa',
            'min_temp_gruntu_dobowa',
            'max_temp_gruntu_dobowa',
            'mean_wilgotnosc_wzgledna',
            'min_wilgotnosc_wzgledna',
            'max_wilgotnosc_wzgledna',
            'sum_opad_10min',
            'mean_wiatr_srednia_predkosc',
            'max_wiatr_predkosc_maksymalna',
            'max_wiatr_poryw_10min',
            'mean_wiatr_kierunek',
            'max_max_temp_gruntu_mies',
            'mean_max_temp_gruntu_mies',
            'min_min_temp_gruntu_mies',
            'mean_min_temp_gruntu_mies',
            'mean_mean_temp_gruntu_mies',
            'mean_mean_wiatr_kierunek',
            'mean_mean_wiatr_srednia_predkosc',
            'max_max_wiatr_predkosc_maksymalna',
            'max_max_wiatr_poryw_10min',
            'min_min_wilgotnosc_wzgledna',
            'mean_min_wilgotnosc_wzgledna',
            'max_max_wilgotnosc_wzgledna',
            'mean_max_wilgotnosc_wzgledna',
            'mean_mean_wilgotnosc_wzgledna',
            'max_sum_opad_10min',
            'sum_sum_opad_10min',
            'max_max_temp_powietrza_mies',
            'mean_max_temp_powietrza_mies',
            'min_min_temp_powietrza_mies',
            'mean_min_temp_powietrza_mies',
            'mean_mean_temp_powietrza_mies',
            'max_temp_powietrza_dobowa',
            'min_temp_powietrza_dobowa',
            'mean_temp_powietrza_dobowa',
            'temperatura_powietrza',
            'temperatura_powietrza_data',
        ];

        foreach ($fields as $field) {
            $values = collect($this->weatherData)
                ->pluck($field)
                ->filter(fn($val) => is_numeric($val))
                ->map(fn($v) => (float) $v);

            if ($values->isEmpty()) {
                $this->minMaxStats[$field] = [
                    'min'      => null,
                    'max'      => null,
                    'avg'      => null,
                    'median'   => null,
                    'std'      => null,
                    'variance' => null,
                    'sum'      => null,
                    'count'    => 0,
                ];
                continue;
            }

            $minValue   = $values->min();
            $maxValue   = $values->max();
            $avgValue   = round($values->avg(), 1);
            $sumValue   = round($values->sum(), 1);
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

            $this->minMaxStats[$field] = [
                'min'      => $minValue,
                'max'      => $maxValue,
                'avg'      => $avgValue,
                'median'   => $median,
                'std'      => $std,
                'variance' => round($variance, 2),
                'sum'      => $sumValue,
                'count'    => $countValue,
            ];
        }
    }

    public function updatedStationId()
    {
        if ($this->validate()) {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $this->miesieczneDate = $date->format('Y');
            $this->sortBy = 'kod_stacji';
            $this->error = null;
            $this->sortDirection = 'desc';
            $this->stationData = [];
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'kod_stacji';
            $this->getStationData();
        }
    }
    public function getStationData()
    {
        try {
            if (!Cache::has('DaneStacji' . $this->stationId)) {
                $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/api/data/meteo/id/{$this->stationId}");
                if ($response->successful()) {
                    $this->stationData =  (array) json_decode($response->body())[0];
                    Cache::put('DaneStacji' . $this->stationId, $this->stationData, now()->addMinutes(5));
                    $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
                    Cache::put('AskTime' . $this->stationId, $this->askTime);
                } else {
                    $this->stationData = [];
                }
            } else {
                $this->stationData = Cache::get('DaneStacji' . $this->stationId);
                $this->askTime = Cache::get('AskTime' . $this->stationId);
            }
        } catch (\Throwable $th) {
            $this->stationData = [];
            $this->error = 'Nie udało się pobrać danych z API. ';
        }
    }

    public function sortWetherData()
    {
        $this->sortedData = collect($this->weatherData);

        if ($this->sortBy !== '') {
            $data = $this->sortedData->sortBy(function ($item) {
                $item[$this->sortBy] ?? $this->sortBy = 'kod_stacji';
                return is_null($item[$this->sortBy]) ? PHP_INT_MAX : $item[$this->sortBy];
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }
        $this->sortedData = $data->values()->all();
        // //$this->loadData(); //tu ejst problem bo odswieza wykres ? ale mozna prosto zamienic na inna zmienna i tyl?
        // $this->sortedData = collect($this->weatherData);

        // if ($this->sortBy !== '') {
        //     $data = $this->sortedData->sortBy(function ($item) {
        //         // Fallback to 'kod_stacji' if the desired key doesn't exist
        //         $value = $item[$this->sortBy] ?? $item['kod_stacji'] ?? PHP_INT_MAX;
        //         return is_null($value) ? PHP_INT_MAX : $value;
        //     }, SORT_REGULAR, $this->sortDirection === 'desc');

        //     $this->sortedData = $data->values()->all();
        // }
    }
    public function loadData()
    {

        $this->weatherData = [];
        if ($this->stations) {
            switch ($this->aggregation) {
                case 'terminowe':
                    $this->loadTerminoweData();
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    $this->calculateMinMaxStats();
                    $this->sortedData = $this->weatherData;
                    $this->sortBy = 'kod_stacji';
                    break;
                case 'dobowe':
                    $date = Carbon::parse($this->doboweDate . '-01');
                    $this->weatherData = $this->loadDataForDate($date);
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    $this->calculateMinMaxStats();
                    $this->sortedData = $this->weatherData;
                    $this->sortBy = 'kod_stacji';
                    break;
                case 'miesieczne':
                    $date = Carbon::parse($this->miesieczneDate . '-01' . '-01');
                    $this->weatherData = $this->loadDataForDate($date);
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    $this->sortedData = $this->weatherData;
                    $this->sortBy = 'kod_stacji';
                    $this->calculateMinMaxStats();
                    break;
                default:
                    $this->load30MinData();
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation, $this->dateOption], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    $this->calculateMinMaxStats();
                    $this->sortedData = $this->weatherData;
                    $this->sortBy = 'kod_stacji';
                    break;
            }
        }
    }
    public function load30MinData()
    {
        switch ($this->dateOption) {
            case 'today':
                $date = Carbon::today();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'kod_stacji';
                break;
            case 'yesterday':
                $date = Carbon::yesterday();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'kod_stacji';
                break;
            case '7days':
                $endDate = Carbon::yesterday();
                $startDate = $endDate->copy()->subDays(6);
                $allData = [];
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $dayData = $this->loadDataForDate($date);
                    if (!empty($dayData)) {
                        $allData = array_merge($allData, $dayData);
                    }
                }
                $this->weatherData = $allData;
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'kod_stacji';
                break;
            default:
                $date = Carbon::today();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'kod_stacji';
                break;
        }
    }
    public function loadTerminoweData()
    {
        if ($this->validate()) {
            if (!$this->stop) {
                $startDate = Carbon::parse($this->terminoweStartDate);
                $endDate = Carbon::parse($this->terminoweEndDate);
                $year = $startDate->format('Y');
                $month = $startDate->format('m');
                $day1 = $startDate->format('d');
                $day2 = $endDate->format('d');

                $this->weatherData = [];
                $combinedData = [];
                for ($day = $day1; $day <= $day2; $day++) {
                    $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
                    $filePath = "imgw/collected/terminowe/{$year}/{$month}/{$year}-{$month}-{$dayFormatted}.json";

                    if (!Storage::exists($filePath)) {
                        continue;
                    }

                    $json = Storage::get($filePath);
                    $data = json_decode($json, true);
                    if (!$data) {
                        continue;
                    }
                    $json = null;
                    // Filter by stationId
                    $filtered = array_filter($data, function ($record) {
                        return isset($record['kod_stacji']) && $record['kod_stacji'] == $this->stationId;
                    });

                    if (count($filtered) === 0) {
                        // Fallback to name
                        $stationName = $this->stations[$this->stationId] ?? null;
                        if ($stationName) {
                            $filtered = array_filter($data, function ($record) use ($stationName) {
                                return isset($record['nazwa_stacji']) &&
                                    strcasecmp(trim($record['nazwa_stacji']), trim($stationName)) === 0;
                            });
                        }
                    }
                    $data = null;

                    $combinedData = array_merge($combinedData, array_values($filtered));
                    $filtered = null;
                }

                $this->weatherData = $combinedData;
                $combinedData = null;
            }
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $this->miesieczneDate = $date->format('Y');

            $this->sortDirection = 'desc';
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'kod_stacji';
        }
    }

    public function loadDataForDate(Carbon $date)
    {
        if ($this->validate()) {
            if ($this->stop == false) {
                $filtered = null;

                switch ($this->aggregation) {
                    case '30min':
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $day = $date->format('d');
                        $filePath = "imgw/api-data/{$year}/{$month}/{$year}-{$month}-{$day}.json";
                        break;
                    case 'dobowe':
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $filePath = "imgw/collected/dobowe/{$year}/{$year}-{$month}.json";
                        break;
                    case 'miesieczne':
                        $year = $date->format('Y');
                        $filePath = "imgw/collected/miesieczne/{$year}.json";
                        break;
                    default:
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $day = $date->format('d');
                        $filePath = "imgw/api-data/{$year}/{$month}/{$year}-{$month}-{$day}.json";
                        break;
                }

                $this->info = $filePath . "" . $this->stationId;

                if (!Storage::exists($filePath)) {
                    return [];
                }

                $json = Storage::get($filePath);
                $data = json_decode($json, true);
                if (!$data) {
                    return [];
                }
                $json = null;
                // Filter by stationId
                $filtered = array_filter($data, function ($record) {
                    return isset($record['kod_stacji']) && $record['kod_stacji'] == $this->stationId;
                });

                if (count($filtered) === 0) {
                    // fallback: get station name from cached list by stationId key
                    $stationName = $this->stations[$this->stationId] ?? null;
                    if ($stationName) {
                        // filter by nazwa_stacji (case-insensitive match)
                        $filtered = array_filter($data, function ($record) use ($stationName) {
                            return isset($record['nazwa_stacji'])
                                && strcasecmp(trim($record['nazwa_stacji']), trim($stationName)) === 0;
                        });
                    }
                }
                $data = null;
                return array_values($filtered); // reindex
            }
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $this->miesieczneDate = $date->format('Y');

            $this->sortDirection = 'desc';
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'kod_stacji';
        }
    }


    public function getStationsProperty(): array
    {
        return $this->stations = Cache::remember('station_list_api', now()->addHour(1), function () {
            // Download latest CSV file
            try {
                $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

                if (!$response->successful()) {
                    throw new \Exception('Nie udało się pobrać wykazu stacji.');
                }
                $data = $response->json();
                $stations = [];
                foreach ($data as $entry) {
                    $id = trim($entry['kod_stacji'] ?? '');
                    $name = trim($entry['nazwa_stacji'] ?? '');

                    if ($id && $name) {
                        $stations[$id] = $name;
                    }
                }
                return $stations;
            } catch (\Exception $e) {
                $this->error = 'Błąd pobierania listy stacji: ' . $e->getMessage();
                return [];
            }
        });
        // return Cache::remember('station_list', now()->addDays(7), function () {
        //     // Step 1: Download latest CSV file
        //     try {
        //         $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get($this->stationListUrl);
        //         if (!$response->successful()) {
        //             throw new \Exception('Nie udało się pobrać wykazu stacji.');
        //         }

        //         // Convert to UTF-8'Windows-1250', 'UTF-8//IGNORE'
        //         $utf8Content = iconv('Windows-1250', 'UTF-8//IGNORE', $response->body());
        //         Storage::put($this->stationListPath, $utf8Content);
        //     } catch (\Exception $e) {
        //         $this->error = 'Błąd pobierania listy stacji: ' . $e->getMessage();

        //         // Fallback: try using existing file if any
        //         if (!Storage::exists($this->stationListPath)) {
        //             return []; // No fallback possible
        //         }
        //     }

        //     // Step 2: Read the file (whether downloaded or fallback)
        //     $lines = explode("\n", Storage::get($this->stationListPath));
        //     $stations = [];

        //     foreach ($lines as $line) {
        //         if (trim($line) === '') continue;

        //         [$id, $name] = str_getcsv($line);
        //         if ($id && $name) {
        //             $stations[trim($id)] = trim($name);
        //         }
        //     }

        //     return $stations;
        // });
    }

    public function render()
    {

        return view('livewire.stacja-recent');
    }
}
