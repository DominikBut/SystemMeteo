<?php

namespace App\Livewire;

use ZipArchive;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class StacjaRead extends Component
{

    public $stationId = 249190560;
    public $year = 2025;
    public $month = 4;
    // public $day;

    public $stationData = [];
    public $error = null;
    public $info = null;

    public function loadData()
    {
        $this->stationData = [];
        $this->error = null;
        $delimiter = ',';
        $fileName = sprintf('%04d_%02d_k.zip', $this->year, $this->month);
        $remoteUrl = "https://danepubliczne.imgw.pl/data/dane_pomiarowo_obserwacyjne/dane_meteorologiczne/dobowe/klimat/{$this->year}/{$fileName}";
        $localZipPath = storage_path("imgw/zips/") . $fileName;
        $csvFileName = sprintf('k_d_%02d_%04d.csv', $this->month, $this->year);
        $extractedCsvPath = storage_path("imgw/csv/") . $csvFileName;

        // Step 1: Download ZIP if not exists
        if (!file_exists($localZipPath)) {
            try {
                $response = Http::timeout(30)->get($remoteUrl);
                if ($response->ok()) {
                    file_put_contents($localZipPath, $response->body());
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

        // Step 2: Extract CSV
        if (!file_exists($extractedCsvPath)) {
            $zip = new ZipArchive();
            if ($zip->open($localZipPath) === true) {
                $zip->extractTo(storage_path('imgw/csv/'));
                $zip->close();
                $this->info = "Rozpakowano.";
            } else {
                $this->error = "Nie można rozpakować pliku ZIP.";
                return;
            }
        } else {
            $this->info = "Istnieje plik.";
        }

        // Step 3: Read CSV and find matching row
        try {
            $line_of_text = [];
            $file_handle = fopen($extractedCsvPath, 'r');
            while ($csvRow = fgetcsv($file_handle, null, $delimiter)) {
                if (empty($csvRow) || count($csvRow) < 5) continue;
                $csvRow = array_map(function ($value) {
                    $value = trim($value); // remove leading/trailing spaces
                    $value = iconv('Windows-1250', 'UTF-8//IGNORE', $value); // fix encoding
                    return $value;
                }, $csvRow);
                $stationId = (int) $csvRow[0];
                $year = (int) $csvRow[2];
                $month = (int) $csvRow[3];
                // $day = (int) $csvRow[4];

                if ($stationId === (int) $this->stationId) {
                    $line_of_text[] = $csvRow;
                    // break;
                }
            }

            $this->stationData = $line_of_text;

            fclose($file_handle);
            // dd($this->stationData);
            if (empty($this->stationData)) {
                $this->error = "Nie znaleziono danych dla podanej stacji i daty.";
            }
        } catch (\Exception $e) {
            $this->error = "Błąd odczytu pliku CSV: " . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.stacja-read');
    }
}
