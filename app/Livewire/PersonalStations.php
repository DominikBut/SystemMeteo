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
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PersonalStations extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
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
                TextColumn::make('id')->label('ID')->copyable()
                    ->copyMessage('Skopiowano ID')->tooltip('Skopiuj ID'),
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
                                    ->tilesUrl('https://tile.openstreetmap.de/{z}/{x}/{y}.png')->hint('MIT License © Dotswan'),
                                Hidden::make('lat')->default(52.2297),
                                // ->hiddenLabel()
                                // ->hidden(),
                                Hidden::make('lon')->default(21.0122),
                                // ->hiddenLabel()
                                // ->hidden(),
                                Select::make('voivodeship')->native(false)->searchable()->prefix('Województwo: ')->required()->columnSpan(2)->default('wielkopolskie')->hiddenLabel()
                                    ->options([
                                        'Wielkopolskie' => 'Wielkopolskie',
                                        'Dolnośląskie' => 'Dolnośląskie',
                                        'Kujawsko-pomorskie' => 'Kujawsko-pomorskie',
                                        'Łódzkie' => 'Łódzkie',
                                        'Lubelskie' => 'Lubelskie',
                                        'Lubuskie' => 'Lubuskie',
                                        'Małopolskie' => 'Małopolskie',
                                        'Mazowieckie' => 'Mazowieckie',
                                        'Opolskie' => 'Opolskie',
                                        'Podkarpackie' => 'Podkarpackie',
                                        'Podlaskie' => 'Podlaskie',
                                        'Pomorskie' => 'Pomorskie',
                                        'Śląskie' => 'Śląskie',
                                        'Świętokrzyskie' => 'Świętokrzyskie',
                                        'Warmińsko-mazurskie' => 'Warmińsko-mazurskie',
                                        'Zachodniopomorskie' => 'Zachodniopomorskie',
                                    ])
                                    ->live(),

                                Select::make('district')->native(false)->searchable()->prefix('Powiat, miasto:')->required()->columnSpan(2)->default('poznanski')->hiddenLabel()
                                    ->options(fn(Get $get): array => match ($get('voivodeship')) {
                                        'Dolnośląskie' => [
                                            'bolesławiecki' => 'bolesławiecki',
                                            'dzierżoniowski' => 'dzierżoniowski',
                                            'głogowski' => 'głogowski',
                                            'jeleniogórski' => 'jeleniogórski',
                                            'kamiennogórski' => 'kamiennogórski',
                                            'kłodzki' => 'kłodzki',
                                            'legnicki' => 'legnicki',
                                            'lubański' => 'lubański',
                                            'lwówecki' => 'lwówecki',
                                            'milicki' => 'milicki',
                                            'oleśnicki' => 'oleśnicki',
                                            'oławski' => 'oławski',
                                            'polkowicki' => 'polkowicki',
                                            'strzeliński' => 'strzeliński',
                                            'świdnicki' => 'świdnicki',
                                            'trzebnicki' => 'trzebnicki',
                                            'wałbrzyski' => 'wałbrzyski',
                                            'wołowski' => 'wołowski',
                                            'wrocławski' => 'wrocławski',
                                            'zgorzelecki' => 'zgorzelecki',
                                            'złotoryjski' => 'złotoryjski',
                                            'Jelenia Góra' => 'Jelenia Góra',
                                            'Legnica' => 'Legnica',
                                            'Wałbrzych' => 'Wałbrzych',
                                            'Wrocław' => 'Wrocław',
                                        ],
                                        'Kujawsko-pomorskie' => [
                                            'brodnicki' => 'brodnicki',
                                            'bydgoski' => 'bydgoski',
                                            'chełmiński' => 'chełmiński',
                                            'golubsko-dobrzyński' => 'golubsko-dobrzyński',
                                            'grudziądzki' => 'grudziądzki',
                                            'inowrocławski' => 'inowrocławski',
                                            'lipnowski' => 'lipnowski',
                                            'mogileński' => 'mogileński',
                                            'nakielski' => 'nakielski',
                                            'radziejowski' => 'radziejowski',
                                            'rypiński' => 'rypiński',
                                            'sępoleński' => 'sępoleński',
                                            'świecki' => 'świecki',
                                            'toruński' => 'toruński',
                                            'tucholski' => 'tucholski',
                                            'wąbrzeski' => 'wąbrzeski',
                                            'węgorzewski' => 'węgorzewski',
                                            'żniński' => 'żniński',
                                            'Bydgoszcz' => 'Bydgoszcz',
                                            'Grudziądz' => 'Grudziądz',
                                            'Toruń' => 'Toruń',
                                        ],
                                        'Lubelskie' => [
                                            'bialski' => 'bialski',
                                            'biłgorajski' => 'biłgorajski',
                                            'chełmski' => 'chełmski',
                                            'hrubieszowski' => 'hrubieszowski',
                                            'janowski' => 'janowski',
                                            'krasnostawski' => 'krasnostawski',
                                            'kraśnicki' => 'kraśnicki',
                                            'lubartowski' => 'lubartowski',
                                            'lubelski' => 'lubelski',
                                            'łukowski' => 'łukowski',
                                            'parczewski' => 'parczewski',
                                            'puławski' => 'puławski',
                                            'radzyński' => 'radzyński',
                                            'rycki' => 'rycki',
                                            'tomaszowski' => 'tomaszowski',
                                            'włodawski' => 'włodawski',
                                            'zamojski' => 'zamojski',
                                            'Biała Podlaska' => 'Biała Podlaska',
                                            'Chełm' => 'Chełm',
                                            'Lublin' => 'Lublin',
                                            'Zamość' => 'Zamość',
                                        ],
                                        'Lubuskie' => [
                                            'gorzowski' => 'gorzowski',
                                            'krośnieński' => 'krośnieński',
                                            'międzyrzecki' => 'międzyrzecki',
                                            'nowosolski' => 'nowosolski',
                                            'sulęciński' => 'sulęciński',
                                            'słubicki' => 'słubicki',
                                            'zielonogórski' => 'zielonogórski',
                                            'żarski' => 'żarski',
                                            'Gorzów Wielkopolski' => 'Gorzów Wielkopolski',
                                            'Zielona Góra' => 'Zielona Góra',
                                        ],
                                        'Łódzkie' => [
                                            'bełchatowski' => 'bełchatowski',
                                            'brzeziński' => 'brzeziński',
                                            'kutnowski' => 'kutnowski',
                                            'łowicki' => 'łowicki',
                                            'łódzki wschodni' => 'łódzki wschodni',
                                            'łódzki zachodni' => 'łódzki zachodni',
                                            'piotrkowski' => 'piotrkowski',
                                            'pabianicki' => 'pabianicki',
                                            'pajęczański' => 'pajęczański',
                                            'radomszczański' => 'radomszczański',
                                            'sieradzki' => 'sieradzki',
                                            'skierniewicki' => 'skierniewicki',
                                            'tomaszowski' => 'tomaszowski',
                                            'wieluński' => 'wieluński',
                                            'wieruszowski' => 'wieruszowski',
                                            'zduńskowolski' => 'zduńskowolski',
                                            'zgierski' => 'zgierski',
                                            'Łódź' => 'Łódź',
                                            'Piotrków Trybunalski' => 'Piotrków Trybunalski',
                                            'Skierniewice' => 'Skierniewice',
                                        ],
                                        'Małopolskie' => [
                                            'bocheński' => 'bocheński',
                                            'brzeski' => 'brzeski',
                                            'chrzanowski' => 'chrzanowski',
                                            'dąbrowski' => 'dąbrowski',
                                            'gorlicki' => 'gorlicki',
                                            'krakowski' => 'krakowski',
                                            'limanowski' => 'limanowski',
                                            'miechowski' => 'miechowski',
                                            'nowosądecki' => 'nowosądecki',
                                            'nowotarski' => 'nowotarski',
                                            'olkuski' => 'olkuski',
                                            'oświęcimski' => 'oświęcimski',
                                            'proszowicki' => 'proszowicki',
                                            'suski' => 'suski',
                                            'tarnowski' => 'tarnowski',
                                            'wadowicki' => 'wadowicki',
                                            'wielicki' => 'wielicki',
                                            'Bożec' => 'Bożec',
                                            'Kielce' => 'Kielce',
                                            'Kraków' => 'Kraków',
                                            'Nowy Sącz' => 'Nowy Sącz',
                                            'Tarnów' => 'Tarnów',
                                        ],
                                        'Mazowieckie' => [
                                            'bielski' => 'bielski',
                                            'ciechanowski' => 'ciechanowski',
                                            'garwoliński' => 'garwoliński',
                                            'gostyniński' => 'gostyniński',
                                            'grodziski' => 'grodziski',
                                            'grójecki' => 'grójecki',
                                            'kozienicki' => 'kozienicki',
                                            'legionowski' => 'legionowski',
                                            'lipski' => 'lipski',
                                            'łowicki' => 'łowicki',
                                            'makowski' => 'makowski',
                                            'międzyrzecki' => 'międzyrzecki',
                                            'ostrołęcki' => 'ostrołęcki',
                                            'ostrowski' => 'ostrowski',
                                            'ostrowiecki' => 'ostrowiecki',
                                            'piaseczyński' => 'piaseczyński',
                                            'płocki' => 'płocki',
                                            'pruszkowski' => 'pruszkowski',
                                            'pruszków' => 'pruszków',
                                            'radomski' => 'radomski',
                                            'siedlecki' => 'siedlecki',
                                            'sierpecki' => 'sierpecki',
                                            'sochaczewski' => 'sochaczewski',
                                            'szydłowiecki' => 'szydłowiecki',
                                            'warszawski zachodni' => 'warszawski zachodni',
                                            'wołomiński' => 'wołomiński',
                                            'węgrowski' => 'węgrowski',
                                            'żyrardowski' => 'żyrardowski',
                                            'Ostrołęka' => 'Ostrołęka',
                                            'Płock' => 'Płock',
                                            'Radom' => 'Radom',
                                            'Siedlce' => 'Siedlce',
                                            'Warszawa' => 'Warszawa',
                                        ],
                                        'Opolskie' => [
                                            'brzeski' => 'brzeski',
                                            'krapkowicki' => 'krapkowicki',
                                            'namysłowski' => 'namysłowski',
                                            'nyski' => 'nyski',
                                            'opolski' => 'opolski',
                                            'prudnicki' => 'prudnicki',
                                            'strzelecki' => 'strzelecki',
                                            'Opole' => 'Opole',
                                        ],
                                        'Podkarpackie' => [
                                            'bieszczadzki' => 'bieszczadzki',
                                            'brzozowski' => 'brzozowski',
                                            'dębicki' => 'dębicki',
                                            'jarosławski' => 'jarosławski',
                                            'jasielski' => 'jasielski',
                                            'kolbuszowski' => 'kolbuszowski',
                                            'krośnieński' => 'krośnieński',
                                            'lubaczowski' => 'lubaczowski',
                                            'leski' => 'leski',
                                            'mielecki' => 'mielecki',
                                            'przemyski' => 'przemyski',
                                            'ropczycko-sędziszowski' => 'ropczycko-sędziszowski',
                                            'rzeszowski' => 'rzeszowski',
                                            'sanocki' => 'sanocki',
                                            'stalowowolski' => 'stalowowolski',
                                            'strzyżowski' => 'strzyżowski',
                                            'tarnobrzeski' => 'tarnobrzeski',
                                            'Krosno' => 'Krosno',
                                            'Przemyśl' => 'Przemyśl',
                                            'Rzeszów' => 'Rzeszów',
                                        ],
                                        'Podlaskie' => [
                                            'białostocki' => 'białostocki',
                                            'bielski' => 'bielski',
                                            'grajewski' => 'grajewski',
                                            'hajnowski' => 'hajnowski',
                                            'kolninski' => 'kolneński',
                                            'łomżyński' => 'łomżyński',
                                            'moniecki' => 'moniecki',
                                            'sejneński' => 'sejneński',
                                            'siemiatycki' => 'siemiatycki',
                                            'sokólski' => 'sokólski',
                                            'wysokomazowiecki' => 'wysokomazowiecki',
                                            'zambrowski' => 'zambrowski',
                                            'Białystok' => 'Białystok',
                                            'Łomża' => 'Łomża',
                                            'Suwałki' => 'Suwałki',
                                        ],
                                        'Pomorskie' => [
                                            'bytowski' => 'bytowski',
                                            'człuchowski' => 'człuchowski',
                                            'gdański' => 'gdański',
                                            'kartuski' => 'kartuski',
                                            'kwidzyński' => 'kwidzyński',
                                            'lęborski' => 'lęborski',
                                            'malborski' => 'malborski',
                                            'nowodworski' => 'nowodworski',
                                            'pucki' => 'pucki',
                                            'słupski' => 'słupski',
                                            'starogardzki' => 'starogardzki',
                                            'sztumski' => 'sztumski',
                                            'tczewski' => 'tczewski',
                                            'wejherowski' => 'wejherowski',
                                            'Gdańsk' => 'Gdańsk',
                                            'Słupsk' => 'Słupsk',
                                            'Gdynia' => 'Gdynia',
                                        ],
                                        'Śląskie' => [
                                            'bielski' => 'bielski',
                                            'będziński' => 'będziński',
                                            'częstochowski' => 'częstochowski',
                                            'gliwicki' => 'gliwicki',
                                            'jastrzębski' => 'jastrzębski',
                                            'katowicki' => 'katowicki',
                                            'lubliniecki' => 'lubliniecki',
                                            'mikołowski' => 'mikołowski',
                                            'myszkowski' => 'myszkowski',
                                            'piekarski' => 'piekarski',
                                            'raciborski' => 'raciborski',
                                            'rybnicki' => 'rybnicki',
                                            'skarżyski' => 'skarżyski',
                                            'tarnogórski' => 'tarnogórski',
                                            'tarnowski' => 'tarnowski',
                                            'wodzisławski' => 'wodzisławski',
                                            'zawierciański' => 'zawierciański',
                                            'zabrzański' => 'zabrzański',
                                            'Bielsko-Biała' => 'Bielsko-Biała',
                                            'Bytom' => 'Bytom',
                                            'Chorzów' => 'Chorzów',
                                            'Dąbrowa Górnicza' => 'Dąbrowa Górnicza',
                                            'Gliwice' => 'Gliwice',
                                            'Jastrzębie-Zdrój' => 'Jastrzębie-Zdrój',
                                            'Katowice' => 'Katowice',
                                            'Mysłowice' => 'Mysłowice',
                                            'Piekary Śląskie' => 'Piekary Śląskie',
                                            'Ruda Śląska' => 'Ruda Śląska',
                                            'Rybnik' => 'Rybnik',
                                            'Sosnowiec' => 'Sosnowiec',
                                            'Tychy' => 'Tychy',
                                            'Wodzisław Śląski' => 'Wodzisław Śląski',
                                            'Zabrze' => 'Zabrze',
                                            'Żory' => 'Żory',
                                            'Zawiercie' => 'Zawiercie',
                                        ],
                                        'Świętokrzyskie' => [
                                            'buski' => 'buski',
                                            'kazimierski' => 'kazimierski',
                                            'kielecki' => 'kielecki',
                                            'opatowski' => 'opatowski',
                                            'ostrowiecki' => 'ostrowiecki',
                                            'pińczowski' => 'pińczowski',
                                            'skarżyski' => 'skarżyski',
                                            'sandomierski' => 'sandomierski',
                                            'staszowski' => 'staszowski',
                                            'włoszczowski' => 'włoszczowski',
                                            'Kielce' => 'Kielce',
                                        ],

                                        'Warmińsko-mazurskie' => [
                                            'bartoszycki' => 'bartoszycki',
                                            'braniewski' => 'braniewski',
                                            'działdowski' => 'działdowski',
                                            'ełcki' => 'ełcki',
                                            'ełkowski' => 'ełkowski',
                                            'giżycki' => 'giżycki',
                                            'gołdapski' => 'gołdapski',
                                            'kętrzyński' => 'kętrzyński',
                                            'lidzbarski' => 'lidzbarski',
                                            'olecki' => 'olecki',
                                            'olsztyński' => 'olsztyński',
                                            'ostródzki' => 'ostródzki',
                                            'szczycieński' => 'szczycieński',
                                            'węgorzewski' => 'węgorzewski',
                                            'Olsztyn' => 'Olsztyn',
                                            'Elbląg' => 'Elbląg',
                                        ],
                                        'Zachodniopomorskie' => [
                                            'białogardzki' => ' białogardzki',
                                            'choszczeński' => ' choszczeński',
                                            'drawski' => ' drawski',
                                            'goleniowski' => ' goleniowski',
                                            'gryfiński' => ' gryfiński',
                                            'gryficki' => ' gryficki',
                                            'kamieński' => ' kamieński',
                                            'koszaliński' => ' koszaliński',
                                            'lęborski' => ' lęborski',
                                            'łódzki wschodni' => ' łódzki wschodni', // (może być pomyłka, bo to nie w zachodniopomorskim)
                                            'myśliborski' => ' myśliborski',
                                            'policki' => ' policki',
                                            'sławieński' => ' sławieński',
                                            'stargardzki' => ' stargardzki',
                                            'świdwiński' => ' świdwiński',
                                            'wałecki' => ' wałecki',
                                            'Kołobrzeg' => 'Kołobrzeg',
                                            'Koszalin' => 'Koszalin',
                                            'Szczecin' => 'Szczecin',
                                            'Świnoujście' => 'Świnoujście',
                                        ],
                                        'Wielkopolskie' => [
                                            'chodzieski' => 'chodzieski',
                                            'czarnkowsko-trzcianecki' => 'czarnkowsko-trzcianecki',
                                            'gostyński' => 'gostyński',
                                            'grodziski' => 'grodziski',
                                            'kaliski' => 'kaliski',
                                            'koniński' => 'koniński',
                                            'kościański' => 'kościański',
                                            'krotoszyński' => 'krotoszyński',
                                            'kolski' => 'kolski',
                                            'leszczyński' => 'leszczyński',
                                            'międzychodzki' => 'międzychodzki',
                                            'nowotomyski' => 'nowotomyski',
                                            'obornicki' => 'obornicki',
                                            'ostrowski' => 'ostrowski',
                                            'pleszewski' => 'pleszewski',
                                            'poznański' => 'poznański',
                                            'rawicki' => 'rawicki',
                                            'szamotulski' => 'szamotulski',
                                            'szczecinecki' => 'szczecinecki',
                                            'średzki' => 'średzki',
                                            'wolsztyński' => 'wolsztyński',
                                            'wągrowiecki' => 'wągrowiecki',
                                            'wielkopolski' => 'wielkopolski',
                                            'złotowski' => 'złotowski',
                                            'Kalisz' => 'Kalisz',
                                            'Konin' => 'Konin',
                                            'Leszno' => 'Leszno',
                                            'Poznań' => 'Poznań',
                                        ],
                                        default => [
                                            'chodzieski' => 'chodzieski',
                                            'czarnkowsko-trzcianecki' => 'czarnkowsko-trzcianecki',
                                            'gostyński' => 'gostyński',
                                            'grodziski' => 'grodziski',
                                            'kaliski' => 'kaliski',
                                            'koniński' => 'koniński',
                                            'kościański' => 'kościański',
                                            'krotoszyński' => 'krotoszyński',
                                            'kolski' => 'kolski',
                                            'leszczyński' => 'leszczyński',
                                            'międzychodzki' => 'międzychodzki',
                                            'nowotomyski' => 'nowotomyski',
                                            'obornicki' => 'obornicki',
                                            'ostrowski' => 'ostrowski',
                                            'pleszewski' => 'pleszewski',
                                            'poznański' => 'poznański',
                                            'rawicki' => 'rawicki',
                                            'szamotulski' => 'szamotulski',
                                            'szczecinecki' => 'szczecinecki',
                                            'średzki' => 'średzki',
                                            'wolsztyński' => 'wolsztyński',
                                            'wągrowiecki' => 'wągrowiecki',
                                            'wielkopolski' => 'wielkopolski',
                                            'złotowski' => 'złotowski',
                                            'Kalisz' => 'Kalisz',
                                            'Konin' => 'Konin',
                                            'Leszno' => 'Leszno',
                                            'Poznań' => 'Poznań',
                                        ],
                                    })
                            ])->columns(4),
                        Section::make()
                            ->schema([
                                Checkbox::make('temperature')->default(true)->inline(true)->label('Odczyt temperatury powietrza')->columnSpan(2),
                                Checkbox::make('humidity')->default(true)->inline(true)->label('Odczyt wilgotności względnej')->columnSpan(2),
                                Checkbox::make('wind')->default(true)->inline(true)->label('Odczyt prędkości i kierunku wiatru')->columnSpan(2),
                                Checkbox::make('rain')->default(true)->inline(true)->label('Odczyt opadu deszczu (10 min)')->columnSpan(2),
                                ToggleButtons::make('active')->boolean()->grouped()->default(true)->inline(true)->label('Archiwizacja danych')->helperText(
                                    fn() => new HtmlString('Czy archiwizować/zapisywać dane na serwerze? <br/> [Zaleca się wyłączyć podczas konserwacji stacji]')
                                )->columnSpan(2),
                                ToggleButtons::make('public')->boolean()->grouped()->default(true)->inline(true)->label('Udostępnianie publiczne')->helperText('Wyświetlać w publicznej liście stacji?')->columnSpan(2),
                            ])->columns(4),
                        Section::make()
                            ->schema([
                                Textarea::make('description')->disableGrammarly()->autosize()->nullable()->columnSpanFull()->label('Krótki dodatkowy opis stacji.'),
                                FileUpload::make('photo')->image()->imageEditor()->visibility('public')->directory('stacje')->nullable()->columnSpanFull()->label('Wybierz dodatkowe zdjęcie dla stacji'),

                            ])->columns(4)
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
                ViewAction::make('Dane')->label("Dane")
                    ->openUrlInNewTab()->url(fn($record) => route('stacja_community', ['id' => $record->id]))->openUrlInNewTab()->color(Color::Blue)->extraAttributes(['class' => 'text-blue-500 underline cursor-pointer']),


                EditAction::make()->label('Edytuj')
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
