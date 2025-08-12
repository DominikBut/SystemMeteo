<?php

namespace App\Livewire;

use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;
use App\Models\Stations;
use Filament\Tables\Table;
use Dotswan\MapPicker\Fields\Map;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PersonalStations extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    // public $stations = null;
    // public $stationId = null;
    // public function mount()
    // {
    //     $this->stations = Stations::where('user_id', Auth::id())->get() ?? null;
    //     $this->stationId = Stations::where('user_id', Auth::id())->orderBy('created_at', 'desc')->first()->id ?? null;
    // }
    public function table(Table $table): Table
    {
        return $table
            ->heading('Lista stacji')
            ->description('Zarządzaj swoimi stacjami.')
            ->query(Stations::query()->where('user_id', Auth::id()))
            ->poll('300s')
            ->emptyStateHeading('Brak utworzonych stacji.')
            ->emptyStateDescription('Spróbuj dodać nową stację przyciskiem powyżej.')->emptyStateIcon('heroicon-o-bookmark-slash')
            ->striped()
            ->columns([
                TextColumn::make('index')->label('Lp.')
                    ->rowIndex(),
                TextColumn::make('id')->label('ID')->url(fn($record) => route('stacja_recent', ['id' => $record->id]))
                    ->openUrlInNewTab()->weight(FontWeight::Medium)->color(Color::Blue) // optional if you want it to open in a new tab
                    ->extraAttributes(['class' => 'text-blue-500 underline cursor-pointer']),
                TextColumn::make('name')->sortable()->searchable()->color('gray')->label('Nazwa stacji')->limit(30)->icon('heroicon-m-wifi')->weight(FontWeight::Medium),
                ImageColumn::make('photo')->label('Zdjęcie'),
                TextColumn::make('created_at')->sortable()->searchable()->color('gray')->label('Utworzono')->wrapHeader(),
                IconColumn::make('active')->sortable()->searchable()->label('Archiwizacja')->boolean()->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')->alignment(Alignment::Center),
                IconColumn::make('public')->sortable()->searchable()->label('Publiczna')->boolean()->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')->alignment(Alignment::Center),
                CheckboxColumn::make('temperature')->sortable()->label('Temperatura')->disabled(),
                CheckboxColumn::make('humidity')->sortable()->label('Wilgotność')->disabled(),
                CheckboxColumn::make('wind')->sortable()->label('Wiatr')->disabled(),
                CheckboxColumn::make('rain')->sortable()->label('Deszcz')->disabled(),
            ])

            ->headerActions([
                CreateAction::make()->label('Utwórz stację')->icon('heroicon-m-wifi')
                    ->model(Stations::class)
                    ->modalHeading('Dodaj nową stację')
                    ->modalDescription('Wypełnij poniższy formularz')
                    ->modalSubmitActionLabel('Utwórz nową stację')
                    ->form([
                        Section::make()
                            ->schema([
                                TextInput::make('name')->required()->columnSpanFull()->minLength(1)->prefixIcon('heroicon-m-wifi')->unique()->maxLength(50)->label('Wyświetlana nazwa stacji')->helperText('Max 50 znaków')->helperText('Nazwa będzie wyświetlana na liście stacji.'),
                                Map::make('location')->required()
                                    ->label('Lokalizacja stacji')
                                    ->columnSpanFull()

                                    ->defaultLocation(latitude: 52.2297, longitude: 21.0122)
                                    ->draggable(true)
                                    ->clickable(true) // click to move marker
                                    ->zoom(10)->showMarker(false)
                                    ->minZoom(0)
                                    ->maxZoom(28)->showZoomControl(true)
                                    ->showMarker(true)->showZoomControl(true)->liveLocation(false)->geoMan(false)
                                    ->showMyLocationButton(false)
                                    ->markerColor("#3b82f6")->extraStyles([
                                        'min-height: 15vh',
                                    ])
                                    ->markerIconSize([36, 36])
                                    ->markerIconClassName('my-marker-class')
                                    ->markerIconAnchor([18, 36])->drawMarker(false)
                                    ->afterStateUpdated(function (Set $set, ?array $state): void {
                                        $set('lat', $state['lat']);
                                        $set('lon', $state['lng']);
                                    })
                                    ->tilesUrl('https://tile.openstreetmap.de/{z}/{x}/{y}.png'),
                                Hidden::make('lat')->default(52.2297),
                                // ->hiddenLabel()
                                // ->hidden(),
                                Hidden::make('lon')->default(21.0122),
                                // ->hiddenLabel()
                                // ->hidden(),
                                Select::make('voivodeship')->native(false)->searchable()->prefix('Województwo: ')->required()->columnSpan(2)->default('wielkopolskie')->hiddenLabel()
                                    ->options([
                                        'wielkopolskie' => 'Wielkopolskie',
                                        'dolnoslaskie' => 'Dolnośląskie',
                                        'kujawsko_pomorskie' => 'Kujawsko-pomorskie',
                                        'lodzkie' => 'Łódzkie',
                                        'lubelskie' => 'Lubelskie',
                                        'lubuskie' => 'Lubuskie',
                                        'malopolskie' => 'Małopolskie',
                                        'mazowieckie' => 'Mazowieckie',
                                        'opolskie' => 'Opolskie',
                                        'podkarpackie' => 'Podkarpackie',
                                        'podlaskie' => 'Podlaskie',
                                        'pomorskie' => 'Pomorskie',
                                        'slaskie' => 'Śląskie',
                                        'swietokrzyskie' => 'Świętokrzyskie',
                                        'warminsko_mazurskie' => 'Warmińsko-mazurskie',
                                        'zachodniopomorskie' => 'Zachodniopomorskie',
                                    ])
                                    ->live(),

                                Select::make('district')->native(false)->searchable()->prefix('Powiat, miasto:')->required()->columnSpan(2)->default('poznanski')->hiddenLabel()
                                    ->options(fn(Get $get): array => match ($get('voivodeship')) {
                                        'dolnoslaskie' => [
                                            'boleslawiecki' => 'bolesławiecki',
                                            'dzierzoniowski' => 'dzierżoniowski',
                                            'glogowski' => 'głogowski',
                                            'jeleniogorski' => 'jeleniogórski',
                                            'kamiennogorski' => 'kamiennogórski',
                                            'klodzki' => 'kłodzki',
                                            'legnicki' => 'legnicki',
                                            'lubanski' => 'lubański',
                                            'lwowecki' => 'lwówecki',
                                            'milicki' => 'milicki',
                                            'olesnicki' => 'oleśnicki',
                                            'olawski' => 'oławski',
                                            'polkowicki' => 'polkowicki',
                                            'strzelienski' => 'strzeliński',
                                            'swidnicki' => 'świdnicki',
                                            'trzebnicki' => 'trzebnicki',
                                            'walbrzyski' => 'wałbrzyski',
                                            'wolowski' => 'wołowski',
                                            'wroclawski' => 'wrocławski',
                                            'zgorzelecki' => 'zgorzelecki',
                                            'zlotoryjski' => 'złotoryjski',
                                            'm_jelenia_gora' => 'Jelenia Góra',
                                            'm_legnica' => 'Legnica',
                                            'm_walbrzych' => 'Wałbrzych',
                                            'm_wroclaw' => 'Wrocław',
                                        ],
                                        'kujawsko_pomorskie' => [
                                            'brodnicki' => 'brodnicki',
                                            'bydgoski' => 'bydgoski',
                                            'chelmno' => 'chełmiński',
                                            'golubsko_dobrzynski' => 'golubsko-dobrzyński',
                                            'grudzadzki' => 'grudziądzki',
                                            'inowroclawski' => 'inowrocławski',
                                            'lipnowski' => 'lipnowski',
                                            'mogilenski' => 'mogileński',
                                            'nakielski' => 'nakielski',
                                            'radziejowski' => 'radziejowski',
                                            'rypinski' => 'rypiński',
                                            'sepolenski' => 'sępoleński',
                                            'swiecki' => 'świecki',
                                            'torunski' => 'toruński',
                                            'tucholski' => 'tucholski',
                                            'wabrzeski' => 'wąbrzeski',
                                            'wegorzewski' => 'węgorzewski',
                                            'zninski' => 'żniński',
                                            'm_bydgoszcz' => 'Bydgoszcz',
                                            'm_grudziadz' => 'Grudziądz',
                                            'm_torun' => 'Toruń',
                                        ],
                                        'lubelskie' => [
                                            'bialskopodlaski' => 'bialski',
                                            'bilgorajski' => 'biłgorajski',
                                            'chelmski' => 'chełmski',
                                            'hrubieszowski' => 'hrubieszowski',
                                            'janowski' => 'janowski',
                                            'krasnostawski' => 'krasnostawski',
                                            'krasnicki' => 'kraśnicki',
                                            'lubartowski' => 'lubartowski',
                                            'lubelski' => 'lubelski',
                                            'lukowski' => 'łukowski',
                                            'parczewski' => 'parczewski',
                                            'pulawski' => 'puławski',
                                            'radzynski' => 'radzyński',
                                            'rycki' => 'rycki',
                                            'tomaszowski' => 'tomaszowski',
                                            'wlodawski' => 'włodawski',
                                            'zamosc' => 'zamojski',
                                            'm_biala_podlaska' => 'Biała Podlaska',
                                            'm_chelm' => 'Chełm',
                                            'm_lublin' => 'Lublin',
                                            'm_zamosc' => 'Zamość',
                                        ],
                                        'lubuskie' => [
                                            'gorzowski' => 'gorzowski',
                                            'krosnienski' => 'krośnieński',
                                            'miedzyrzecki' => 'międzyrzecki',
                                            'nowosolski' => 'nowosolski',
                                            'sulecinski' => 'sulęciński',
                                            'slubicki' => 'słubicki',
                                            'zielonogorski' => 'zielonogórski',
                                            'zaryski' => 'żarski',
                                            'm_gorzow_wlkp' => 'Gorzów Wielkopolski',
                                            'm_zielona_gora' => 'Zielona Góra',
                                        ],
                                        'lodzkie' => [
                                            'belchatowski' => 'bełchatowski',
                                            'brzezinski' => 'brzeziński',
                                            'kutnowski' => 'kutnowski',
                                            'lowicki' => 'łowicki',
                                            'lodzki_wschodni' => 'łódzki wschodni',
                                            'lodzki_zachodni' => 'łódzki zachodni',
                                            'piotrkowski' => 'piotrkowski',
                                            'pabianicki' => 'pabianicki',
                                            'pajeczanski' => 'pajęczański',
                                            'radomszczanski' => 'radomszczański',
                                            'sieradzki' => 'sieradzki',
                                            'skierniewicki' => 'skierniewicki',
                                            'tomaszowski' => 'tomaszowski',
                                            'wielunski' => 'wieluński',
                                            'wieruszowski' => 'wieruszowski',
                                            'zdunskowolski' => 'zduńskowolski',
                                            'zgierski' => 'zgierski',
                                            'm_lodz' => 'Łódź',
                                            'm_piotrkow_trybunalski' => 'Piotrków Trybunalski',
                                            'm_skierniewice' => 'Skierniewice',
                                        ],
                                        'malopolskie' => [
                                            'bochenski' => 'bocheński',
                                            'brzeski' => 'brzeski',
                                            'chrzanowski' => 'chrzanowski',
                                            'dabrowski' => 'dąbrowski',
                                            'gorlicki' => 'gorlicki',
                                            'krakowski' => 'krakowski',
                                            'limanowski' => 'limanowski',
                                            'miechowski' => 'miechowski',
                                            'nowosadecki' => 'nowosądecki',
                                            'nowotarski' => 'nowotarski',
                                            'olkuski' => 'olkuski',
                                            'oswiecimski' => 'oświęcimski',
                                            'proszowicki' => 'proszowicki',
                                            'suski' => 'suski',
                                            'tarnowski' => 'tarnowski',
                                            'wadowicki' => 'wadowicki',
                                            'wielicki' => 'wielicki',
                                            'm_bozec' => 'Bożec',
                                            'm_kielce' => 'Kielce',
                                            'm_krakow' => 'Kraków',
                                            'm_nowy_sacz' => 'Nowy Sącz',
                                            'm_tarnow' => 'Tarnów',
                                        ],
                                        'mazowieckie' => [
                                            'bielski' => 'bielski',
                                            'ciechanowski' => 'ciechanowski',
                                            'garwoliński' => 'garwoliński',
                                            'gostyninski' => 'gostyniński',
                                            'grodziski' => 'grodziski',
                                            'grójecki' => 'grójecki',
                                            'kozanski' => 'kozienicki',
                                            'legionowski' => 'legionowski',
                                            'lipski' => 'lipski',
                                            'lowicki' => 'łowicki',
                                            'makowski' => 'makowski',
                                            'miedzyrzec_podlaski' => 'międzyrzecki',
                                            'ostrolecki' => 'ostrołęcki',
                                            'ostrowski' => 'ostrowski',
                                            'ostrowiecki' => 'ostrowiecki',
                                            'piaseczynski' => 'piaseczyński',
                                            'płocki' => 'płocki',
                                            'pruszkowski' => 'pruszkowski',
                                            'pruszkow' => 'pruszków',
                                            'radomski' => 'radomski',
                                            'siedlecki' => 'siedlecki',
                                            'sierpecki' => 'sierpecki',
                                            'sochaczewski' => 'sochaczewski',
                                            'szydłowiecki' => 'szydłowiecki',
                                            'warszawski_zachodni' => 'warszawski zachodni',
                                            'wołomiński' => 'wołomiński',
                                            'węgrów' => 'węgrowski',
                                            'żyrardowski' => 'żyrardowski',
                                            'm_ostroleka' => 'Ostrołęka',
                                            'm_plock' => 'Płock',
                                            'm_radom' => 'Radom',
                                            'm_siedlce' => 'Siedlce',
                                            'm_warszawa' => 'Warszawa',
                                        ],
                                        'opolskie' => [
                                            'brzeski' => 'brzeski',
                                            'krapkowicki' => 'krapkowicki',
                                            'namyslowski' => 'namysłowski',
                                            'nyski' => 'nyski',
                                            'opolki' => 'opolski',
                                            'prudnicki' => 'prudnicki',
                                            'strzelecki' => 'strzelecki',
                                            'm_opole' => 'Opole',
                                        ],
                                        'podkarpackie' => [
                                            'bieszczadzki' => 'bieszczadzki',
                                            'brzozowski' => 'brzozowski',
                                            'dębicki' => 'dębicki',
                                            'jaroslawski' => 'jarosławski',
                                            'jasielski' => 'jasielski',
                                            'kolbuszowski' => 'kolbuszowski',
                                            'krosnienski' => 'krośnieński',
                                            'lubaczowski' => 'lubaczowski',
                                            'leski' => 'leski',
                                            'mielecki' => 'mielecki',
                                            'przemyski' => 'przemyski',
                                            'rozwadowski' => 'ropczycko-sędziszowski',
                                            'rzeszowski' => 'rzeszowski',
                                            'sanocki' => 'sanocki',
                                            'stalowowolski' => 'stalowowolski',
                                            'strzyżowski' => 'strzyżowski',
                                            'tarnobrzeski' => 'tarnobrzeski',
                                            'm_krosno' => 'Krosno',
                                            'm_przemysl' => 'Przemyśl',
                                            'm_rzeszow' => 'Rzeszów',
                                        ],
                                        'podlaskie' => [
                                            'bialostocki' => 'białostocki',
                                            'bielski' => 'bielski',
                                            'grajewski' => 'grajewski',
                                            'hajnowski' => 'hajnowski',
                                            'kolninski' => 'kolneński',
                                            'lomzynski' => 'łomżyński',
                                            'moniecki' => 'moniecki',
                                            'sejneński' => 'sejneński',
                                            'siemiatycki' => 'siemiatycki',
                                            'sokolski' => 'sokólski',
                                            'wysokomazowiecki' => 'wysokomazowiecki',
                                            'zambrowski' => 'zambrowski',
                                            'm_bialystok' => 'Białystok',
                                            'm_lomza' => 'Łomża',
                                            'm_suwalki' => 'Suwałki',
                                        ],
                                        'pomorskie' => [
                                            'bytowski' => 'bytowski',
                                            'czluchowski' => 'człuchowski',
                                            'gdanski' => 'gdański',
                                            'kartuski' => 'kartuski',
                                            'kwidzyński' => 'kwidzyński',
                                            'leborki' => 'lęborski',
                                            'malborski' => 'malborski',
                                            'nowodworski' => 'nowodworski',
                                            'pucki' => 'pucki',
                                            'slupski' => 'słupski',
                                            'starogardzki' => 'starogardzki',
                                            'sztumski' => 'sztumski',
                                            'tczewski' => 'tczewski',
                                            'wejherowski' => 'wejherowski',
                                            'm_gdansk' => 'Gdańsk',
                                            'm_slupsk' => 'Słupsk',
                                            'm_gdynia' => 'Gdynia',
                                        ],
                                        'slaskie' => [
                                            'bielski' => 'bielski',
                                            'będziński' => 'będziński',
                                            'bytom' => 'bytom',
                                            'czezstochowski' => 'częstochowski',
                                            'gliwicki' => 'gliwicki',
                                            'jastrzebski' => 'jastrzębski',
                                            'katowicki' => 'katowicki',
                                            'lubliniecki' => 'lubliniecki',
                                            'mikołowski' => 'mikołowski',
                                            'myszkowski' => 'myszkowski',
                                            'piekarski' => 'piekarski',
                                            'raciborski' => 'raciborski',
                                            'rybnicki' => 'rybnicki',
                                            'skarzyski' => 'skarżyski',
                                            'tarnogórski' => 'tarnogórski',
                                            'tarnowski' => 'tarnowski',
                                            'wodzisławski' => 'wodzisławski',
                                            'zawierciański' => 'zawierciański',
                                            'zabrzański' => 'zabrzański',
                                            'm_bielsko_biala' => 'Bielsko-Biała',
                                            'm_bytom' => 'Bytom',
                                            'm_chorzow' => 'Chorzów',
                                            'm_dabrowa_gornicza' => 'Dąbrowa Górnicza',
                                            'm_gliwice' => 'Gliwice',
                                            'm_jastrzebie_zdroj' => 'Jastrzębie-Zdrój',
                                            'm_katowice' => 'Katowice',
                                            'm_mysłowice' => 'Mysłowice',
                                            'm_piekary_slaskie' => 'Piekary Śląskie',
                                            'm_ruda_slaska' => 'Ruda Śląska',
                                            'm_rybnik' => 'Rybnik',
                                            'm_sosnowiec' => 'Sosnowiec',
                                            'm_tychy' => 'Tychy',
                                            'm_wodzislaw_slaski' => 'Wodzisław Śląski',
                                            'm_zabrze' => 'Zabrze',
                                            'm_zory' => 'Żory',
                                            'm_zawiercie' => 'Zawiercie',
                                        ],
                                        'swietokrzyskie' => [
                                            'buski' => 'buski',
                                            'kazimierski' => 'kazimierski',
                                            'kielce' => 'kielecki',
                                            'opatowski' => 'opatowski',
                                            'ostrowiecki' => 'ostrowiecki',
                                            'pińczowski' => 'pińczowski',
                                            'skarzyski' => 'skarżyski',
                                            'sandomierski' => 'sandomierski',
                                            'sielpianski' => 'staszowski',
                                            'wloszczowski' => 'włoszczowski',
                                            'm_kielce' => 'Kielce',
                                        ],

                                        'warminsko_mazurskie' => [
                                            'bartoszycki' => 'bartoszycki',
                                            'braniewski' => 'braniewski',
                                            'dzialdowski' => 'działdowski',
                                            'elk' => 'ełcki',
                                            'elkowski' => 'ełcki',
                                            'gizycki' => 'giżycki',
                                            'golubski' => 'gołdapski',
                                            'ketrzynski' => 'kętrzyński',
                                            'lidzbarski' => 'lidzbarski',
                                            'olecki' => 'olecki',
                                            'olsztynski' => 'olsztyński',
                                            'ostrodzki' => 'ostródzki',
                                            'szczycienski' => 'szczycieński',
                                            'wegorzewski' => 'węgorzewski',
                                            'm_olsztyn' => 'Olsztyn',
                                            'm_elblag' => 'Elbląg',
                                        ],
                                        'zachodniopomorskie' => [
                                            'bialogardzki' => ' białogardzki',
                                            'choszczenski' => ' choszczeński',
                                            'drawski' => ' drawski',
                                            'goleniowski' => ' goleniowski',
                                            'gralinski' => ' gryfiński',
                                            'gryficki' => ' gryficki',
                                            'kamieński' => ' kamieński',
                                            'koszaliński' => ' koszaliński',
                                            'lęborki' => ' lęborski',
                                            'lodzki_wschodni' => ' łódzki wschodni', // (może być pomyłka, bo to nie w zachodniopomorskim)
                                            'myśliborski' => ' myśliborski',
                                            'policki' => ' policki',
                                            'sławieński' => ' sławieński',
                                            'stargardzki' => ' stargardzki',
                                            'świdwiński' => ' świdwiński',
                                            'wałecki' => ' wałecki',
                                            'm_bałtycki' => 'Kołobrzeg',
                                            'm_koszalin' => 'Koszalin',
                                            'm_szczecin' => 'Szczecin',
                                            'm_swinoujscie' => 'Świnoujście',
                                        ],
                                        'wielkopolskie' => [
                                            'chodzieski' => 'chodzieski',
                                            'czarnkowsko_trzcianecki' => 'czarnkowsko-trzcianecki',
                                            'gostynski' => 'gostyński',
                                            'grodziski' => 'grodziski',
                                            'kaliski' => 'kaliski',
                                            'koniński' => 'koniński',
                                            'kościański' => 'kościański',
                                            'krotoszynski' => 'krotoszyński',
                                            'kolski' => 'kolski',
                                            'leszczynski' => 'leszczyński',
                                            'międzychodzki' => 'międzychodzki',
                                            'nowotomyski' => 'nowotomyski',
                                            'obornicki' => 'obornicki',
                                            'ostrowski' => 'ostrowski',
                                            'pleszewski' => 'pleszewski',
                                            'poznanski' => 'poznański',
                                            'rawicki' => 'rawicki',
                                            'szamotulski' => 'szamotulski',
                                            'szczecinecki' => 'szczecinecki',
                                            'sredzki' => 'średzki',
                                            'wolsztynski' => 'wolsztyński',
                                            'wągrowiecki' => 'wągrowiecki',
                                            'wielkopolski' => 'wielkopolski',
                                            'zlotowski' => 'złotowski',
                                            'm_kalisz' => 'Kalisz',
                                            'm_konin' => 'Konin',
                                            'm_leszno' => 'Leszno',
                                            'm_poznan' => 'Poznań',
                                        ],
                                        default => [
                                            'chodzieski' => 'chodzieski',
                                            'czarnkowsko_trzcianecki' => 'czarnkowsko-trzcianecki',
                                            'gostynski' => 'gostyński',
                                            'grodziski' => 'grodziski',
                                            'kaliski' => 'kaliski',
                                            'koniński' => 'koniński',
                                            'kościański' => 'kościański',
                                            'krotoszynski' => 'krotoszyński',
                                            'kolski' => 'kolski',
                                            'leszczynski' => 'leszczyński',
                                            'międzychodzki' => 'międzychodzki',
                                            'nowotomyski' => 'nowotomyski',
                                            'obornicki' => 'obornicki',
                                            'ostrowski' => 'ostrowski',
                                            'pleszewski' => 'pleszewski',
                                            'poznanski' => 'poznański',
                                            'rawicki' => 'rawicki',
                                            'szamotulski' => 'szamotulski',
                                            'szczecinecki' => 'szczecinecki',
                                            'sredzki' => 'średzki',
                                            'wolsztynski' => 'wolsztyński',
                                            'wągrowiecki' => 'wągrowiecki',
                                            'wielkopolski' => 'wielkopolski',
                                            'zlotowski' => 'złotowski',
                                            'm_kalisz' => 'Kalisz',
                                            'm_konin' => 'Konin',
                                            'm_leszno' => 'Leszno',
                                            'm_poznan' => 'Poznań',
                                        ],
                                    })
                            ])->columns(4),
                        Section::make()
                            ->schema([
                                Checkbox::make('temperature')->default(true)->inline(true)->label('Odczyt temperatury powietrza'),
                                Checkbox::make('humidity')->default(true)->inline(true)->label('Odczyt wilgotności względnej'),
                                Checkbox::make('wind')->default(true)->inline(true)->label('Odczyt prędkości i kierunku wiatru'),
                                Checkbox::make('rain')->default(true)->inline(true)->label('Odczyt opadu deszczu (10 min)'),
                                ToggleButtons::make('active')->boolean()->grouped()->default(true)->inline(true)->label('Archiwizacja danych')->helperText(
                                    fn() => new HtmlString('Czy archiwizować/zapisywać dane na serwerze? <br/> [Zaleca się wyłączyć podczas konserwacji stacji]')
                                )->columnSpan(2),
                                ToggleButtons::make('public')->boolean()->grouped()->default(true)->inline(true)->label('Udostępnianie publiczne')->helperText('Wyświetlać w publicznej liście stacji?')->columnSpan(2),
                            ])->columns(4),
                        Section::make()
                            ->schema([
                                Textarea::make('description')->disableGrammarly()->autosize()->nullable()->columnSpanFull()->label('Krótki dodatkowy opis stacji.'),
                                FileUpload::make('photo')->image()->imageEditor()->visibility('public')->directory('stacje')->nullable()->columnSpanFull()->label('Wybierz dodatkowe zdjęcie dla stacji'),

                            ])
                    ])->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        return $data;
                    })->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Dodano nową stację')
                            ->color('success')->body('Edycja parametrów jest zawsze możliwa.'),
                    ),
            ])
            ->actions([
                EditAction::make()->label('Edytuj')->button()
                    ->form([
                        Section::make()
                            ->schema([
                                Checkbox::make('temperature')->default(true)->inline(true)->label('Odczyt temperatury powietrza'),
                                Checkbox::make('humidity')->default(true)->inline(true)->label('Odczyt wilgotności względnej'),
                                Checkbox::make('wind')->default(true)->inline(true)->label('Odczyt prędkości i kierunku wiatru'),
                                Checkbox::make('rain')->default(true)->inline(true)->label('Odczyt opadu deszczu (10 min)'),
                                ToggleButtons::make('active')->boolean()->grouped()->default(true)->inline(true)->label('Archiwizacja danych')->helperText(
                                    fn() => new HtmlString('Czy archiwizować/zapisywać dane na serwerze? <br/> [Zaleca się wyłączyć podczas konserwacji stacji]')
                                )->columnSpan(2),
                                ToggleButtons::make('public')->boolean()->grouped()->default(true)->inline(true)->label('Udostępnianie publiczne')->helperText('Wyświetlać w publicznej liście stacji?')->columnSpan(2),
                            ])->columns(4),
                        Section::make()
                            ->schema([
                                Textarea::make('description')->disableGrammarly()->autosize()->nullable()->columnSpanFull()->label('Krótki dodatkowy opis stacji.'),
                                FileUpload::make('photo')->image()->imageEditor()->visibility('public')->directory('stacje')->nullable()->columnSpanFull()->label('Wybierz dodatkowe zdjęcie dla stacji'),

                            ])

                    ])->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Zapisano zmiany.')
                            ->body('Edycja stacji pomyślna.')->color('success'),
                    )->modalHeading('Edytuj stację')
                    ->modalDescription('Zmień wartości wybranych opcji.')
                    ->modalSubmitActionLabel('Zapisz zmiany'),
                DeleteAction::make()->label('Usuń')->modalHeading('Usuń stację')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Usunięto stację')
                            ->color('success'),
                    )

            ])
            ->bulkActions([
                // ...
            ])->paginated([10]);
    }

    public function render()
    {
        return view('livewire.personal-stations');
    }
}
