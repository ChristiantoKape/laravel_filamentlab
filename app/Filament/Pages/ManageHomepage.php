<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Settings\HomepageSettings;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManageHomepage extends Page
{
    use InteractsWithForms;  // tambahkan trait

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Homepage Settings';
    
    public $heroes = [];
    public $heroes_titles = [];
    public $heroes_banner_desktop = [];
    public $heroes_banner_mobile = [];
    public $heroes_descriptions = [];
    public $heroes_button_text = [];
    public $heroes_button_url = [];
    public $isEditing = false;
    
    public function mount(): void
    {
        $settings = app(HomepageSettings::class);

        $this->heroes_titles = $settings->heroes_titles ?? [];
        $this->heroes_banner_desktop = $settings->heroes_banner_desktop ?? [];
        $this->heroes_banner_mobile = $settings->heroes_banner_mobile ?? [];
        $this->heroes_descriptions = $settings->heroes_descriptions ?? [];
        $this->heroes_button_text = $settings->heroes_button_text ?? [];
        $this->heroes_button_url = $settings->heroes_button_url ?? [];

        // Transform data for form
        $heroes = [];
        $count = count($this->heroes_titles);
        
        for ($i = 0; $i < $count; $i++) {
            $heroes[] = [
                'hero_title' => $this->heroes_titles[$i] ?? '',
                'hero_banner_image_desktop' => $this->heroes_banner_desktop[$i] ?? '',
                'hero_banner_image_mobile' => $this->heroes_banner_mobile[$i] ?? '',
                'short_description' => $this->heroes_descriptions[$i] ?? '',
                'button_text' => $this->heroes_button_text[$i] ?? '',
                'button_url' => $this->heroes_button_url[$i] ?? '',
            ];
        }

        $this->heroes = $heroes; // Set heroes array untuk form
        
        $this->form->fill([
            'heroes' => $this->heroes,
        ]);
    }

    public function toggleEdit(): void
    {
        $this->isEditing = !$this->isEditing;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->color('success')
                ->action('submit')
                ->visible(fn () => $this->isEditing),

            Action::make('edit')
                ->label('Edit')
                ->color('primary')
                ->action('toggleEdit')
                ->visible(fn () => !$this->isEditing), // Hanya tampil ketika TIDAK dalam mode edit
        ];
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hero Section')
                    ->schema([
                        Repeater::make('heroes')
                            ->label('')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('hero_banner_image_desktop')
                                            ->image()
                                            ->required()
                                            ->label('Banner Image Desktop')
                                            ->directory('hero_banner_image_desktops')
                                            ->imagePreviewHeight('250')
                                            ->maxSize(2048)
                                            ->helperText('Format yang diizinkan: JPG, JPEG, PNG (Maksimal 2MB)')
                                            ->getUploadedFileNameForStorageUsing(
                                                fn (TemporaryUploadedFile $file): string => 
                                                    Carbon::now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension()
                                            ),
                                        FileUpload::make('hero_banner_image_mobile')
                                            ->image()
                                            ->required()
                                            ->label('Banner Image Mobile')
                                            ->directory('hero_banner_image_mobiles')
                                            ->imagePreviewHeight('250')
                                            ->maxSize(2048)
                                            ->helperText('Format yang diizinkan: JPG, JPEG, PNG (Maksimal 2MB)')
                                            ->getUploadedFileNameForStorageUsing(
                                                fn (TemporaryUploadedFile $file): string => 
                                                    Carbon::now()->format('Y-m-d_H-i-s') . '.' . $file->getClientOriginalExtension()
                                            ),
                                    ]),
                                TextInput::make('hero_title')
                                    ->label('Title')
                                    ->required()
                                    ->disabled(fn () => !$this->isEditing)
                                    ->maxLength(150),
                                Textarea::make('short_description')
                                    ->label('Short Description')
                                    ->required()
                                    ->rows(5)
                                    ->cols(10),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('button_text')
                                            ->label('Button Text')
                                            ->required(),
                                        TextInput::make('button_url')
                                            ->label('Button URL')
                                            ->url()
                                            ->suffixIcon('heroicon-m-globe-alt')
                                            ->required(),            
                                    ]),
                                
                            ])
                            ->disabled(fn () => !$this->isEditing)
                            ->deletable(fn () => $this->isEditing)
                            ->addActionLabel('Add Hero')
                            ->hiddenLabel()
                            ->grid(1)
                            ->itemLabel('')
                            ->collapsible(false)
                    ]),
            ]);
    }
    
    public function submit(): void
    {
        if (!$this->isEditing) {
            return;
        }

        $settings = app(HomepageSettings::class);
        $data = $this->form->getState();

        // Transform heroes data for storage
        $heroes_titles = [];
        $heroes_banner_desktop = [];
        $heroes_banner_mobile = [];
        $heroes_descriptions = [];
        $heroes_button_text = [];
        $heroes_button_url = [];

        foreach ($data['heroes'] as $hero) {
            $heroes_titles[] = $hero['hero_title'];
            $heroes_banner_desktop[] = $hero['hero_banner_image_desktop'];
            $heroes_banner_mobile[] = $hero['hero_banner_image_mobile'];
            $heroes_descriptions[] = $hero['short_description'];
            $heroes_button_text[] = $hero['button_text'];
            $heroes_button_url[] = $hero['button_url'];
        }

        $settings->heroes_titles = $heroes_titles;
        $settings->heroes_banner_desktop = $heroes_banner_desktop;
        $settings->heroes_banner_mobile = $heroes_banner_mobile;
        $settings->heroes_descriptions = $heroes_descriptions;
        $settings->heroes_button_text = $heroes_button_text;
        $settings->heroes_button_url = $heroes_button_url;
        $settings->save();
        
        $this->isEditing = false;
        
        Notification::make()
            ->success()
            ->title('Homepage settings updated successfully')
            ->send();
    }

    protected static string $view = 'filament.pages.manage-homepage';
}
