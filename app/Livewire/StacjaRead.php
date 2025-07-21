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
        $this->info = null;
        $delimiter = ',';

        $fileName = sprintf('%04d_%02d_k.zip', $this->year, $this->month);
        $csvFileName = sprintf('k_d_%02d_%04d.csv', $this->month, $this->year);

        $relativeFolder = "imgw/archived/dobowe/{$this->year}";
        $relativeZipPath = "{$relativeFolder}/{$fileName}";
        $relativeCsvPath = "{$relativeFolder}/{$csvFileName}";

        // Step 1: Download ZIP if not exists

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

            while ($csvRow = fgetcsv($file_handle, null, $delimiter)) {
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

    public function render()
    {
        return view('livewire.stacja-read');
    }
}
