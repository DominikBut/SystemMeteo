<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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
        try {
            $response = Http::timeout(10)->get('https://danepubliczne.imgw.pl/api/data/meteo/');

            if (!$response->successful()) {
                Log::channel('imgw')->error('Failed to fetch IMGW data: HTTP error');
                $this->error('Failed to fetch IMGW data: HTTP error');
                return 1;
            }

            $data = $response->json();

            // Encode current data for cache comparison
            $currentJson = json_encode($data, JSON_UNESCAPED_UNICODE);
            $lastJson = Cache::get('imgw_last_data');

            if ($lastJson === $currentJson) {
                $this->info('Data is unchanged; skipping save.');
                Log::channel('imgw')->info('Data unchanged; no file saved at ' . now()->format('H:i:s'));
                return 0;
            }

            // Build year/month path
            $timestamps = array_filter(array_column($data, 'temperatura_gruntu_data'));

            if (empty($timestamps)) {
                Log::channel('imgw')->warning('No temperatura_gruntu_data found in data; falling back to now().');
                $date = now()->format('Y-m-d');
            } else {
                $latestUTC = max($timestamps);
                $latestUTC2 = Carbon::parse($latestUTC, 'UTC')->setTimezone('Europe/Warsaw');
                $date = $latestUTC2->format('Y-m-d');
            }

            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);

            $folderPath = "imgw/api-data/{$year}/{$month}";
            $filename = "{$folderPath}/{$date}.json";


            // Load existing content or initialize empty array
            if (Storage::exists($filename)) {
                $existing = json_decode(Storage::get($filename), true);
                if (!is_array($existing)) {
                    $existing = [];
                }
            } else {
                $existing = [];
            }

            // Merge new data
            $existing = array_merge($existing, $data);

            // // Sort by 'kod_stacji'
            // usort($existing, function ($a, $b) {
            //     return strcmp($a['kod_stacji'], $b['kod_stacji']);
            // });

            // Save back to file
            Storage::put($filename, json_encode($existing, JSON_UNESCAPED_UNICODE));

            // Cache new data
            Cache::put('imgw_last_data', $currentJson, now()->addHours(12));

            Log::channel('imgw')->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");
            $this->info("Appended raw data to {$filename} at " . now()->format('H:i:s') . " latest temp date in file: {$latestUTC} converted to {$latestUTC2}");
        } catch (\Exception $e) {
            Log::channel('imgw')->error('Exception: ' . $e->getMessage());
            $this->error('Exception: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
