<?php

namespace App\Livewire;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StacjaRead extends Component
{

    #[Url(except: null, as: 'id', history: true)]
    #[Validate('numeric', message: 'Zły format id!')]
    public $stationId;
    public $stations = [];
    public $stationData = [];
    public string $askTime;

    protected $stationListPath = 'imgw/wykaz_stacji.csv';
    protected $stationListUrl = 'https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv';

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


    #[Validate('string|in:terminowe,dobowe,miesieczne', message: 'Zły format wyboru!')]
    public string $aggregation = 'terminowe'; // Default mode

    public $terminoweStartDate;
    public $terminoweEndDate;
    public $doboweDate;
    public $miesieczneDate;
    protected string $delimiter = ',';
    public $doboweType = false;
    public $miesieczneType = false;


    public function mount()
    {
        $this->doboweType = false;
        $this->miesieczneType = false;
        $today = Carbon::today();
        $monthsAgo = $today->day > 10 ? 2 : 3;
        $date = $today->copy()->subMonthsNoOverflow($monthsAgo)->endOfMonth();
        $this->terminoweEndDate = $date->format('Y-m-d');
        $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
        $this->doboweDate = $date->format('Y-m');
        $date2 = now(); // or Carbon::now()
        if ($date2->month > 2 || ($date2->month === 2 && $date2->day >= 10)) {
            $this->miesieczneDate = $date2->subYear()->format('Y');
        } else {
            $this->miesieczneDate = $date2->subYears(2)->format('Y');
        }
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
    public function updatedAggregation()
    {
        $this->doboweType = false;
        $this->miesieczneType = false;
    }
    public function calculateMinMaxStats()
    {
        $fields = [
            //terminowe
            "temperatura_powietrza",
            "temp_term_zw",
            "wilgotnosc_wzgledna",
            "wiatr_srednia_predkosc",
            "zachmurzenie",
            //dobowe
            "max_temp_powietrza_dobowa",
            "min_temp_powietrza_dobowa",
            "mean_temp_powietrza_dobowa",
            "min_temp_gruntu_dobowa",
            "sum_opad_10min",
            "pokrywa_sniezna_wys",


            //miesieczne
            "max_max_temp_powietrza_mies",
            "min_min_temp_powietrza_mies",
            "abs_max_max_temp_powietrza_mies",
            "abs_min_min_temp_powietrza_mies",
            "mean_mean_temp_powietrza_mies",
            "min_min_temp_gruntu_mies",
            "sum_sum_opad_10min",
            "max_sum_opad_10min",

            "dni_deszcz_opad_10min",
            "dni_snieg_opad_10min",
            "pokrywa_sniezna_wys",
            "dni_pokrywa_sniezna_wys",

            'temperatura_gruntu',
            'wiatr_srednia_predkosc',
            'wiatr_predkosc_maksymalna',
            'wiatr_poryw_10min',
            'wilgotnosc_wzgledna',


        ];

        foreach ($fields as $field) {
            $values = collect($this->weatherData)->pluck($field)->filter(fn($val) => is_numeric($val));
            $this->minMaxStats[$field] = [
                'min' => $values->isEmpty() ? null : $values->min(),
                'max' => $values->isEmpty() ? null : $values->max(),
                'avg' => $values->isEmpty() ? null : round($values->avg(), 1),
            ];
            // Extra stats ONLY for 'sum_opad_10min'
            if ($field === 'sum_opad_10min') {
                // For snow (contains 'S')
                $snowValues = collect($this->weatherData)
                    ->filter(fn($row) => str_contains($row['rodzaj_opadu'] ?? '', 'S'))
                    ->pluck($field)
                    ->filter(fn($val) => is_numeric($val));

                $this->minMaxStats["{$field}_snow"] = [
                    'min' => $snowValues->isEmpty() ? null : $snowValues->min(),
                    'max' => $snowValues->isEmpty() ? null : $snowValues->max(),
                    'avg' => $snowValues->isEmpty() ? null : round($snowValues->avg(), 1),
                ];

                // For rain (contains 'W')
                $rainValues = collect($this->weatherData)
                    ->filter(fn($row) => str_contains($row['rodzaj_opadu'] ?? '', 'W'))
                    ->pluck($field)
                    ->filter(fn($val) => is_numeric($val));

                $this->minMaxStats["{$field}_rain"] = [
                    'min' => $rainValues->isEmpty() ? null : $rainValues->min(),
                    'max' => $rainValues->isEmpty() ? null : $rainValues->max(),
                    'avg' => $rainValues->isEmpty() ? null : round($rainValues->avg(), 1),
                ];
            }
        }
    }
    public function updatedStationId()
    {
        if ($this->validate()) {
            $today = Carbon::today();
            $monthsAgo = $today->day > 10 ? 2 : 3;
            $date = $today->copy()->subMonthsNoOverflow($monthsAgo)->endOfMonth();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $date2 = now(); // or Carbon::now()
            if ($date2->month > 2 || ($date2->month === 2 && $date2->day >= 10)) {
                $this->miesieczneDate = $date2->subYear()->format('Y');
            } else {
                $this->miesieczneDate = $date2->subYears(2)->format('Y');
            }
            $this->error = null;
            $this->sortBy = 'kod_stacji';
            $this->sortDirection = 'desc';
            $this->stationData = [];
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'kod_stacji';
            $this->getStationData();
        } else {
            $today = Carbon::today();
            $monthsAgo = $today->day > 10 ? 2 : 3;
            $date = $today->copy()->subMonthsNoOverflow($monthsAgo)->endOfMonth();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $date2 = now(); // or Carbon::now()
            if ($date2->month > 2 || ($date2->month === 2 && $date2->day >= 10)) {
                $this->miesieczneDate = $date2->subYear()->format('Y');
            } else {
                $this->miesieczneDate = $date2->subYears(2)->format('Y');
            }
            $this->error = null;
            $this->doboweType = false;
            $this->miesieczneType = false;
            $this->sortBy = 'kod_stacji';
            $this->sortDirection = 'desc';
            $this->stationData = [];
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'kod_stacji';
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
    }
    public function loadData()
    {

        $this->weatherData = [];
        if ($this->stations) {
            switch ($this->aggregation) {
                case 'dobowe':
                    $date = Carbon::parse($this->doboweDate . '-01');
                    $this->weatherData = $this->loadDataForDate($date);
                    $this->dispatch('weatherDataUpdated2', [$this->weatherData, $this->aggregation, $this->doboweType], [
                        'aggregation' => $this->aggregation,

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
                    $this->dispatch('weatherDataUpdated2', [$this->weatherData, $this->aggregation, $this->miesieczneType], [
                        'aggregation' => $this->aggregation,

                        'terminoweStartDate' => $this->terminoweStartDate,
                        'terminoweEndDate' => $this->terminoweEndDate,
                        'doboweDate' => $this->doboweDate,
                        'miesieczneDate' => $this->miesieczneDate,
                    ]);
                    $this->sortedData = $this->weatherData;
                    $this->sortBy = 'kod_stacji';
                    $this->calculateMinMaxStats();
                    break;
                // case 'terminowe':
                //     $this->loadTerminoweData();
                //     $this->dispatch('weatherDataUpdated2', [$this->weatherData, $this->aggregation], [
                //         'aggregation' => $this->aggregation,

                //         'terminoweStartDate' => $this->terminoweStartDate,
                //         'terminoweEndDate' => $this->terminoweEndDate,
                //         'doboweDate' => $this->doboweDate,
                //         'miesieczneDate' => $this->miesieczneDate,
                //     ]);
                //     $this->calculateMinMaxStats();
                //     $this->sortedData = $this->weatherData;
                //     $this->sortBy = 'kod_stacji';
                //     break;
                default:
                    $this->loadTerminoweData();
                    $this->dispatch('weatherDataUpdated2', [$this->weatherData, $this->aggregation], [
                        'aggregation' => $this->aggregation,

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
                $filtered = null;

                $relativeFolder = "imgw/archived/terminowe/{$year}";
                $relativeCsvPath = $relativeFolder . "/k_t_{$month}_{$year}.csv";
                $fileName = "{$year}_{$month}_k.zip";
                $relativeZipPath = $relativeFolder . "/{$fileName}";


                $this->info = $relativeCsvPath . "" . $this->stationId;

                // Step 1: Download ZIP if not exists
                if (!Storage::exists($relativeCsvPath) && !Storage::exists($relativeZipPath)) {
                    try {
                        $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/terminowe/klimat/{$year}/{$fileName}");
                        if ($response->ok()) {
                            Storage::put($relativeZipPath, $response->body());
                            $this->info = "Pobrano.";
                        } else {
                            $this->error = "Nie udało się pobrać pliku ZIP.";
                            $this->weatherData = [];
                        }
                    } catch (\Exception $e) {
                        $this->error = "Błąd podczas pobierania: "; //$e->getMessage()
                        $this->weatherData = [];
                    }
                } else {
                    $this->info = "Istnieje zip.";
                }
                // Step 2: Extract CSV if not already extracted
                if (!Storage::exists($relativeCsvPath)) {
                    $zipPath = Storage::path($relativeZipPath);
                    $extractPath = Storage::path($relativeFolder);

                    $zip = new ZipArchive();
                    if ($zip->open($zipPath) === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();
                        Storage::delete($relativeZipPath); // delete ZIP after extraction
                        // if ($this->aggregation === 'dobowe') {
                        //     Storage::delete($relativeFolder . "/k_d_t_{$month}_{$year}.csv"); //delete additional file that comes in some zips
                        // } else {
                        //     Storage::delete($relativeFolder . "/k_m_t_{$year}.csv"); //delete additional file that comes in some zips
                        // }


                        $this->info = "Rozpakowano.";
                    } else {
                        $this->error = "Nie można rozpakować pliku ZIP.";
                        $this->weatherData = [];
                    }
                } else {
                    $this->info = "Istnieje plik.";
                }

                try {
                    $csvPath = Storage::path($relativeCsvPath);
                    $file_handle = fopen($csvPath, 'r');

                    $filtered = [];

                    while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                        // Skip malformed rows or ones with mostly empty cells
                        if (!is_array($csvRow) || count(array_filter($csvRow)) < 5) continue;

                        $csvRow = array_map(function ($value) {
                            $value = trim($value);
                            $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                            return $value;
                        }, $csvRow);

                        // CSV column mapping based on provided header description
                        [
                            $kod_stacji,
                            $nazwa_stacji,
                            $rok,
                            $miesiac,
                            $dzien,
                            $godzina,
                            $temp_pow,
                            $temp_pow_status,
                            $temp_term_zw,
                            $temp_term_zw_status,
                            $wskaz_lodu, //Wskaźnik lodu [L/W]
                            $wskaz_wentylacji, //Wskaźnik wentylacji [W/N]
                            $wilg,
                            $wilg_status,
                            $wiatr_kierunek_kod, //Kod kierunku wiatru [kod]
                            $wiatr_kierunek_kod_status,
                            $wiatr_srednia_predkosc,
                            $wiatr_srednia_predkosc_status,
                            $zachmurzenie, //Zachmurzenie ogólne [0-10 do dn.31.12.1988/oktanty od dn.01.01.1989]  5
                            $zachmurzenie_status,
                            $widzialnosc, //Widzialność [kod]
                            $widzialnosc_status
                        ] = array_pad($csvRow, 22, null); // pad in case fewer than 18 values

                        // Validate minimal required fields
                        if (!is_numeric($kod_stacji) || !is_numeric($rok) || !is_numeric($miesiac) || !is_numeric($dzien)) {
                            continue;
                        }

                        if ((int)$kod_stacji == (int) $this->stationId && ($dzien >= $day1 && $dzien <= $day2)) {
                            $filtered[] = [
                                "kod_stacji" => $kod_stacji,
                                "nazwa_stacji" => $nazwa_stacji,
                                "data" => sprintf('%04d-%02d-%02d %02d:00:00', $rok, $miesiac, $dzien, $godzina),
                                "temperatura_powietrza" => ($temp_pow_status === '' || is_null($temp_pow_status)) ? (float) $temp_pow : null,
                                "temp_term_zw" => ($temp_term_zw_status === '' || is_null($temp_term_zw_status)) ? (float) $temp_term_zw : null,

                                "wskaz_lodu" => (!$wskaz_lodu === '' || !empty($wskaz_lodu)) ? (string) $wskaz_lodu : null,
                                "wskaz_wentylacji" => (!$wskaz_wentylacji === '' || !empty($wskaz_wentylacji)) ? (string) $wskaz_wentylacji : null,

                                "wilgotnosc_wzgledna" => ($wilg_status === '' || is_null($wilg_status)) ? (float) $wilg : null,
                                "wiatr_srednia_predkosc" => ($wiatr_srednia_predkosc_status === '' || is_null($wiatr_srednia_predkosc_status)) ? (float) $wiatr_srednia_predkosc : null,
                                "wiatr_kierunek_kod" => ($wiatr_kierunek_kod_status === '' || is_null($wiatr_kierunek_kod_status)) ? (string) $wiatr_kierunek_kod : null,
                                "zachmurzenie" => ($zachmurzenie_status === '' || is_null($zachmurzenie_status)) ? (string) $zachmurzenie : null,
                                "widzialnosc" => ($widzialnosc_status === '' || is_null($widzialnosc_status)) ? (string) $widzialnosc : null,
                            ];
                        }
                    }

                    $this->weatherData = array_values($filtered);
                    $filtered = null;
                } catch (\Exception $e) {
                    $this->error = "Błąd odczytu pliku CSV."; //$e->getMessage()
                    $this->weatherData = [];
                }
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
        //add checka for wersje t mies dobowe
        if ($this->validate()) {
            if ($this->stop == false) {
                $filtered = null;
                $relativeCsvPath = null;
                switch ($this->aggregation) {
                    // case 'dobowe':
                    //     $year = $date->format('Y');
                    //     $month = $date->format('m');
                    //     $relativeFolder = "imgw/archived/dobowe/{$year}";
                    //     if ($this->doboweType === false) {
                    //         $relativeCsvPath = $relativeFolder . "/k_d_{$month}_{$year}.csv";
                    //     } else {
                    //         $relativeCsvPath = $relativeFolder . "/k_d_t_{$month}_{$year}.csv";
                    //     }

                    //     $fileName = "{$year}_{$month}_k.zip";
                    //     $relativeZipPath = $relativeFolder . "/{$fileName}";
                    //     break;

                    case 'miesieczne':
                        $year = $date->format('Y');
                        $relativeFolder = "imgw/archived/miesieczne/{$year}";
                        if ($this->miesieczneType === false) {
                            $relativeCsvPath = $relativeFolder . "/k_m_d_{$year}.csv";
                        } else {
                            $relativeCsvPath = $relativeFolder . "/k_m_t_{$year}.csv";
                        }

                        $fileName = "{$year}_m_k.zip";
                        $relativeZipPath = $relativeFolder . "/{$fileName}";
                        break;
                    default:
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $relativeFolder = "imgw/archived/dobowe/{$year}";
                        if ($this->doboweType === false) {
                            $relativeCsvPath = $relativeFolder . "/k_d_{$month}_{$year}.csv";
                        } else {
                            $relativeCsvPath = $relativeFolder . "/k_d_t_{$month}_{$year}.csv";
                        }
                        $fileName = "{$year}_{$month}_k.zip";
                        $relativeZipPath = $relativeFolder . "/{$fileName}";
                        break;
                }

                $this->info = $relativeCsvPath . "" . $this->stationId;

                // Step 1: Download ZIP if not exists
                if (!Storage::exists($relativeCsvPath) && !Storage::exists($relativeZipPath)) {
                    try {
                        $response = Http::timeout(5)->connectTimeout(5)->retry(3, 100)->get("https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/{$this->aggregation}/klimat/{$year}/{$fileName}");
                        if ($response->ok()) {
                            Storage::put($relativeZipPath, $response->body());
                            $this->info = "Pobrano.";
                        } else {
                            $this->error = "Nie udało się pobrać pliku ZIP.";
                            return [];
                        }
                    } catch (\Exception $e) {
                        $this->error = "Błąd podczas pobierania: "; //$e->getMessage()
                        return [];
                    }
                } else {
                    $this->info = "Istnieje zip.";
                }
                // Step 2: Extract CSV if not already extracted
                if (!Storage::exists($relativeCsvPath)) {
                    $zipPath = Storage::path($relativeZipPath);
                    $extractPath = Storage::path($relativeFolder);

                    $zip = new ZipArchive();
                    if ($zip->open($zipPath) === true) {
                        $zip->extractTo($extractPath);
                        $zip->close();
                        Storage::delete($relativeZipPath); // delete ZIP after extraction
                        // if ($this->aggregation === 'dobowe') {
                        //     Storage::delete($relativeFolder . "/k_d_t_{$month}_{$year}.csv"); //delete additional file that comes in some zips
                        // } else {
                        //     Storage::delete($relativeFolder . "/k_m_t_{$year}.csv"); //delete additional file that comes in some zips
                        // }


                        $this->info = "Rozpakowano.";
                    } else {
                        $this->error = "Nie można rozpakować pliku ZIP.";
                        return [];
                    }
                } else {
                    $this->info = "Istnieje plik.";
                }

                //maybe cache for reading this? based on date
                // Step 3: Read CSV and find matching rows
                try {
                    $csvPath = Storage::path($relativeCsvPath);
                    $file_handle = fopen($csvPath, 'r');


                    $filtered = [];
                    if ($this->aggregation === 'dobowe') {
                        if ($this->doboweType === false) {
                            while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                                // Skip malformed rows or ones with mostly empty cells
                                if (!is_array($csvRow) || count(array_filter($csvRow)) < 5) continue;

                                $csvRow = array_map(function ($value) {
                                    $value = trim($value);
                                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                                    return $value;
                                }, $csvRow);

                                // CSV column mapping based on provided header description
                                [
                                    $kod_stacji,
                                    $nazwa_stacji,
                                    $rok,
                                    $miesiac,
                                    $dzien,
                                    $tmax,
                                    $tmax_status,
                                    $tmin,
                                    $tmin_status,
                                    $tavg,
                                    $tavg_status,
                                    $temp_grunt_min,
                                    $temp_grunt_min_status,
                                    $suma_opadow,
                                    $suma_opadow_status,
                                    $rodzaj_opadu,
                                    $pokrywa_sniezna_wys,
                                    $pokrywa_sniezna_wys_status
                                ] = array_pad($csvRow, 18, null); // pad in case fewer than 18 values

                                // Validate minimal required fields
                                if (!is_numeric($kod_stacji) || !is_numeric($rok) || !is_numeric($miesiac) || !is_numeric($dzien)) {
                                    continue;
                                }
                                if ((int)$kod_stacji === (int) $this->stationId) {
                                    $filtered[] = [
                                        "kod_stacji" => $kod_stacji,
                                        "nazwa_stacji" => $nazwa_stacji,
                                        "data" => sprintf('%04d-%02d-%02d', $rok, $miesiac, $dzien),
                                        "max_temp_powietrza_dobowa" => ($tmax_status === '' || is_null($tmax_status)) ? (float) $tmax : null,
                                        "min_temp_powietrza_dobowa" => ($tmin_status === '' || is_null($tmin_status)) ? (float) $tmin : null,
                                        "mean_temp_powietrza_dobowa" => ($tavg_status === '' || is_null($tavg_status)) ? (float) $tavg : null,
                                        "min_temp_gruntu_dobowa" => ($temp_grunt_min_status === '' || is_null($temp_grunt_min_status)) ? (float) $temp_grunt_min : null,

                                        "sum_opad_10min" => ($suma_opadow_status === '' || is_null($suma_opadow_status)) ? (float) $suma_opadow : null,
                                        "pokrywa_sniezna_wys" => ($pokrywa_sniezna_wys_status === '' || is_null($pokrywa_sniezna_wys_status)) ? (float) $pokrywa_sniezna_wys : null,

                                        "rodzaj_opadu" => ($suma_opadow_status === '' || is_null($suma_opadow_status)) ? (string) $rodzaj_opadu : null,

                                    ];
                                }
                            }
                        }
                        if ($this->doboweType === true) {

                            while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                                // Skip malformed rows or ones with mostly empty cells
                                if (!is_array($csvRow) || count(array_filter($csvRow)) < 5) continue;

                                $csvRow = array_map(function ($value) {
                                    $value = trim($value);
                                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                                    return $value;
                                }, $csvRow);
                                // CSV column mapping based on provided header description
                                [
                                    $kod_stacji,
                                    $nazwa_stacji,
                                    $rok,
                                    $miesiac,
                                    $dzien,
                                    $temp_pow,
                                    $temp_pow_status,
                                    $wilg,
                                    $wilg_status,
                                    $wiatr_srednia_predkosc,
                                    $wiatr_srednia_predkosc_status,
                                    $zachmurzenie, //Zachmurzenie ogólne [0-10 do dn.31.12.1988/oktanty od dn.01.01.1989]  5
                                    $zachmurzenie_status,
                                ] = array_pad($csvRow, 13, null); // pad in case fewer than 18 values

                                // Validate minimal required fields
                                if (!is_numeric($kod_stacji) || !is_numeric($rok) || !is_numeric($miesiac) || !is_numeric($dzien)) {
                                    continue;
                                }
                                if ((int)$kod_stacji === (int) $this->stationId) {
                                    $filtered[] = [
                                        "kod_stacji" => $kod_stacji,
                                        "nazwa_stacji" => $nazwa_stacji,
                                        "data" => sprintf('%04d-%02d-%02d', $rok, $miesiac, $dzien),
                                        "temperatura_powietrza" => ($temp_pow_status === '' || is_null($temp_pow_status)) ? (float) $temp_pow : null,
                                        "wilgotnosc_wzgledna" => ($wilg_status === '' || is_null($wilg_status)) ? (float) $wilg : null,
                                        "wiatr_srednia_predkosc" => ($wiatr_srednia_predkosc_status === '' || is_null($wiatr_srednia_predkosc_status)) ? (float) $wiatr_srednia_predkosc : null,
                                        "zachmurzenie" => ($zachmurzenie_status === '' || is_null($zachmurzenie_status)) ? (string) $zachmurzenie : null,
                                    ];
                                }
                            }
                        }
                    } else if ($this->aggregation === 'miesieczne') {

                        if ($this->miesieczneType === false) {
                            while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                                // Skip malformed rows or ones with mostly empty cells
                                if (!is_array($csvRow) || count(array_filter($csvRow)) < 5) continue;

                                $csvRow = array_map(function ($value) {
                                    $value = trim($value);
                                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                                    return $value;
                                }, $csvRow);

                                // Pad the row to avoid undefined offset warnings
                                [
                                    $kod_stacji,
                                    $nazwa_stacji,
                                    $rok,
                                    $miesiac,
                                    $tmax_abs,
                                    $tmax_abs_status,
                                    $tmax_avg,
                                    $tmax_avg_status,
                                    $tmin_abs,
                                    $tmin_abs_status,
                                    $tmin_avg,
                                    $tmin_avg_status,
                                    $tavg,
                                    $tavg_status,
                                    $tmng,
                                    $tmng_status,
                                    $suma_opad_mies,
                                    $suma_opad_mies_status,
                                    $opad_max,
                                    $opad_max_status,
                                    $dzien_opad_max_first,
                                    $dzien_opad_max_last,
                                    $pokrywa_max,
                                    $pokrywa_max_status,
                                    $dni_pokrywa_sniezna,
                                    $dni_opad_deszcz,
                                    $dni_opad_snieg
                                ] = array_pad($csvRow, 27, null); // pad to 27 columns

                                // Validate minimal required fields
                                if (!is_numeric($kod_stacji) || !is_numeric($rok) || !is_numeric($miesiac)) {
                                    continue;
                                }
                                if ((int)$kod_stacji === (int) $this->stationId) {
                                    $filtered[] = [
                                        "kod_stacji" => $kod_stacji,
                                        "nazwa_stacji" => $nazwa_stacji,
                                        "data" => sprintf('%04d-%02d', $rok, $miesiac),
                                        "max_max_temp_powietrza_mies" => ($tmax_avg_status === '' || is_null($tmax_avg_status)) ? (float) $tmax_avg : null,
                                        "min_min_temp_powietrza_mies" => ($tmin_avg_status === '' || is_null($tmin_avg_status)) ? (float) $tmin_avg : null,
                                        "abs_max_max_temp_powietrza_mies" => ($tmax_abs_status === '' || is_null($tmax_abs_status)) ? (float) $tmax_abs : null,
                                        "abs_min_min_temp_powietrza_mies" => ($tmin_abs_status === '' || is_null($tmin_abs_status)) ? (float) $tmin_abs : null,

                                        "mean_mean_temp_powietrza_mies" => ($tavg_status === '' || is_null($tavg_status)) ? (float) $tavg : null,
                                        "min_min_temp_gruntu_mies" => ($tmng_status === '' || is_null($tmng_status)) ? (float) $tmng : null,

                                        "sum_sum_opad_10min" => ($suma_opad_mies_status === '' || is_null($suma_opad_mies_status)) ? (float) $suma_opad_mies : null,
                                        "max_sum_opad_10min" => ($opad_max_status === '' || is_null($opad_max_status)) ? (float) $opad_max : null,
                                        "first_max_sum_opad_10min" => (($opad_max_status === '' || is_null($opad_max_status)) && (!$dzien_opad_max_first === '' || !empty($dzien_opad_max_first))) ? (string) $dzien_opad_max_first : null,
                                        "last_max_sum_opad_10min" => (($opad_max_status === '' || is_null($opad_max_status)) && (!$dzien_opad_max_last === '' || !empty($dzien_opad_max_last))) ? (string) $dzien_opad_max_last : null,
                                        "dni_deszcz_opad_10min" => ($suma_opad_mies_status === '' || is_null($suma_opad_mies_status)) ? (int) $dni_opad_deszcz : null,
                                        "dni_snieg_opad_10min" => ($suma_opad_mies_status === '' || is_null($suma_opad_mies_status)) ? (int) $dni_opad_snieg : null,
                                        "pokrywa_sniezna_wys" => ($pokrywa_max_status === '' || is_null($pokrywa_max_status)) ? (float) $pokrywa_max : null,
                                        "dni_pokrywa_sniezna_wys" => ($pokrywa_max_status === '' || is_null($pokrywa_max_status)) ? (int) $dni_pokrywa_sniezna : null,

                                    ];
                                }
                            }
                        }
                        if ($this->miesieczneType === true) {


                            while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                                // Skip malformed rows or ones with mostly empty cells
                                if (!is_array($csvRow) || count(array_filter($csvRow)) < 5) continue;

                                $csvRow = array_map(function ($value) {
                                    $value = trim($value);
                                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                                    return $value;
                                }, $csvRow);
                                // CSV column mapping based on provided header description
                                [
                                    $kod_stacji,
                                    $nazwa_stacji,
                                    $rok,
                                    $miesiac,
                                    $temp_pow,
                                    $temp_pow_status,
                                    $wilg,
                                    $wilg_status,
                                    $wiatr_srednia_predkosc,
                                    $wiatr_srednia_predkosc_status,
                                    $zachmurzenie, //Zachmurzenie ogólne [0-10 do dn.31.12.1988/oktanty od dn.01.01.1989]  5
                                    $zachmurzenie_status,
                                ] = array_pad($csvRow, 12, null); // pad in case fewer than 18 values

                                // Validate minimal required fields
                                if (!is_numeric($kod_stacji) || !is_numeric($rok) || !is_numeric($miesiac)) {
                                    continue;
                                }
                                if ((int)$kod_stacji === (int) $this->stationId) {
                                    $filtered[] = [
                                        "kod_stacji" => $kod_stacji,
                                        "nazwa_stacji" => $nazwa_stacji,
                                        "data" => sprintf('%04d-%02d', $rok, $miesiac),
                                        "temperatura_powietrza" => ($temp_pow_status === '' || is_null($temp_pow_status)) ? (float) $temp_pow : null,
                                        "wilgotnosc_wzgledna" => ($wilg_status === '' || is_null($wilg_status)) ? (float) $wilg : null,
                                        "wiatr_srednia_predkosc" => ($wiatr_srednia_predkosc_status === '' || is_null($wiatr_srednia_predkosc_status)) ? (float) $wiatr_srednia_predkosc : null,
                                        "zachmurzenie" => ($zachmurzenie_status === '' || is_null($zachmurzenie_status)) ? (string) $zachmurzenie : null,
                                    ];
                                }
                            }
                        }
                    }

                    return array_values($filtered);
                } catch (\Exception $e) {
                    $this->error = "Błąd odczytu pliku CSV: "; //$e->getMessage()
                }
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
        //add logic for csv too and konkatenuj distinct
        return $this->stations = Cache::remember('station_list_api_2', now()->addHour(1), function () {
            // Step 1: Download latest CSV file
            try {
                $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

                if (!$response->successful()) {
                    throw new Exception('Nie udało się pobrać wykazu stacji.');
                }
                $data = $response->json();
                $apistations = [];
                foreach ($data as $entry) {
                    $id = trim($entry['kod_stacji'] ?? '');
                    $name = trim($entry['nazwa_stacji'] ?? '');

                    if ($id && $name) {
                        $apistations[$id] = $name;
                    }
                }

                //csv
                $csvData = [];
                if (!Cache::has('station_list_csv')) {

                    $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get($this->stationListUrl);
                    if ($response->successful()) {

                        $utf8Content = iconv('Windows-1250', 'UTF-8//IGNORE', $response->body());
                        Storage::put($this->stationListPath, $utf8Content);
                        // Step 2: Read the file (whether downloaded or fallback)
                        $lines = explode("\n", Storage::get($this->stationListPath));
                        $csvstations = [];

                        foreach ($lines as $line) {
                            if (trim($line) === '') continue;

                            [$id, $name] = str_getcsv($line);
                            if ($id && $name) {
                                $csvstations[trim($id)] = trim($name);
                            }
                        }

                        $csvData = $csvstations;
                        Cache::put('station_list_csv', $csvData, now()->addDays(1));
                    } else {
                        $csvData = [];
                    }
                } else {
                    $csvData = Cache::get('station_list_csv', []);
                }

                // === 3. Combine both lists ===
                $combined = $apistations;

                foreach ($csvData as $id => $name) {
                    if (!isset($combined[$id])) {
                        // Only add if not already in API list
                        $combined[$id] = $name;
                    } elseif ($combined[$id] !== $name) {
                        // Optional: Log conflict if names differ
                        Log::info("Station ID conflict: $id — API: '{$combined[$id]}' vs CSV: '$name'");
                        // Or prefer API version and skip CSV
                    }
                }

                return $combined;

                //return $apistations;
            } catch (\Exception $e) {
                $this->error = 'Błąd pobierania listy stacji: '; //e.getMessage()
                return [];
            }
        });
    }

    public function render()
    {

        return view('livewire.stacja-read');
    }
}
