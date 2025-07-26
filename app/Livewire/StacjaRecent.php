<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StacjaRecent extends Component
{
    #[Url(except: '', as: 'id')]
    public $stationId = '';

    #[Url(except: '')]
    public $year;

    #[Url(except: '')]
    public $month;

    public $stations = [];
    public $error = null;

    protected $stationListPath = 'imgw/wykaz_stacji.csv';
    protected $stationListUrl = 'https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/wykaz_stacji.csv';
    protected $refreshDays = 7;

    public function mount()
    {
        $this->stations = $this->getStationsProperty();
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
