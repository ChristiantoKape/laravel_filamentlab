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
    public $achievements = []; // Tambahkan ini
    public $isEditing = false;
    
    public function mount(): void
    {
        $settings = app(HomepageSettings::class);

        $this->heroes = $settings->heroes ?? [];
        $this->achievements = $settings->achievements ?? [];

        $this->form->fill([
            'heroes' => $this->heroes,
            'achievements' => $this->achievements,
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

                Section::make('Achievements Section')
                    ->schema([
                        Repeater::make('achievements')
                            ->label('')
                            ->schema([
                                TextInput::make('achievement_title')
                                    ->label('Title')
                                    ->required()
                                    ->disabled(fn () => !$this->isEditing)
                                    ->maxLength(150),
                            ])
                            ->disabled(fn () => !$this->isEditing)
                            ->deletable(fn () => $this->isEditing)
                            ->addActionLabel('Add Achievement')
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
            return; // Jangan submit jika bukan mode edit
        }

        $settings = app(HomepageSettings::class);
        $data = $this->form->getState();

        $settings->heroes = $data['heroes'] ?? [];
        $settings->achievements = $data['achievements'] ?? [];
        $settings->save();
        
        $this->isEditing = false; // Kembali ke mode view setelah save
        
        Notification::make()
            ->success()
            ->title('Homepage settings updated successfully')
            ->send();
    }

    protected static string $view = 'filament.pages.manage-homepage';
}
