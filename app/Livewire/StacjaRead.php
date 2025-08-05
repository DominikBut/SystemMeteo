<?php

namespace App\Livewire;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StacjaRead extends Component
{

    // #[Url(except: '', keep: true)]
    // public $year = 2025;
    // #[Url(except: '', keep: true)]
    // public $month = 4;
    // public $day;

    #[Url(except: null, as: 'id')]
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
    protected string $delimiter = ',';

    public function loadData2()
    {
        ///
        $fileName = sprintf('%04d_%02d_k.zip', $this->year, $this->month);
        $csvFileName = sprintf('k_d_%02d_%04d.csv', $this->month, $this->year);

        $relativeFolder = "imgw/archived/dobowe/{$this->year}";
        $relativeZipPath = "{$relativeFolder}/{$fileName}";
        $relativeCsvPath = "{$relativeFolder}/{$csvFileName}";

        // Step 1: Download ZIP if not exists
        ////
        if (!Storage::exists($relativeCsvPath) && !Storage::exists($relativeZipPath)) {
            try {
                $response = Http::timeout(30)->get("https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/dobowe/klimat/{$this->year}/{$fileName}");
                if ($response->ok()) {
                    Storage::put($relativeZipPath, $response->body());
                    $this->info = "Pobrano.";
                } else {
                    $this->error = "Nie udało się pobrać pliku ZIP.";
                    return;
                }
            } catch (\Exception $e) {
                $this->error = "Błąd podczas pobierania: " . $e->getMessage();
                return;
            }
        } else {
            $this->info = "Istnieje zip.";
        }
        ////
        // Step 2: Extract CSV if not already extracted
        if (!Storage::exists($relativeCsvPath)) {
            $zipPath = Storage::path($relativeZipPath);
            $extractPath = Storage::path($relativeFolder);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();
                Storage::delete($relativeZipPath); // delete ZIP after extraction
                Storage::delete($relativeFolder . '/' . sprintf('k_d_t_%02d_%04d.csv', $this->month, $this->year)); //delete some additional .csv that comes from before 2024.05 months
                $this->info = "Rozpakowano.";
            } else {
                $this->error = "Nie można rozpakować pliku ZIP.";
                return;
            }
        } else {
            $this->info = "Istnieje plik.";
        }

        // Step 3: Read CSV and find matching rows
        try {
            $csvPath = Storage::path($relativeCsvPath);
            $line_of_text = [];
            $file_handle = fopen($csvPath, 'r');

            while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                if (empty($csvRow) || count($csvRow) < 5) continue;

                $csvRow = array_map(function ($value) {
                    $value = trim($value);
                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                    return $value;
                }, $csvRow);

                $stationId = (int) $csvRow[0];
                $year = (int) $csvRow[2];
                $month = (int) $csvRow[3];

                if ($stationId === (int) $this->stationId) {
                    $line_of_text[] = $csvRow;
                }
            }

            fclose($file_handle);
            $this->stationData = $line_of_text;

