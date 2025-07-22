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
            $response = Http::timeout(30)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

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

            $timestamps = array_filter(array_column($data, 'temperatura_gruntu_data'));
            $latestUTC = $timestamps ? max($timestamps) : now()->toISOString();
            $latestUTC2 = Carbon::parse($latestUTC, 'UTC')->setTimezone('Europe/Warsaw');
            $date = $latestUTC2->format('Y-m-d');

            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $folderPath = "imgw/api-data/{$year}/{$month}";
            $filename = "{$folderPath}/{$date}.json";

            $isFirstWriteToday = !Storage::exists($filename);

            $existing = Storage::exists($filename)
                ? json_decode(Storage::get($filename), true) ?? []
                : [];

            $existing = array_merge($existing, $data);
            Storage::put($filename, json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $existing = null;
            $lastJson = null;
            Cache::put('imgw_last_data', $currentJson, now()->addHours(2));
            $currentJson = null;
            $this->info("Appended raw data to {$filename}");

            // ✅ Generate summary if first time writing today
            if ($isFirstWriteToday) {
                $yesterday = Carbon::parse($date)->subDay();
                $prevDate = $yesterday->format('Y-m-d');
                $prevYear = $yesterday->format('Y');
                $prevMonth = $yesterday->format('m');
                $prevPath = "imgw/api-data/{$prevYear}/{$prevMonth}/{$prevDate}.json";

                if (Storage::exists($prevPath)) {
                    $prevData = json_decode(Storage::get($prevPath), true);
                    usort($prevData, function ($a, $b) {
                        return strcmp($a['kod_stacji'], $b['kod_stacji']);
                    });
                    $grouped = collect($prevData)->groupBy('kod_stacji');
                    $summary = [];

                    foreach ($grouped as $kod => $records) {
                        $recordsArray = $records->values(); // reset keys

                        foreach ([4, 10, 16] as $targetHour) {
                            $match = $recordsArray->last(function ($item) use ($targetHour) {
                                return !empty($item['temperatura_gruntu_data']) &&
                                    Carbon::parse($item['temperatura_gruntu_data'], 'UTC')->hour === $targetHour;
                            });

                            if (!$match) {
                                $match = $recordsArray->last(function ($item) use ($targetHour) {
                                    return !empty($item['opad_10min_data']) &&
                                        Carbon::parse($item['opad_10min_data'], 'UTC')->hour === $targetHour;
                                });
                            }

                            if (!$match) {
                                if ($targetHour === 4) {
                                    $match = $recordsArray->first();
                                } elseif ($targetHour === 10) {
                                    $match = $recordsArray->get(intval(floor($recordsArray->count() / 2)));
                                } else {
                                    $match = $recordsArray->last();
                                }
                            }

                            $summary[] = $match;
                        }
                    }
                    $grouped = null;
                    $summaryFile = "imgw/collected/terminowe/{$prevYear}/{$prevMonth}/{$prevDate}.json";
                    Storage::put($summaryFile, json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    $summary = null;
                    $this->info("Collected summary saved to {$summaryFile}");
                    Log::channel('imgw')->info("Collected summary saved to {$summaryFile}");
                } else {
                    Log::channel('imgw')->warning("Previous day file {$prevPath} not found.");
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

    // public function handle()
    // {
    //     ini_set('memory_limit', '512M');
    //     try {
    //         $response = Http::timeout(30)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

    //         if (!$response->successful()) {
    //             Log::channel('imgw')->error('Failed to fetch IMGW data: HTTP error');
    //             $this->error('Failed to fetch IMGW data: HTTP error');
    //             return 1;
    //         }

    //         $data = $response->json();

    //         // Encode current data for cache comparison
    //         $currentJson = json_encode($data, JSON_UNESCAPED_UNICODE);
    //         $lastJson = Cache::get('imgw_last_data');

    //         if ($lastJson === $currentJson) {
    //             $this->info('Data is unchanged; skipping save.');
    //             Log::channel('imgw')->info('Data unchanged; no file saved at ' . now()->format('H:i:s'));
    //             return 0;
    //         }

    //         // Build year/month path
    //         $timestamps = array_filter(array_column($data, 'temperatura_gruntu_data'));

    //         if (empty($timestamps)) {
    //             Log::channel('imgw')->warning('No temperatura_gruntu_data found in data; falling back to now().');
    //             $date = now()->format('Y-m-d');
    //         } else {
    //             $latestUTC = max($timestamps);
    //             $latestUTC2 = Carbon::parse($latestUTC, 'UTC')->setTimezone('Europe/Warsaw');
    //             $date = $latestUTC2->format('Y-m-d');
    //         }

    //         $year = substr($date, 0, 4);
    //         $month = substr($date, 5, 2);

    //         $folderPath = "imgw/api-data/{$year}/{$month}";
    //         $filename = "{$folderPath}/{$date}.json";


    //         // Load existing content or initialize empty array
    //         if (Storage::exists($filename)) {
    //             $existing = json_decode(Storage::get($filename), true);
    //             if (!is_array($existing)) {
    //                 $existing = [];
    //             }
    //         } else {
    //             $existing = [];
    //         }

    //         // Merge new data
    //         $existing = array_merge($existing, $data);

    //         // // Sort by 'kod_stacji'
    //         // usort($existing, function ($a, $b) {
    //         //     return strcmp($a['kod_stacji'], $b['kod_stacji']);
    //         // });

    //         // Save back to file
    //         Storage::put($filename, json_encode($existing, JSON_UNESCAPED_UNICODE));

    //         // Cache new data
    //         Cache::put('imgw_last_data', $currentJson, now()->addHours(2));

    //         Log::channel('imgw')->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");
    //         $this->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");
    //     } catch (\Exception $e) {
    //         Log::channel('imgw')->error('Exception: ' . $e->getMessage());
    //         $this->error('Exception: ' . $e->getMessage());
    //         return 1;
    //     }

    //     return 0;
    // }
}
