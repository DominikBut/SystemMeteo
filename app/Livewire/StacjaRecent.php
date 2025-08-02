<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StacjaRecent extends Component
{
    #[Url(except: null, as: 'id')]
    #[Validate('numeric', message: 'Zły format id!')]
    public $stationId;
    public $stations = [];
    public $stationData = [];
    public string $askTime;

    // protected $stationListPath = 'imgw/wykaz_stacji.csv';
    // protected $stationListUrl = 'https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv';

    public $weatherData = [];

    #[Validate('string|in:temperatura_gruntu_data,temperatura_gruntu,wilgotnosc_wzgledna,wilgotnosc_wzgledna_data,', message: 'Zły format zmiennej!')]
    public string $sortBy = 'temperatura_gruntu_data';

    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'

    public bool $stop = false;

    public $error = null;
    public $info;

    #[Validate('string|in:today,yesterday,7days', message: 'Zły format wyboru!')]
    public string $dateOption = 'today'; // 'dzis', 'wczoraj', '7 dni'
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
    }

    public function setSort(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
    public function updatedStationId()
    {
        if ($this->validate()) {
            $this->getStationData();
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $this->miesieczneDate = $date->format('Y');
            $this->sortBy = 'desc';
            $this->sortDirection = 'temperatura_gruntu_data';
            $this->stationData = [];
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

    public function getSortedWeatherDataProperty()
    {
        $data = collect($this->weatherData);

        if ($this->sortBy !== '') {
            $data = $data->sortBy(function ($item) {
                return $item[$this->sortBy] ?? null;
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        $this->loadData();
        return $data->values()->all();
    }
    public function loadData()
    {
        $this->weatherData = [];
        if ($this->stations) {
            switch ($this->aggregation) {
                case '30min':
                    $this->load30MinData();
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation, $this->dateOption], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    break;
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
                    break;
                default:
                    $this->load30MinData();
                    $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->aggregation], [
                        'aggregation' => $this->aggregation,
                        'dateOption' => $this->dateOption,
                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
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
                break;
            case 'yesterday':
                $date = Carbon::yesterday();
                $this->weatherData = $this->loadDataForDate($date);
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
                break;
            default:
                $date = Carbon::today();
                $this->weatherData = $this->loadDataForDate($date);
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

                    $filePath = "imgw/collected/terminowe/{$year}/{$month}/{$year}-{$month}-{$day}.json";

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
            $this->sortBy = 'desc';
            $this->sortDirection = 'temperatura_gruntu_data';
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
                // Build filepath, e.g.: imgw/collected/terminowe/2025/07/27.json

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
            $this->sortBy = 'desc';
            $this->sortDirection = 'temperatura_gruntu_data';
        }
    }


    public function getStationsProperty(): array
    {
        return $this->stations = Cache::remember('station_list_api', now()->addHour(1), function () {
            // Step 1: Download latest CSV file
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
                //$latestUTC = $timestamps ? max($timestamps) : now()->toISOString();

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