            if (empty($this->stationData)) {
                $this->error = "Nie znaleziono danych dla podanej stacji i daty.";
            }
        } catch (\Exception $e) {
            $this->error = "Błąd odczytu pliku CSV: " . $e->getMessage();
        }
    }



    public function mount()
    {
        $date = Carbon::yesterday();
        $this->terminoweEndDate = $date->format('Y-m-d');
        $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
        $this->doboweDate = $date->format('Y-m');
        $this->miesieczneDate = $date->format('Y');
        $this->stations = $this->getStationsProperty();
        // $this->validate();
        // $this->getStationData();
        // $this->loadData();
        // $this->calculateMinMaxStats();
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
        ];

        foreach ($fields as $field) {
            $values = collect($this->weatherData)->pluck($field)->filter(fn($val) => is_numeric($val));
            $this->minMaxStats[$field] = [
                'min' => $values->isEmpty() ? null : $values->min(),
                'max' => $values->isEmpty() ? null : $values->max(),
                'avg' => $values->isEmpty() ? null : round($values->avg(), 1),
            ];
        }
    }
    public function updatedStationId()
    {
        if ($this->validate()) {
            $this->sortBy = 'kod_stacji';
            $this->sortDirection = 'desc';
            $this->getStationData();
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->doboweDate = $date->format('Y-m');
            $this->miesieczneDate = $date->format('Y');
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
                return is_null($item[$this->sortBy]) ? PHP_INT_MAX : $item[$this->sortBy];
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }
        $this->sortedData = $data->values()->all();
        //$this->loadData(); //tu ejst problem bo odswieza wykres ? ale mozna prosto zamienic na inna zmienna i tyl?

    }
    public function loadData()
    {

        $this->weatherData = [];
        if ($this->stations) {
            switch ($this->aggregation) {
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
                $combinedData = [];
                for ($day = $day1; $day <= $day2; $day++) {
                    $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT);
                    $filePath = "imgw/collected/terminowe/{$year}/{$month}/{$year}-{$month}-{$dayFormatted}.json";

                    //to skipuje jak nie znjadzie pliku dla tego dnia?
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
                    case 'dobowe':
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $relativeFolder = "imgw/archived/dobowe/{$year}";
                        $relativeCsvPath = $relativeFolder . "/k_d_{$month}_{$year}.csv";
                        $fileName = "{$year}_{$month}_k.zip";
                        $relativeZipPath = $relativeFolder . "/{$fileName}";
                        break;
                    case 'miesieczne':
                        $year = $date->format('Y');
                        $relativeFolder = "imgw/archived/miesieczne/{$year}";
                        $relativeCsvPath = $relativeFolder . "/k_m_d_{$year}.csv";
                        $fileName = "{$year}_m_k.zip";
                        $relativeZipPath = $relativeFolder . "/{$fileName}";
                        break;
                    default:
                        $year = $date->format('Y');
                        $month = $date->format('m');
                        $relativeFolder = "imgw/archived/dobowe/{$year}";
                        $relativeCsvPath = $relativeFolder . "/k_d_{$month}_{$year}.csv";
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
                        $this->error = "Błąd podczas pobierania: " . $e->getMessage();
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
                        if ($this->aggregation === 'dobowe') {
                            Storage::delete($relativeFolder . "/k_d_t_{$month}_{$year}.csv"); //delete additional file that comes in some zips
                        } else {
                            Storage::delete($relativeFolder . "/k_m_t_{$year}.csv"); //delete additional file that comes in some zips
                        }


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

                    // $line_of_text = [];
                    // while ($csvRow = fgetcsv($file_handle, null, $this->delimiter)) {
                    //     if (empty($csvRow) || count($csvRow) < 5) continue;

                    //     $csvRow = array_map(function ($value) {
                    //         $value = trim($value);
                    //         $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value);
                    //         return $value;
                    //     }, $csvRow);
                    //     $line_of_text[] = $csvRow;
                    //     //to robi plik caly do zmiennej

                    //     // //to odczytuje wg stationid
                    //     // $stationId = (int) $csvRow[0];
                    //     // $year = (int) $csvRow[2];
                    //     // $month = (int) $csvRow[3];

                    //     // if ($stationId === (int) $this->stationId) {
                    //     //     $line_of_text[] = $csvRow;
                    //     // } //to sie zrobi potem jak zczyta z pliku stacje?
                    //     // moze switch po prostu do csv odczytu z tej listy? albo combined lista? najpierw test z tym aktulanym potem zmiana jezeli bd trzeba na csv albo z pliku aktualnego
                    // }

                    // fclose($file_handle);
                    // $this->stationData = $line_of_text;
                    $filtered = [];
                    if ($this->aggregation === 'dobowe') {
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
                                $tmng,
                                $tmng_status,
                                $suma_opadow,
                                $suma_opadow_status,
                                $rodzaj_opadu,
                                $pokrywa_sniezna,
                                $pokrywa_sniezna_status
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
                                    "max_temp_gruntu_dobowa" => is_numeric($tmax) ? (float) $tmax : null,
                                    "min_temp_gruntu_dobowa" => is_numeric($tmin) ? (float) $tmin : null,
                                    "mean_temp_gruntu_dobowa" => is_numeric($tavg) ? (float) $tavg : null,
                                    "mean_wiatr_kierunek" => null,
                                    "mean_wiatr_srednia_predkosc" => null,
                                    "max_wiatr_predkosc_maksymalna" => null,
                                    "max_wiatr_poryw_10min" => null,
                                    "mean_wilgotnosc_wzgledna" => null,
                                    "min_wilgotnosc_wzgledna" => null,
                                    "max_wilgotnosc_wzgledna" => null,
                                    "sum_opad_10min" => is_numeric($suma_opadow) ? (float) $suma_opadow : 0,
                                ];
                            }
                        }
                    } else {

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
                                    "max_max_temp_gruntu_mies" => is_numeric($tmax_abs) ? (float) $tmax_abs : null,
                                    "mean_max_temp_gruntu_mies" => is_numeric($tmax_avg) ? (float) $tmax_avg : null,
                                    "min_min_temp_gruntu_mies" => is_numeric($tmin_abs) ? (float) $tmin_abs : null,
                                    "mean_min_temp_gruntu_mies" => is_numeric($tmin_avg) ? (float) $tmin_avg : null,
                                    "mean_mean_temp_gruntu_mies" => is_numeric($tavg) ? (float) $tavg : null,
                                    "sum_sum_opad_10min" => is_numeric($suma_opad_mies) ? (float) $suma_opad_mies : 0,
                                    "max_sum_opad_10min" => is_numeric($opad_max) ? (float) $opad_max : null,
                                    "max_max_wilgotnosc_wzgledna" => null,
                                    "min_min_wilgotnosc_wzgledna" => null,
                                    "mean_max_wilgotnosc_wzgledna" => null,
                                    "mean_min_wilgotnosc_wzgledna" => null,
                                    "mean_mean_wilgotnosc_wzgledna" => null,
                                    "max_max_wiatr_predkosc_maksymalna" => null,
                                    "mean_mean_wiatr_kierunek" => null,
                                    "mean_mean_wiatr_srednia_predkosc" => null,
                                    "max_max_wiatr_poryw_10min" => null,
                                ];
                            }
                        }
                    }

                    return array_values($filtered);
                    // if (empty($this->stationData)) {
                    //     $this->error = "Nie znaleziono danych dla podanej stacji i daty.";
                    // }
                } catch (\Exception $e) {
                    $this->error = "Błąd odczytu pliku CSV: " . $e->getMessage();
                }


                // if (!Storage::exists($relativeCsvPath)) {
                //     return [];
                // }

                // $json = Storage::get($relativeCsvPath);
                // $data = json_decode($json, true);
                // if (!$data) {
                //     return [];
                // }
                // $json = null;
                // // Filter by stationId
                // $filtered = array_filter($data, function ($record) {
                //     return isset($record['kod_stacji']) && $record['kod_stacji'] == $this->stationId;
                // });

                // if (count($filtered) === 0) {
                //     // fallback: get station name from cached list by stationId key
                //     $stationName = $this->stations[$this->stationId] ?? null;
                //     if ($stationName) {
                //         // filter by nazwa_stacji (case-insensitive match)
                //         $filtered = array_filter($data, function ($record) use ($stationName) {
                //             return isset($record['nazwa_stacji'])
                //                 && strcasecmp(trim($record['nazwa_stacji']), trim($stationName)) === 0;
                //         });
                //     }
                // }

                // $data = null;
                // return array_values($filtered); // reindex
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
                $this->error = 'Błąd pobierania listy stacji: ';
                return [];
            }
        });
    }

    public function render()
    {

        return view('livewire.stacja-read');
    }
}
