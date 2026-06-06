<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LogImgwData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:log-imgw-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and log IMGW data every 10 minutes';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        ini_set('memory_limit', '512M');

        try {
            $response = Http::timeout(30)->connectTimeout(30)->retry(3, 100)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

            if (!$response->successful()) {
                Log::channel('imgw')->error('Failed to fetch IMGW data: HTTP error');
                $this->error('Failed to fetch IMGW data: HTTP error');
                return 1;
            }

            $data = $response->json();

            $currentJson = json_encode($data, JSON_UNESCAPED_UNICODE);
            $lastJson = Cache::get('imgw_last_data');

            if ($lastJson === $currentJson) {
                $this->info('Data is unchanged; skipping save.');
                Log::channel('imgw')->info('Data unchanged; no file saved at ' . now()->format('H:i:s'));
                return 0;
            }

            $timestamps = [];

            foreach ($data as $row) {
                foreach ($row as $key => $value) {
                    if (str_ends_with($key, '_data') && $value) {
                        $timestamps[] = $value;
                    }
                }
            }
            $latestUTC = $timestamps ? max($timestamps) : now()->toISOString();
            $latestUTC2 = Carbon::parse($latestUTC, 'UTC')->setTimezone('Europe/Warsaw');
            $date = $latestUTC2->format('Y-m-d');
            $timestamps = null;
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $folderPath = "imgw/api-data/{$year}/{$month}";
            $filename = "{$folderPath}/{$date}.json";

            $isFirstWriteToday = !Storage::exists($filename);

            $existing = Storage::exists($filename)
                ? json_decode(Storage::get($filename), true) ?? []
                : [];

            $existing = array_merge($existing, $data);
            $data = null;
            Storage::put($filename, json_encode($existing, JSON_UNESCAPED_UNICODE)); //| JSON_PRETTY_PRINT
            $existing = null;
            $lastJson = null;
            Cache::put('imgw_last_data', $currentJson, now()->addHours(2));
            $currentJson = null;
            Log::channel('imgw')->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");
            $this->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");

            // Generate summary if first time writing today
            if ($isFirstWriteToday) {
                $yesterday = Carbon::parse($date)->subDay();
                $prevDate = $yesterday->format('Y-m-d');
                $prevYear = $yesterday->format('Y');
                $prevMonth = $yesterday->format('m');
                $prevPath = "imgw/api-data/{$prevYear}/{$prevMonth}/{$prevDate}.json";

                if (Storage::exists($prevPath)) {
                    $prevData = json_decode(Storage::get($prevPath), true);

                    $grouped = collect($prevData)->groupBy('kod_stacji');
                    $prevData = null;
                    $summary = [];
                    $yesterday = Carbon::yesterday('Europe/Warsaw')->toDateString();
                    foreach ($grouped as $kod => $records) {
                        $recordsArray = $records->values(); // reset keys

                        // Define intervals based on target hours
                        $targets = [
                            4  => ['start' => 22, 'end' => 4],
                            10 => ['start' => 4,  'end' => 10],
                            16 => ['start' => 10, 'end' => 16],
                            21 => ['start' => 16, 'end' => 21],
                        ];

                        foreach ($targets as $targetHour => $interval) {
                            $start = $interval['start'];
                            $end = $interval['end'];

                            // // Sum all opad_10min values in the interval [start, end)
                            // $opadSum = $recordsArray->filter(function ($item) use ($start, $end) {
                            //     if (!empty($item['opad_10min_data'])) {
                            //         $hour = Carbon::parse($item['opad_10min_data'])->hour;
                            //         // Handle crossing midnight: e.g., 22–4
                            //         if ($start > $end) {
                            //             return $hour >= $start || $hour < $end;
                            //         } else {
                            //             return $hour >= $start && $hour < $end;
                            //         }
                            //     }
                            //     return false;
                            // })->reduce(function ($carry, $item) {
                            //     $val = is_numeric($item['opad_10min']) ? floatval($item['opad_10min']) : (int)0;
                            //     Log::channel('imgw')->info("Saved:  {$item['opad_10min_data']}");
                            //     return $carry + $val;
                            // }, (int)0);

                            // Filter records in interval, on correct (UTC+2) date
                            // Interval filter helper
                            $inIntervalYesterday = function ($datetimeStr) use ($start, $end, $yesterday) {
                                if (empty($datetimeStr)) return false;
                                $dt = Carbon::parse($datetimeStr, 'UTC')->addHours(2);
                                $date = $dt->toDateString();
                                $hour = Carbon::parse($datetimeStr)->hour;
                                $inRange = $start > $end
                                    ? ($hour >= $start || $hour < $end)
                                    : ($hour >= $start && $hour < $end);
                                return $inRange && $date === $yesterday;
                            };

                            // Sum opad_10min
                            $opadSum = $recordsArray->filter(
                                fn($item) =>
                                $inIntervalYesterday($item['opad_10min_data'])
                            )->sum(fn($item) => is_numeric($item['opad_10min']) ? floatval($item['opad_10min']) : 0);

                            // Averages
                            $average = function ($field, $dateField) use ($recordsArray, $inIntervalYesterday) {
                                $filtered = $recordsArray->filter(
                                    fn($item) =>
                                    isset($item[$field], $item[$dateField]) &&
                                        is_numeric($item[$field]) &&
                                        $inIntervalYesterday($item[$dateField])
                                );
                                return $filtered->isEmpty() ? null : round($filtered->avg(fn($i) => floatval($i[$field])), 1);
                            };

                            // Maximums
                            $maximum = function ($field, $dateField) use ($recordsArray, $inIntervalYesterday) {
                                $filtered = $recordsArray->filter(
                                    fn($item) =>
                                    isset($item[$field], $item[$dateField]) &&
                                        is_numeric($item[$field]) &&
                                        $inIntervalYesterday($item[$dateField])
                                );
                                return $filtered->isEmpty() ? null : $filtered->max(fn($i) => floatval($i[$field]));
                            };
                            // Compute stats
                            $avg_gruntu      = $average('temperatura_gruntu', 'temperatura_gruntu_data');
                            $avg_powietrza   = $average('temperatura_powietrza', 'temperatura_powietrza_data');
                            $avg_kierunek    = $average('wiatr_kierunek', 'wiatr_kierunek_data');
                            $avg_srednia     = $average('wiatr_srednia_predkosc', 'wiatr_srednia_predkosc_data');
                            $avg_wilgotnosc  = $average('wilgotnosc_wzgledna', 'wilgotnosc_wzgledna_data');

                            $max_poryw       = $maximum('wiatr_poryw_10min', 'wiatr_poryw_10min_data');
                            $max_maksymalna  = $maximum('wiatr_predkosc_maksymalna', 'wiatr_predkosc_maksymalna_data');

                            // For timestamps and metadata, pick a representative row near targetHour
                            $match = null;

                            if ($targetHour === 21) {
                                // Find representative record closest to the target hour
                                $match = $recordsArray->last(function ($item) use ($targetHour) {
                                    return !empty($item['temperatura_gruntu_data']) &&
                                        Carbon::parse($item['temperatura_gruntu_data'])->hour === $targetHour;
                                });

                                if (!$match) {
                                    $match = $recordsArray->last(function ($item) use ($targetHour) {
                                        return !empty($item['opad_10min_data']) &&
                                            Carbon::parse($item['opad_10min_data'])->hour === $targetHour;
                                    });
                                }
                            } else {
                                // Find representative record closest to the target hour
                                $match = $recordsArray->first(function ($item) use ($targetHour) {
                                    return !empty($item['temperatura_gruntu_data']) &&
                                        Carbon::parse($item['temperatura_gruntu_data'])->hour === $targetHour;
                                });

                                if (!$match) {
                                    $match = $recordsArray->first(function ($item) use ($targetHour) {
                                        return !empty($item['opad_10min_data']) &&
                                            Carbon::parse($item['opad_10min_data'])->hour === $targetHour;
                                    });
                                }
                            }

                            if (!$match) {
                                if ($targetHour === 4) {
                                    $match = $recordsArray->first();
                                } elseif ($targetHour === 10) {
                                    $match = $recordsArray->get(intval(floor($recordsArray->count() / 2)));
                                } elseif ($targetHour === 16) {
                                    $match = $recordsArray->get(intval(floor($recordsArray->count())) * 0.75);
                                } else {
                                    $match = $recordsArray->last();
                                }
                            }

                            $intervalRecord = $match ?? [];

                            // Attach summary values
                            $intervalRecord['opad_10min']                = $opadSum !== null ? round($opadSum, 1) : null;
                            $intervalRecord['temperatura_gruntu']        = $avg_gruntu !== null ? round($avg_gruntu, 1) : null;
                            $intervalRecord['temperatura_powietrza']     = $avg_powietrza !== null ? round($avg_powietrza, 1) : null;
                            $intervalRecord['wiatr_kierunek']            = $avg_kierunek !== null ? round($avg_kierunek, 1) : null;
                            $intervalRecord['wiatr_srednia_predkosc']    = $avg_srednia !== null ? round($avg_srednia, 1) : null;
                            $intervalRecord['wilgotnosc_wzgledna']       = $avg_wilgotnosc !== null ? round($avg_wilgotnosc, 1) : null;
                            $intervalRecord['wiatr_poryw_10min']         = $max_poryw;
                            $intervalRecord['wiatr_predkosc_maksymalna'] = $max_maksymalna;
                            $opadSum = null;
                            $avg_gruntu = null;
                            $avg_powietrza = null;
                            $avg_kierunek = null;
                            $avg_srednia = null;
                            $avg_wilgotnosc = null;
                            $max_poryw = null;
                            $max_maksymalna = null;

                            $summary[] = $intervalRecord;
                            $intervalRecord = null;
                            $match = null;
                        }
                    }
                    $grouped = null;
                    usort($summary, function ($a, $b) {
                        return strcmp($a['kod_stacji'], $b['kod_stacji']);
                    });
                    $summaryFile = "imgw/collected/terminowe/{$prevYear}/{$prevMonth}/{$prevDate}.json";
                    Storage::put($summaryFile, json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); //| JSON_PRETTY_PRINT
                    $summary = null;
                    $this->info("Saved terminowe summary to {$summaryFile}");
                    Log::channel('imgw')->info("Saved terminowe summary to {$summaryFile}");
                } else {
                    Log::channel('imgw')->warning("Previous day files not found.");
                }

                $dailySummaryPath = "imgw/collected/dobowe/{$prevYear}/{$prevYear}-{$prevMonth}.json";

                if (Storage::exists($summaryFile)) {
                    $summaryData = json_decode(Storage::get($summaryFile), true);

                    $grouped = collect($summaryData)->groupBy('kod_stacji');
                    $summaryData = null;
                    $dailySummary = [];

                    foreach ($grouped as $kod => $records) {
                        $base = $records->first(); // get name/coords
                        $filtered = collect($records);


                        $getValues = function ($field, $dataField, $isNumeric = true) use ($filtered, $prevDate) {
                            return $filtered
                                ->filter(
                                    fn($r) =>
                                    isset($r[$field], $r[$dataField]) &&
                                        is_numeric($r[$field]) &&
                                        Str::startsWith($r[$dataField], $prevDate)
                                )
                                ->pluck($field)
                                ->map(fn($v) => $isNumeric ? floatval($v) : $v);
                        };

                        $gruntu      = $getValues('temperatura_gruntu', 'temperatura_gruntu_data');
                        $powietrza   = $getValues('temperatura_powietrza', 'temperatura_powietrza_data');
                        $kierunek    = $getValues('wiatr_kierunek', 'wiatr_kierunek_data');
                        $srednia     = $getValues('wiatr_srednia_predkosc', 'wiatr_srednia_predkosc_data');
                        $maks        = $getValues('wiatr_predkosc_maksymalna', 'wiatr_predkosc_maksymalna_data');
                        $poryw       = $getValues('wiatr_poryw_10min', 'wiatr_poryw_10min_data');
                        $wilgotnosc  = $getValues('wilgotnosc_wzgledna', 'wilgotnosc_wzgledna_data');
                        $opad        = $getValues('opad_10min', 'opad_10min_data');

                        $dailySummary[] = [
                            'kod_stacji' => (string) ($base['kod_stacji'] ?? ''),
                            'nazwa_stacji' => (string) ($base['nazwa_stacji'] ?? ''),
                            'lon' => (string) ($base['lon'] ?? ''),
                            'lat' => (string) ($base['lat'] ?? ''),
                            'data' => (string) $prevDate,

                            'max_temp_gruntu_dobowa' => $gruntu->isNotEmpty() ? round($gruntu->max(), 1) : null,
                            'min_temp_gruntu_dobowa' => $gruntu->isNotEmpty() ? round($gruntu->min(), 1) : null,
                            'mean_temp_gruntu_dobowa' => $gruntu->isNotEmpty() ?  round($gruntu->avg(), 1) : null,

                            'max_temp_powietrza_dobowa' => $powietrza->isNotEmpty() ? round($powietrza->max(), 1) : null,
                            'min_temp_powietrza_dobowa' => $powietrza->isNotEmpty() ? round($powietrza->min(), 1) : null,
                            'mean_temp_powietrza_dobowa' => $powietrza->isNotEmpty() ?  round($powietrza->avg(), 1) : null,

                            'mean_wiatr_kierunek' => $kierunek->isNotEmpty() ? round($kierunek->avg(), 1) : null,
                            'mean_wiatr_srednia_predkosc' => $srednia->isNotEmpty() ?  round($srednia->avg(), 1) : null,
                            'max_wiatr_predkosc_maksymalna' => $maks->isNotEmpty() ?  round($maks->max(), 1) : null,
                            'max_wiatr_poryw_10min' => $poryw->isNotEmpty() ? round($poryw->max(), 1) : null,

                            'mean_wilgotnosc_wzgledna' => $wilgotnosc->isNotEmpty() ? round($wilgotnosc->avg(), 1) : null,
                            'min_wilgotnosc_wzgledna' => $wilgotnosc->isNotEmpty() ? round($wilgotnosc->min(), 1) : null,
                            'max_wilgotnosc_wzgledna' => $wilgotnosc->isNotEmpty() ? round($wilgotnosc->max(), 1) : null,

                            'sum_opad_10min' => $opad->isNotEmpty() ?  $opad->sum() : null,
                        ];
                    }
                    $grouped = null;
                    // Load previous if exists and merge
                    $existing = Storage::exists($dailySummaryPath)
                        ? json_decode(Storage::get($dailySummaryPath), true)
                        : [];

                    $merged = array_merge($existing, $dailySummary);
                    $existing = null;
                    $dailySummary = null;
                    usort($merged, function ($a, $b) {
                        return strcmp($a['kod_stacji'], $b['kod_stacji']);
                    });
                    Storage::put($dailySummaryPath, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)); //
                    Log::channel('imgw')->info("Updated dobowe summary to {$dailySummaryPath}");
                    $merged = null;

                    $monthlySummaryPath = "imgw/collected/miesieczne/{$prevYear}.json";
                    if (Storage::exists($dailySummaryPath)) {
                        // Place this after your daily summary block inside `handle()`

                        $summaryData = json_decode(Storage::get($dailySummaryPath), true);

                        $grouped = collect($summaryData)->groupBy('kod_stacji');
                        $summaryData = null;
                        $monthlySummary = [];

                        foreach ($grouped as $kod => $records) {
                            $base = collect($records)->first();

                            $fieldStats = fn($values) => [
                                'min' => $values->isNotEmpty() ? round($values->min(), 1) : null,
                                'max' => $values->isNotEmpty() ? round($values->max(), 1) : null,
                                'mean' => $values->isNotEmpty() ? round($values->avg(), 1) : null,
                                'sum' => $values->isNotEmpty() ? round($values->sum(), 1) : null,
                            ];

                            // Directly collect values without filtering
                            $pluck = fn($key) => collect($records)->pluck($key)->filter(fn($v) => is_numeric($v))->map(fn($v) => (float) $v);


                            $gruntu_max  = $fieldStats($pluck('max_temp_gruntu_dobowa'));
                            $gruntu_min  = $fieldStats($pluck('min_temp_gruntu_dobowa'));
                            $gruntu_mean = $fieldStats($pluck('mean_temp_gruntu_dobowa'));

                            $powietrza_max  = $fieldStats($pluck('max_temp_powietrza_dobowa'));
                            $powietrza_min  = $fieldStats($pluck('min_temp_powietrza_dobowa'));
                            $powietrza_mean = $fieldStats($pluck('mean_temp_powietrza_dobowa'));

                            $kierunek = $fieldStats($pluck('mean_wiatr_kierunek'));
                            $srednia  = $fieldStats($pluck('mean_wiatr_srednia_predkosc'));
                            $maks     = $fieldStats($pluck('max_wiatr_predkosc_maksymalna'));
                            $poryw    = $fieldStats($pluck('max_wiatr_poryw_10min'));

                            $wilg_min  = $fieldStats($pluck('min_wilgotnosc_wzgledna'));
                            $wilg_max  = $fieldStats($pluck('max_wilgotnosc_wzgledna'));
                            $wilg_mean = $fieldStats($pluck('mean_wilgotnosc_wzgledna'));

                            $opad = $fieldStats($pluck('sum_opad_10min'));

                            $monthlySummary[] = [
                                'kod_stacji' => (string) ($base['kod_stacji'] ?? ''),
                                'nazwa_stacji' => (string) ($base['nazwa_stacji'] ?? ''),
                                'lon' => (string) ($base['lon'] ?? ''),
                                'lat' => (string) ($base['lat'] ?? ''),
                                'data' => "{$prevYear}-{$prevMonth}",

                                'max_max_temp_gruntu_mies' => $gruntu_max['max'] !== null ?  $gruntu_max['max'] : null,
                                'mean_max_temp_gruntu_mies' => $gruntu_max['mean'] !== null ? $gruntu_max['mean'] : null,
                                'min_min_temp_gruntu_mies' => $gruntu_min['min'] !== null ?  $gruntu_min['min'] : null,
                                'mean_min_temp_gruntu_mies' => $gruntu_min['mean'] !== null ?  $gruntu_min['mean'] : null,
                                'mean_mean_temp_gruntu_mies' => $gruntu_mean['mean'] !== null ?  $gruntu_mean['mean'] : null,

                                'max_max_temp_powietrza_mies' => $powietrza_max['max'] !== null ?  $powietrza_max['max'] : null,
                                'mean_max_temp_powietrza_mies' => $powietrza_max['mean'] !== null ? $powietrza_max['mean'] : null,
                                'min_min_temp_powietrza_mies' => $powietrza_min['min'] !== null ?  $powietrza_min['min'] : null,
                                'mean_min_temp_powietrza_mies' => $powietrza_min['mean'] !== null ?  $powietrza_min['mean'] : null,
                                'mean_mean_temp_powietrza_mies' => $powietrza_mean['mean'] !== null ?  $powietrza_mean['mean'] : null,

                                'mean_mean_wiatr_kierunek' => $kierunek['mean'] !== null ? $kierunek['mean'] : null,
                                'mean_mean_wiatr_srednia_predkosc' => $srednia['mean'] !== null ?  $srednia['mean'] : null,
                                'max_max_wiatr_predkosc_maksymalna' => $maks['max'] !== null ?  $maks['max'] : null,
                                'max_max_wiatr_poryw_10min' => $poryw['max'] !== null ? $poryw['max'] : null,

                                'min_min_wilgotnosc_wzgledna' => $wilg_min['min'] !== null ?  $wilg_min['min'] : null,
                                'mean_min_wilgotnosc_wzgledna' => $wilg_min['mean'] !== null ?  $wilg_min['mean'] : null,
                                'max_max_wilgotnosc_wzgledna' => $wilg_max['max'] !== null ?  $wilg_max['max'] : null,
                                'mean_max_wilgotnosc_wzgledna' => $wilg_max['mean'] !== null ?  $wilg_max['mean'] : null,
                                'mean_mean_wilgotnosc_wzgledna' => $wilg_mean['mean'] !== null ?  $wilg_mean['mean'] : null,

                                'max_sum_opad_10min' => $opad['max'] !== null ?  $opad['max'] : null,
                                'sum_sum_opad_10min' => $opad['sum'] !== null ?  $opad['sum'] : null,
                            ];
                        }
                        $grouped = null;
                        // Load previous if exists and merge
                        $existing = Storage::exists($monthlySummaryPath)
                            ? json_decode(Storage::get($monthlySummaryPath), true)
                            : [];

                        // Index existing data by "kod_stacji-data"
                        $indexed = collect($existing)
                            ->keyBy(fn($item) => $item['kod_stacji'] . '-' . $item['data'])
                            ->toArray();

                        // Replace or add new summaries
                        foreach ($monthlySummary as $record) {
                            $key = $record['kod_stacji'] . '-' . $record['data'];
                            $indexed[$key] = $record;
                        }
                        // Re-index by values and sort
                        $merged = array_values($indexed);
                        $indexed = null;
                        usort($merged, fn($a, $b) => strcmp($a['kod_stacji'], $b['kod_stacji']));
                        $existing = null;
                        $monthlySummary = null;
                        // Sort and save
                        usort($merged, function ($a, $b) {
                            return strcmp($a['kod_stacji'], $b['kod_stacji']);
                        });
                        Storage::put($monthlySummaryPath, json_encode($merged, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                        Log::channel('imgw')->info("Updated miesieczne summary to {$monthlySummaryPath}");
                        $merged = null;
                    }
                }
                // Cleanup old files (>7 days)
                $sevenDaysAgo = Carbon::now()->subDays(7)->startOfDay();

                $allFiles = Storage::allFiles('imgw/api-data');

                foreach ($allFiles as $filePath) {
                    if (Str::endsWith($filePath, '.json')) {
                        // Extract date from file path: imgw/api-data/YYYY/MM/YYYY-MM-DD.json
                        $parts = explode('/', $filePath);
                        $fileName = end($parts);

                        if (preg_match('/(\d{4})-(\d{2})-(\d{2})\.json/', $fileName, $matches)) {
                            $fileDate = Carbon::createFromFormat('Y-m-d', "{$matches[1]}-{$matches[2]}-{$matches[3]}");
                            if ($fileDate->lessThan($sevenDaysAgo)) {
                                Storage::delete($filePath);
                                Log::channel('imgw')->info("Deleted old file: {$filePath}");
                            }
                        }
                    }
                }
                $allFiles = null;
            }

            return 0;
        } catch (\Exception $e) {
            Log::channel('imgw')->error('Exception: ' . $e->getMessage());
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }
    }
}
