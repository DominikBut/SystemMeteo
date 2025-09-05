<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Data;
use Livewire\Component;
use App\Models\Stations;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StacjaCommunity extends Component
{

    #[Url(except: null, as: 'id', history: true)]
    #[Validate('numeric', message: 'Zły format id!')]
    public $stationId;
    public $stations = [];
    public $stationData;
    public string $askTime;

    public $weatherData;
    public  $minMaxStats = [];

    #[Validate('string', message: 'Zły format zmiennej!')]
    public string $sortBy = 'id';

    #[Validate('string|in:asc,desc', message: 'Zły format sortowania!')]
    public string $sortDirection = 'desc'; // or 'desc'

    public $sortedData;

    public bool $stop = false;

    public $error = null;
    public $info;

    #[Validate('string|in:today,yesterday,7days,interval', message: 'Zły format wyboru!')]
    public string $dateOption = 'today'; // 'dzis', 'wczoraj', '7 dni'

    public $stationInfo = null;
    public $terminoweStartDate;
    public $terminoweEndDate;
    public $station_name;


    public function mount()
    {

        $date = Carbon::today();
        $this->terminoweEndDate = $date->format('Y-m-d');
        $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');

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

    public function calculateMinMaxStats()
    {
        $fields = [
            //terminowe
            'temp_air',
            'humidity',
            'wind_speed',
            'rain_10min',

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
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->error = null;
            $this->sortBy = 'id';
            $this->sortDirection = 'desc';
            $this->stationData = null;
            $this->sortedData = $this->weatherData;
            $this->getStationData();
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');
            $this->error = null;
            $this->sortBy = 'id';
            $this->sortDirection = 'desc';
            $this->stationData = null;
            $this->sortedData = $this->weatherData;
        }
    }
    public function getStationData()
    {

        $station_tmp = Stations::where('id', $this->stationId)->first();
        $user = Auth::id();
        // dd($station_tmp, $user);
        if ($station_tmp) {
            if ($station_tmp->public == true || $station_tmp->user_id == $user) {
                $this->station_name = $station_tmp->name;
                $response = Data::where('station_id', $this->stationId)
                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at', 'desc')
                    ->first();
                $this->stationInfo = $station_tmp;
                $this->stationData = $response;
                $this->askTime = Carbon::now()->format('Y-m-d H:i:s');
            } else {
                $this->stationInfo = null;
                $this->station_name = null;
                $this->stationData = null;
                $this->error = 'Stacja nie jest publiczna. ';
            }
        } else {
            $this->stationInfo = null;
            $this->station_name = null;
            $this->stationData = null;
            $this->error = 'Nie udało się pobrać danych dla tej stacji. ';
        }
    }

    public function sortWetherData()
    {
        $this->sortedData = collect($this->weatherData);

        if ($this->sortBy !== '') {
            $data = $this->sortedData->sortBy(function ($item) {
                $value = $item[$this->sortBy] ?? null;

                if (is_null($value)) {
                    return $this->sortDirection === 'desc' ? -PHP_INT_MAX : PHP_INT_MAX;
                }

                return $value;
            }, SORT_REGULAR, $this->sortDirection === 'desc');

            $this->sortedData = $data->values()->all();
        }
    }
    public function loadData()
    {
        $this->weatherData = [];
        if ($this->stations) {
            $this->load30MinData();
            $this->dispatch('weatherDataUpdated', [$this->weatherData, $this->dateOption, $this->station_name], [
                'terminoweStartDate' => $this->terminoweStartDate,
                'terminoweEndDate' => $this->terminoweEndDate,
            ]);

            $this->calculateMinMaxStats();
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'id';
        }
    }
    public function load30MinData()
    {
        switch ($this->dateOption) {
            case 'today':
                $date = Carbon::today();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'id';
                break;
            case 'yesterday':
                $date = Carbon::yesterday();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'id';
                break;
            case '7days':
                $endDate = Carbon::yesterday();
                $startDate = $endDate->copy()->subDays(6);
                $allData = collect();
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $dayData = $this->loadDataForDate($date);
                    if ($dayData->isNotEmpty()) {
                        $allData = $allData->merge($dayData);
                    }
                }
                $this->weatherData = $allData;
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'id';
                break;
            case 'interval':
                $endDate = Carbon::parse($this->terminoweEndDate);
                $startDate = Carbon::parse($this->terminoweStartDate);
                $allData = collect();
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    $dayData = $this->loadDataForDate($date);
                    if ($dayData->isNotEmpty()) {
                        $allData = $allData->merge($dayData);
                    }
                }
                $this->weatherData = $allData;
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'id';
                break;
            default:
                $date = Carbon::today();
                $this->weatherData = $this->loadDataForDate($date);
                $this->sortedData = $this->weatherData;
                $this->sortBy = 'id';
                break;
        }
    }

    public function loadDataForDate(Carbon $date)
    {
        $user = Auth::id();
        if ($this->validate()) {
            if ($this->stop == false) {
                if (!is_null($this->stationInfo)) {
                    $response = Data::where('station_id', $this->stationId)
                        ->whereDate('created_at', $date)
                        ->orderBy('created_at', 'asc')
                        ->get();
                    return $response->values(); // reindex
                } else {
                    $respone = null;
                }
            }
        } else {
            $date = Carbon::yesterday();
            $this->terminoweEndDate = $date->format('Y-m-d');
            $this->terminoweStartDate = $date->firstOfMonth()->format('Y-m-d');

            $this->sortDirection = 'desc';
            $this->sortedData = $this->weatherData;
            $this->sortBy = 'id';
        }
    }


    public function getStationsProperty(): array
    {
        //done? wrzuca all public and all usera do listy

        $user = Auth::id();
        if ($user) {
            $stations = Stations::where(function ($query) use ($user) {
                $query->where('public', true)
                    ->orWhere('user_id', $user);
            })->orderBy('name', 'asc')
                ->select('name', 'id', 'user_id')
                ->get();
        } else {
            $stations = Stations::where('public', true)->orderBy('name', 'asc')->select('name', 'id', 'user_id')->get();
        }

        $tmp_stations = [];
        $owned = [];
        foreach ($stations as $entry) {
            $id = $entry->id;
            $name = $entry->name;

            if ($id && $name) {
                $tmp_stations[$id] = $name;
                $owned[$entry->id] = $entry->user_id === $user  ?  true : false; // extra array
            }
        }
        return [
            'names' => $tmp_stations,
            'owned' => $owned,
        ];
        // return $tmp_stations;
    }
    public function render()
    {
        return view('livewire.stacja-community');
    }
}
