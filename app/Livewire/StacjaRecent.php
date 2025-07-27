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

    // #[Url(except: null)]
    // public $year;

    // #[Url(except: null)]
    // public $month;

    #[Validate('string|in:temperatura_gruntu_data,opad_10min_data', message: 'Zły format zmiennej!')]
    public string $sortBy = 'temperatura_gruntu_data';


    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'
    public bool  $stop = false;
    public $stations = [];
    public $error = null;
    public $info;
    protected $stationListPath = 'imgw/wykaz_stacji.csv';
    protected $stationListUrl = 'https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv';
    protected $refreshDays = 7;

    public string $dateOption = 'today'; // 'dzis', 'wczoraj', '7 dni'
    public $weatherData = [];

    public function mount()
    {
        $this->stations = $this->getStationsProperty();
        $this->validate();
        $this->loadData();
    }
    public function updatedDateOption()
    {
        if ($this->stationId && isset($this->stations[$this->stationId])) {
            $this->loadData();
        } else {
            $this->weatherData = [];
        }
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

    public function getSortedWeatherDataProperty()
    {
        $data = collect($this->weatherData);

        if ($this->sortBy !== '') {
            $data = $data->sortBy(function ($item) {
                return $item[$this->sortBy] ?? null;
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return $data->values()->all();
    }

    public function loadData()
    {
        $this->weatherData = [];

        if ($this->dateOption === 'today') {
            $date = Carbon::today();
            $this->weatherData = $this->loadDataForDate($date);
        } elseif ($this->dateOption === 'yesterday') {
            $date = Carbon::yesterday();
            $this->weatherData = $this->loadDataForDate($date);
        } elseif ($this->dateOption === '7days') {
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays(6);
            $allData = [];
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dayData = $this->loadDataForDate($date);
                if (!empty($dayData)) {
                    $allData = array_merge($allData, $dayData);
                }
            }
            $this->weatherData = $allData;
        }
    }

    public function loadDataForDate(Carbon $date)
    {
        if ($this->validate()) {
            if ($this->stop == false) {

                // Build filepath, e.g.: imgw/collected/terminowe/2025/07/27.json
                $filtered = null;
                $year = $date->format('Y');
                $month = $date->format('m');
                $day = $date->format('d');
                $this->info = $date;
                $filePath = "imgw/api-data/{$year}/{$month}/{$year}-{$month}-{$day}.json";
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
            $this->sortBy = 'desc';
            $this->sortDirection = 'temperatura_gruntu_data';
        }
    }


    public function getStationsProperty(): array
    {
        return Cache::remember('station_list', now()->addDays(7), function () {
            // Step 1: Download latest CSV file
            try {
                $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get($this->stationListUrl);
                if (!$response->successful()) {
                    throw new \Exception('Nie udało się pobrać wykazu stacji.');
                }

                // Convert to UTF-8'Windows-1250', 'UTF-8//IGNORE'
                $utf8Content = iconv('Windows-1250', 'UTF-8//IGNORE', $response->body());
                Storage::put($this->stationListPath, $utf8Content);
            } catch (\Exception $e) {
                $this->error = 'Błąd pobierania listy stacji: ' . $e->getMessage();

                // Fallback: try using existing file if any
                if (!Storage::exists($this->stationListPath)) {
                    return []; // No fallback possible
                }
            }

            // Step 2: Read the file (whether downloaded or fallback)
            $lines = explode("\n", Storage::get($this->stationListPath));
            $stations = [];

            foreach ($lines as $line) {
                if (trim($line) === '') continue;

                [$id, $name] = str_getcsv($line);
                if ($id && $name) {
                    $stations[trim($id)] = trim($name);
                }
            }

            return $stations;
        });
    }

    public function render()
    {

        return view('livewire.stacja-recent');
    }
}
